<?php

namespace Modules\WorkOrder\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\WorkOrder\Entities\Vendor;

class VendorController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('workorder::modules.vendor.vendors');
        $this->middleware(function ($request, $next) {
            abort_403(user()->permission('view_vendor') == 'none');
            return $next($request);
        });
    }

    public function index()
    {
        abort_403(user()->permission('view_vendor') == 'none');

        $this->vendors = Vendor::where('company_id', company()->id)
            ->when(user()->permission('view_vendor') !== 'all', function ($q) {
                $q->where('added_by', user()->id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        if (request()->ajax()) {
            $html = view('workorder::vendors.ajax.index', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html]);
        }

        return view('workorder::vendors.index', $this->data);
    }

    public function create()
    {
        abort_403(user()->permission('add_vendor') == 'none');

        if (request()->ajax()) {
            $html = view('workorder::vendors.ajax.create', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => __('workorder::modules.vendor.addVendor')]);
        }

        return view('workorder::vendors.create', $this->data);
    }

    public function store(Request $request)
    {
        abort_403(user()->permission('add_vendor') == 'none');

        $request->validate([
            'vendor_name' => 'required|string|max:255',
            'email'       => 'nullable|email|max:255',
            'mobile'      => 'nullable|string|max:30',
        ]);

        Vendor::create([
            'company_id'       => company()->id,
            'vendor_name'      => $request->vendor_name,
            'company_name'     => $request->company_name,
            'designation'      => $request->designation,
            'mobile'           => $request->mobile,
            'alternate_mobile' => $request->alternate_mobile,
            'email'            => $request->email,
            'office_address'   => $request->office_address,
            'cnic'             => $request->cnic,
            'ntn'              => $request->ntn,
            'bank_details'     => $request->bank_details,
            'category'         => $request->category,
            'notes'            => $request->notes,
            'status'           => $request->status ?? 'active',
            'added_by'         => user()->id,
            'last_updated_by'  => user()->id,
        ]);

        return Reply::successWithData(__('messages.recordSaved'), [
            'redirectUrl' => route('vendors.index'),
        ]);
    }

    public function show($id)
    {
        $this->vendor = Vendor::where('company_id', company()->id)->findOrFail($id);
        $this->workOrders = $this->vendor->workOrders()->with('event')->latest()->take(10)->get();

        if (request()->ajax()) {
            $html = view('workorder::vendors.ajax.show', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->vendor->vendor_name]);
        }

        return view('workorder::vendors.show', $this->data);
    }

    public function edit($id)
    {
        $this->vendor = Vendor::where('company_id', company()->id)->findOrFail($id);
        $editPermission = user()->permission('edit_vendor');
        abort_403(!($editPermission == 'all' || (in_array($editPermission, ['added', 'owned', 'both']) && $this->vendor->added_by == user()->id)));

        if (request()->ajax()) {
            $html = view('workorder::vendors.ajax.edit', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => __('workorder::modules.vendor.editVendor')]);
        }

        return view('workorder::vendors.edit', $this->data);
    }

    public function update(Request $request, $id)
    {
        $vendor = Vendor::where('company_id', company()->id)->findOrFail($id);
        $editPermission = user()->permission('edit_vendor');
        abort_403(!($editPermission == 'all' || (in_array($editPermission, ['added', 'owned', 'both']) && $vendor->added_by == user()->id)));

        $request->validate([
            'vendor_name' => 'required|string|max:255',
            'email'       => 'nullable|email|max:255',
            'mobile'      => 'nullable|string|max:30',
        ]);

        $vendor->update([
            'vendor_name'      => $request->vendor_name,
            'company_name'     => $request->company_name,
            'designation'      => $request->designation,
            'mobile'           => $request->mobile,
            'alternate_mobile' => $request->alternate_mobile,
            'email'            => $request->email,
            'office_address'   => $request->office_address,
            'cnic'             => $request->cnic,
            'ntn'              => $request->ntn,
            'bank_details'     => $request->bank_details,
            'category'         => $request->category,
            'notes'            => $request->notes,
            'status'           => $request->status ?? 'active',
            'last_updated_by'  => user()->id,
        ]);

        return Reply::successWithData(__('messages.recordUpdated'), [
            'redirectUrl' => route('vendors.index'),
        ]);
    }

    public function destroy($id)
    {
        $vendor = Vendor::where('company_id', company()->id)->findOrFail($id);
        $deletePermission = user()->permission('delete_vendor');
        abort_403(!($deletePermission == 'all' || (in_array($deletePermission, ['added', 'owned', 'both']) && $vendor->added_by == user()->id)));

        $vendor->delete();
        return Reply::success(__('messages.recordDeleted'));
    }
}
