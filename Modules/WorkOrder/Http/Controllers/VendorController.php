<?php

namespace Modules\WorkOrder\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\WorkOrder\Entities\Vendor;
use Modules\WorkOrder\Http\Requests\StoreVendor;
use Modules\WorkOrder\Http\Requests\UpdateVendor;

class VendorController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('workorder::modules.vendor.vendors');
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('admin', user_roles()) && user()->permission('view_vendor') == 'none');
            return $next($request);
        });
    }

    public function index()
    {
        abort_403(!in_array('admin', user_roles()) && user()->permission('view_vendor') == 'none');

        $this->vendors = Vendor::where('company_id', company()->id)
            ->when(!in_array('admin', user_roles()) && user()->permission('view_vendor') !== 'all', function ($q) {
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
        abort_403(!in_array('admin', user_roles()) && user()->permission('add_vendor') == 'none');

        $this->countries = countries();

        if (request()->ajax()) {
            $html = view('workorder::vendors.ajax.create', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => __('workorder::modules.vendor.addVendor')]);
        }

        return view('workorder::vendors.create', $this->data);
    }

    public function store(StoreVendor $request)
    {
        abort_403(user()->permission('add_vendor') == 'none');

        Vendor::create([
            'company_id'       => company()->id,
            'vendor_name'      => $request->vendor_name,
            'company_name'     => $request->company_name,
            'designation'      => $request->designation,
            'country_phonecode'=> $request->country_phonecode,
            'mobile'           => $request->mobile,
            'alternate_country_phonecode' => $request->alternate_country_phonecode,
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
        abort_403(!in_array('admin', user_roles()) && !($editPermission == 'all' || (in_array($editPermission, ['added', 'owned', 'both']) && $this->vendor->added_by == user()->id)));

        $this->countries = countries();

        if (request()->ajax()) {
            $html = view('workorder::vendors.ajax.edit', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => __('workorder::modules.vendor.editVendor')]);
        }

        return view('workorder::vendors.edit', $this->data);
    }

    public function update(UpdateVendor $request, $id)
    {
        $vendor = Vendor::where('company_id', company()->id)->findOrFail($id);
        $editPermission = user()->permission('edit_vendor');
        abort_403(!in_array('admin', user_roles()) && !($editPermission == 'all' || (in_array($editPermission, ['added', 'owned', 'both']) && $vendor->added_by == user()->id)));

        $vendor->update([
            'vendor_name'      => $request->vendor_name,
            'company_name'     => $request->company_name,
            'designation'      => $request->designation,
            'country_phonecode'=> $request->country_phonecode,
            'mobile'           => $request->mobile,
            'alternate_country_phonecode' => $request->alternate_country_phonecode,
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
        abort_403(!in_array('admin', user_roles()) && !($deletePermission == 'all' || (in_array($deletePermission, ['added', 'owned', 'both']) && $vendor->added_by == user()->id)));

        $vendor->delete();
        return Reply::success(__('messages.recordDeleted'));
    }
}
