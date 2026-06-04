<?php

namespace Modules\GatePass\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\Team;
use App\Models\User;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\GatePass\Entities\GatePassRequest;
use Modules\GatePass\Entities\GatePassItem;
use Modules\GatePass\Entities\GatePassApprovalLog;

class GatePassController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Gate Pass';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('admin', user_roles()) && user()->permission('view_gate_pass') == 'none');
            return $next($request);
        });
    }

    public function index()
    {
        $this->requests = GatePassRequest::with('user', 'department')
            ->where(function($query) {
                if (in_array('admin', user_roles())) {
                    // Admin can see all requests
                } elseif (user()->permission('view_gate_pass') == 'added') {
                    $query->where('user_id', user()->id);
                } elseif (user()->permission('view_gate_pass') == 'owned') {
                    $query->where('user_id', user()->id);
                } elseif (user()->permission('view_gate_pass') == 'both') {
                    $query->where('user_id', user()->id);
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('gatepass::index', $this->data);
    }

    public function create()
    {
        $this->departments = Team::all();
        $this->employees = User::allEmployees();
        $this->products = Product::all();
        
        $this->lastRequest = GatePassRequest::withTrashed()->orderBy('id', 'desc')->first();
        $this->nextNumber = 'GP-' . date('Y') . '-' . sprintf('%04d', ($this->lastRequest ? $this->lastRequest->id + 1 : 1));

        if (request()->ajax()) {
            $html = view('gatepass::ajax.create', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => 'Create Gate Pass Request']);
        }

        return view('gatepass::create', $this->data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:in,out',
            'return_type' => 'required|in:returnable,non-returnable',
            'request_date' => 'required|date',
            'purpose' => 'required',
            'product_id.*' => 'required|exists:products,id',
            'quantity.*' => 'required|numeric'
        ]);

        $gatePass = new GatePassRequest();
        $gatePass->company_id = company()->id;
        $gatePass->user_id = user()->id;
        $gatePass->department_id = $request->department_id;
        $gatePass->request_number = 'GP-' . date('Y') . '-' . sprintf('%04d', (GatePassRequest::withTrashed()->max('id') + 1));
        $gatePass->request_date = Carbon::parse($request->request_date)->toDateString();
        $gatePass->type = $request->type;
        $gatePass->return_type = $request->return_type;
        $gatePass->purpose = $request->purpose;
        $gatePass->from_location = $request->from_location;
        $gatePass->to_location = $request->to_location;
        $gatePass->vehicle_number = $request->vehicle_number;
        $gatePass->driver_name = $request->driver_name;
        $gatePass->expected_return_date = $request->expected_return_date ? Carbon::parse($request->expected_return_date)->toDateString() : null;
        $gatePass->status = 'pending_hod';
        $gatePass->save();

        // Save Items
        if ($request->product_id) {
            foreach ($request->product_id as $key => $productId) {
                $product = Product::find($productId);
                $name = $product ? $product->name : 'Unknown Product';
                GatePassItem::create([
                    'gate_pass_request_id' => $gatePass->id,
                    'product_id' => $productId,
                    'item_name' => $name,
                    'quantity' => $request->quantity[$key],
                    'unit' => $request->unit[$key] ?? null,
                    'serial_number' => $request->serial_number[$key] ?? null,
                    'asset_tag' => $request->asset_tag[$key] ?? null,
                    'condition' => $request->condition[$key] ?? null,
                    'remarks' => $request->item_remarks[$key] ?? null
                ]);
            }
        }

        // Log action
        GatePassApprovalLog::create([
            'gate_pass_request_id' => $gatePass->id,
            'user_id' => user()->id,
            'action' => 'submitted',
            'remarks' => 'Request submitted for HOD approval',
            'ip_address' => $request->ip()
        ]);

        return Reply::successWithData('Gate Pass request submitted successfully!', [
            'redirectUrl' => route('gate-pass.index')
        ]);
    }

    public function show($id)
    {
        $this->gatePass = GatePassRequest::with(['user', 'department', 'items', 'logs.user', 'hod', 'store', 'security'])->findOrFail($id);
        
        if (request()->ajax()) {
            $html = view('gatepass::ajax.show', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => 'Gate Pass Detail']);
        }

        return view('gatepass::show', $this->data);
    }

    public function edit($id)
    {
        $this->gatePass = GatePassRequest::with('items')->findOrFail($id);
        $editPermission = user()->permission('edit_gate_pass');
        abort_403(!in_array('admin', user_roles()) && !($editPermission == 'all' || (in_array($editPermission, ['added', 'owned', 'both']) && $this->gatePass->user_id == user()->id)));
        
        $this->departments = Team::all();
        $this->products = Product::all();

        if (request()->ajax()) {
            $html = view('gatepass::ajax.edit', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => 'Edit Gate Pass Request']);
        }

        return view('gatepass::edit', $this->data);
    }

    public function update(Request $request, $id)
    {
        $gatePass = GatePassRequest::findOrFail($id);
        $editPermission = user()->permission('edit_gate_pass');
        abort_403(!in_array('admin', user_roles()) && !($editPermission == 'all' || (in_array($editPermission, ['added', 'owned', 'both']) && $gatePass->user_id == user()->id)));

        $request->validate([
            'type' => 'required|in:in,out',
            'return_type' => 'required|in:returnable,non-returnable',
            'request_date' => 'required|date',
            'purpose' => 'required',
            'product_id.*' => 'required|exists:products,id',
            'quantity.*' => 'required|numeric'
        ]);

        $wasSentBack = ($gatePass->status == 'sent_back');

        $gatePass->department_id = $request->department_id;
        $gatePass->request_date = Carbon::parse($request->request_date)->toDateString();
        $gatePass->type = $request->type;
        $gatePass->return_type = $request->return_type;
        $gatePass->purpose = $request->purpose;
        $gatePass->from_location = $request->from_location;
        $gatePass->to_location = $request->to_location;
        $gatePass->vehicle_number = $request->vehicle_number;
        $gatePass->driver_name = $request->driver_name;
        $gatePass->expected_return_date = $request->expected_return_date ? Carbon::parse($request->expected_return_date)->toDateString() : null;
        
        if ($wasSentBack) {
            $gatePass->status = 'pending_hod';
        }
        $gatePass->save();

        // Update Items (Simple way: delete and recreate)
        $gatePass->items()->delete();
        if ($request->product_id) {
            foreach ($request->product_id as $key => $productId) {
                $product = Product::find($productId);
                $name = $product ? $product->name : 'Unknown Product';
                GatePassItem::create([
                    'gate_pass_request_id' => $gatePass->id,
                    'product_id' => $productId,
                    'item_name' => $name,
                    'quantity' => $request->quantity[$key],
                    'unit' => $request->unit[$key] ?? null,
                    'serial_number' => $request->serial_number[$key] ?? null,
                    'asset_tag' => $request->asset_tag[$key] ?? null,
                    'condition' => $request->condition[$key] ?? null,
                    'remarks' => $request->item_remarks[$key] ?? null
                ]);
            }
        }

        if ($wasSentBack) {
            GatePassApprovalLog::create([
                'gate_pass_request_id' => $gatePass->id,
                'user_id' => user()->id,
                'action' => 'submitted',
                'remarks' => 'Request resubmitted after correction',
                'ip_address' => $request->ip()
            ]);
        }

        return Reply::successWithData('Gate Pass request updated successfully!', [
            'redirectUrl' => route('gate-pass.index')
        ]);
    }

    public function destroy($id)
    {
        $gatePass = GatePassRequest::findOrFail($id);
        $deletePermission = user()->permission('delete_gate_pass');
        abort_403(!in_array('admin', user_roles()) && !($deletePermission == 'all' || (in_array($deletePermission, ['added', 'owned', 'both']) && $gatePass->user_id == user()->id)));
        
        $gatePass->delete();
        return Reply::success(__('messages.recordDeleted'));
    }

    public function printPass($id)
    {
        $this->gatePass = GatePassRequest::with(['user', 'department', 'items', 'hod', 'store', 'security'])->findOrFail($id);
        return view('gatepass::print', $this->data);
    }

    public function verifyPass($hash)
    {
        $this->gatePass = GatePassRequest::where('qr_code', $hash)->with(['user', 'department', 'items', 'hod', 'store', 'security'])->firstOrFail();
        return view('gatepass::show', $this->data);
    }
}
