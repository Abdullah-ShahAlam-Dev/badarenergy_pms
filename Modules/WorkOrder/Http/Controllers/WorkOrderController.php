<?php

namespace Modules\WorkOrder\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\Event;
use App\Models\Tax;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\WorkOrder\Entities\ApprovalMapping;
use Modules\WorkOrder\Entities\Vendor;
use Modules\WorkOrder\Entities\WorkOrder;
use Modules\WorkOrder\Entities\WorkOrderApproval;
use Modules\WorkOrder\Entities\WorkOrderItem;
use Modules\WorkOrder\Notifications\NewWorkOrder;
use Barryvdh\DomPDF\Facade\Pdf;

class WorkOrderController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('workorder::modules.workOrder.workOrders');
        $this->middleware(function ($request, $next) {
            abort_403(user()->permission('view_work_order') == 'none');
            return $next($request);
        });
    }

    // ── INDEX ──────────────────────────────────────────────────────────────────

    public function index()
    {
        $query = WorkOrder::with(['vendor', 'event', 'creator'])
            ->where('company_id', company()->id)
            ->when(user()->permission('view_work_order') === 'added', fn($q) => $q->where('created_by', user()->id))
            ->when(user()->permission('view_work_order') === 'owned', fn($q) => $q->where('created_by', user()->id))
            ->when(user()->permission('view_work_order') === 'both',  fn($q) => $q->where('created_by', user()->id))
            ->orderBy('created_at', 'desc');

        $this->workOrders  = $query->paginate(15);
        $this->totalCount   = WorkOrder::where('company_id', company()->id)->count();
        $this->pendingCount = WorkOrder::where('company_id', company()->id)->where('status', 'pending_approval')->count();
        $this->approvedCount = WorkOrder::where('company_id', company()->id)->where('status', 'approved')->count();
        $this->completedCount = WorkOrder::where('company_id', company()->id)->where('status', 'completed')->count();

        return view('workorder::work-orders.index', $this->data);
    }

    // ── CREATE ─────────────────────────────────────────────────────────────────

    public function create()
    {
        abort_403(user()->permission('add_work_order') == 'none');

        $this->events  = Event::where('company_id', company()->id)->orderBy('event_name')->get();
        $this->vendors = Vendor::where('company_id', company()->id)->where('status', 'active')->orderBy('vendor_name')->get();
        $this->taxes   = Tax::where('company_id', company()->id)->get();
        $this->nextWoNumber = WorkOrder::nextWoNumber();

        if (request()->ajax()) {
            $html = view('workorder::work-orders.ajax.create', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => __('workorder::modules.workOrder.createWorkOrder')]);
        }

        return view('workorder::work-orders.create', $this->data);
    }

    // ── STORE ──────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        abort_403(user()->permission('add_work_order') == 'none');

        $request->validate([
            'event_id'     => 'required|exists:events,id',
            'vendor_id'    => 'required|exists:vendors,id',
            'wo_date'      => 'required|date',
            'delivery_date' => 'nullable|date|after_or_equal:wo_date',
            'item_name'    => 'required|array|min:1',
            'item_name.*'  => 'required|string',
            'quantity.*'   => 'required|numeric|min:0.01',
            'rate.*'       => 'required|numeric|min:0',
        ]);

        // Guard: active vendor only
        $vendor = Vendor::where('company_id', company()->id)->where('status', 'active')->findOrFail($request->vendor_id);

        // Build totals
        [$subTotal, $taxAmount, $itemsData] = $this->buildItemTotals($request);

        $discount      = (float)($request->discount ?? 0);
        $discountType  = $request->discount_type ?? 'percent';
        $discountAmount = $discountType === 'percent' ? $subTotal * ($discount / 100) : $discount;
        $grandTotal     = $subTotal + $taxAmount - $discountAmount;

        // Approval check
        $mapping = ApprovalMapping::getApproverFor(user()->id);
        $approvalRequired = (bool)($request->approval_required ?? 1);
        $status = 'draft';

        if ($approvalRequired) {
            $status = 'pending_approval';
        } else {
            $status = 'approved';
        }

        $workOrder = WorkOrder::create([
            'company_id'          => company()->id,
            'wo_number'           => WorkOrder::nextWoNumber(),
            'wo_date'             => Carbon::parse($request->wo_date)->toDateString(),
            'delivery_date'       => $request->delivery_date ? Carbon::parse($request->delivery_date)->toDateString() : null,
            'event_id'            => $request->event_id,
            'vendor_id'           => $request->vendor_id,
            'work_category'       => $request->work_category,
            'venue'               => $request->venue,
            'no_of_days'          => $request->no_of_days ?? 1,
            'priority'            => $request->priority ?? 'medium',
            'description'         => $request->description,
            'remarks'             => $request->remarks,
            'terms_conditions'    => $request->terms_conditions,
            'special_instructions' => $request->special_instructions,
            'sub_total'           => $subTotal,
            'discount'            => $discount,
            'discount_type'       => $discountType,
            'tax_amount'          => $taxAmount,
            'grand_total'         => $grandTotal,
            'status'              => $status,
            'approval_required'   => $approvalRequired ? 1 : 0,
            'created_by'          => user()->id,
            'approved_by'         => $approvalRequired ? null : user()->id,
            'approved_at'         => $approvalRequired ? null : now(),
            'added_by'            => user()->id,
            'last_updated_by'     => user()->id,
        ]);

        // Save items
        foreach ($itemsData as $item) {
            WorkOrderItem::create(array_merge($item, ['work_order_id' => $workOrder->id]));
        }

        // Audit log
        WorkOrderApproval::create([
            'work_order_id' => $workOrder->id,
            'user_id'       => user()->id,
            'action'        => 'submitted',
            'remarks'       => $approvalRequired ? 'Work Order submitted for approval.' : 'Work Order auto-approved (approval disabled).',
            'ip_address'    => $request->ip(),
        ]);

        // Notify assigned approver
        if ($approvalRequired && $mapping) {
            $approverUser = \App\Models\User::find($mapping->approver_id);
            if ($approverUser) {
                $approverUser->notify(new NewWorkOrder($workOrder));
            }
        }

        return Reply::successWithData(__('messages.recordSaved'), [
            'redirectUrl' => route('work-orders.index'),
        ]);
    }

    // ── SHOW ───────────────────────────────────────────────────────────────────

    public function show($id)
    {
        $this->workOrder = WorkOrder::with(['vendor', 'event', 'items.tax', 'approvals.user', 'creator', 'approver'])
            ->where('company_id', company()->id)
            ->findOrFail($id);

        $this->approvalMapping = ApprovalMapping::getApproverFor($this->workOrder->created_by);
        $this->canApprove = $this->resolveCanApprove($this->workOrder);
        $this->totalPaid = $this->workOrder->payments()->sum('amount');

        if (request()->ajax()) {
            $html = view('workorder::work-orders.ajax.show', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->workOrder->wo_number]);
        }

        return view('workorder::work-orders.show', $this->data);
    }

    // ── EDIT ───────────────────────────────────────────────────────────────────

    public function edit($id)
    {
        $this->workOrder = WorkOrder::with('items')->where('company_id', company()->id)->findOrFail($id);

        // Locked work orders cannot be edited
        abort_403($this->workOrder->isLocked());

        $editPermission = user()->permission('edit_work_order');
        abort_403(!($editPermission == 'all' || (in_array($editPermission, ['added', 'owned', 'both']) && $this->workOrder->created_by == user()->id)));

        $this->events  = Event::where('company_id', company()->id)->orderBy('event_name')->get();
        $this->vendors = Vendor::where('company_id', company()->id)->where('status', 'active')->orderBy('vendor_name')->get();
        $this->taxes   = Tax::where('company_id', company()->id)->get();

        if (request()->ajax()) {
            $html = view('workorder::work-orders.ajax.edit', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => __('workorder::modules.workOrder.editWorkOrder')]);
        }

        return view('workorder::work-orders.edit', $this->data);
    }

    // ── UPDATE ─────────────────────────────────────────────────────────────────

    public function update(Request $request, $id)
    {
        $workOrder = WorkOrder::where('company_id', company()->id)->findOrFail($id);

        abort_403($workOrder->isLocked());
        $editPermission = user()->permission('edit_work_order');
        abort_403(!($editPermission == 'all' || (in_array($editPermission, ['added', 'owned', 'both']) && $workOrder->created_by == user()->id)));

        $request->validate([
            'event_id'    => 'required|exists:events,id',
            'vendor_id'   => 'required|exists:vendors,id',
            'wo_date'     => 'required|date',
            'item_name'   => 'required|array|min:1',
            'item_name.*' => 'required|string',
            'quantity.*'  => 'required|numeric|min:0.01',
            'rate.*'      => 'required|numeric|min:0',
        ]);

        [$subTotal, $taxAmount, $itemsData] = $this->buildItemTotals($request);
        $discount      = (float)($request->discount ?? 0);
        $discountType  = $request->discount_type ?? 'percent';
        $discountAmount = $discountType === 'percent' ? $subTotal * ($discount / 100) : $discount;
        $grandTotal     = $subTotal + $taxAmount - $discountAmount;

        $workOrder->update([
            'wo_date'             => Carbon::parse($request->wo_date)->toDateString(),
            'delivery_date'       => $request->delivery_date ? Carbon::parse($request->delivery_date)->toDateString() : null,
            'event_id'            => $request->event_id,
            'vendor_id'           => $request->vendor_id,
            'work_category'       => $request->work_category,
            'venue'               => $request->venue,
            'no_of_days'          => $request->no_of_days ?? 1,
            'priority'            => $request->priority ?? 'medium',
            'description'         => $request->description,
            'remarks'             => $request->remarks,
            'terms_conditions'    => $request->terms_conditions,
            'special_instructions' => $request->special_instructions,
            'sub_total'           => $subTotal,
            'discount'            => $discount,
            'discount_type'       => $discountType,
            'tax_amount'          => $taxAmount,
            'grand_total'         => $grandTotal,
            'last_updated_by'     => user()->id,
        ]);

        // Refresh items
        $workOrder->items()->delete();
        foreach ($itemsData as $item) {
            WorkOrderItem::create(array_merge($item, ['work_order_id' => $workOrder->id]));
        }

        return Reply::successWithData(__('messages.recordUpdated'), [
            'redirectUrl' => route('work-orders.index'),
        ]);
    }

    // ── DESTROY ────────────────────────────────────────────────────────────────

    public function destroy($id)
    {
        $workOrder = WorkOrder::where('company_id', company()->id)->findOrFail($id);
        $deletePermission = user()->permission('delete_work_order');
        abort_403(!($deletePermission == 'all' || (in_array($deletePermission, ['added', 'owned', 'both']) && $workOrder->created_by == user()->id)));

        $workOrder->delete();
        return Reply::success(__('messages.recordDeleted'));
    }

    // ── DUPLICATE ──────────────────────────────────────────────────────────────

    public function duplicate($id)
    {
        abort_403(user()->permission('add_work_order') == 'none');

        $original = WorkOrder::with('items')->where('company_id', company()->id)->findOrFail($id);

        $newWo = $original->replicate();
        $newWo->wo_number     = WorkOrder::nextWoNumber();
        $newWo->status        = 'draft';
        $newWo->approved_by   = null;
        $newWo->approved_at   = null;
        $newWo->created_by    = user()->id;
        $newWo->added_by      = user()->id;
        $newWo->last_updated_by = user()->id;
        $newWo->wo_date       = now()->toDateString();
        $newWo->save();

        foreach ($original->items as $item) {
            $newItem = $item->replicate();
            $newItem->work_order_id = $newWo->id;
            $newItem->save();
        }

        WorkOrderApproval::create([
            'work_order_id' => $newWo->id,
            'user_id'       => user()->id,
            'action'        => 'submitted',
            'remarks'       => 'Duplicated from Work Order #' . $original->wo_number,
            'ip_address'    => request()->ip(),
        ]);

        return Reply::successWithData(__('workorder::modules.workOrder.duplicated'), [
            'redirectUrl' => route('work-orders.show', $newWo->id),
        ]);
    }

    // ── PDF ────────────────────────────────────────────────────────────────────

    public function downloadPdf($id)
    {
        $this->workOrder = WorkOrder::with(['vendor', 'event', 'items.tax', 'creator', 'approver'])
            ->where('company_id', company()->id)
            ->findOrFail($id);

        $this->company = company();
        $pdf = Pdf::loadView('workorder::work-orders.pdf.work-order', $this->data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('work-order-' . $this->workOrder->wo_number . '.pdf');
    }

    public function printView($id)
    {
        $this->workOrder = WorkOrder::with(['vendor', 'event', 'items.tax', 'creator', 'approver'])
            ->where('company_id', company()->id)
            ->findOrFail($id);

        $this->company = company();
        return view('workorder::work-orders.pdf.work-order', $this->data);
    }

    // ── PRIVATE HELPERS ────────────────────────────────────────────────────────

    /**
     * Build item rows and return [subTotal, taxAmount, itemsArray].
     */
    private function buildItemTotals(Request $request): array
    {
        $subTotal  = 0;
        $taxAmount = 0;
        $items     = [];

        foreach ($request->item_name as $key => $name) {
            $qty        = (float)($request->quantity[$key] ?? 1);
            $rate       = (float)($request->rate[$key] ?? 0);
            $taxPercent = (float)($request->tax_percent[$key] ?? 0);
            $taxType    = $request->tax_type[$key] ?? 'exclusive';
            $taxId      = $request->tax_id[$key] ?? null;

            $calculated = WorkOrderItem::calculateTotal($qty, $rate, $taxPercent, $taxType);

            $lineSubtotal = $qty * $rate;
            $subTotal    += $lineSubtotal;
            $taxAmount   += $calculated['tax_amount'];

            $items[] = [
                'item_name'   => $name,
                'description' => $request->item_description[$key] ?? null,
                'quantity'    => $qty,
                'unit'        => $request->unit[$key] ?? null,
                'rate'        => $rate,
                'tax_id'      => $taxId,
                'tax_type'    => $taxType,
                'tax_percent' => $taxPercent,
                'tax_amount'  => $calculated['tax_amount'],
                'total'       => $calculated['total'],
            ];
        }

        return [$subTotal, $taxAmount, $items];
    }

    /**
     * Determine if the current user can approve/reject this work order.
     */
    private function resolveCanApprove(WorkOrder $workOrder): bool
    {
        if (!$workOrder->isPendingApproval()) {
            return false;
        }

        $mapping = ApprovalMapping::getApproverFor($workOrder->created_by);
        return $mapping && $mapping->approver_id === user()->id;
    }
}
