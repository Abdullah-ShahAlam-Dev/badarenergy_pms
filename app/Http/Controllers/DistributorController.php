<?php

namespace App\Http\Controllers;

use App\DataTables\DistributorsDataTable;
use App\Helper\Reply;
use App\Http\Requests\Distributor\StoreDistributorRequest;
use App\Http\Requests\Distributor\UpdateDistributorRequest;
use App\Models\Distributor;

class DistributorController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Distributors';

        $this->middleware(function ($request, $next) {
            $this->viewPermission = user()->permission('view_distributors');
            $this->addPermission = user()->permission('add_distributors');
            $this->editPermission = user()->permission('edit_distributors');
            $this->deletePermission = user()->permission('delete_distributors');

            return $next($request);
        });
    }

    public function index(DistributorsDataTable $dataTable)
    {
        abort_403(!in_array('admin', user_roles()) && $this->viewPermission == 'none');
        return $dataTable->render('distributors.index', $this->data);
    }

    public function create()
    {
        abort_403(!in_array('admin', user_roles()) && $this->addPermission == 'none');

        if (request()->ajax()) {
            $this->pageTitle = 'Add Distributor';
            $html = view('distributors.ajax.create', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return redirect(route('distributors.index'));
    }

    public function store(StoreDistributorRequest $request)
    {
        abort_403(!in_array('admin', user_roles()) && $this->addPermission == 'none');

        $distributor = new Distributor();
        $distributor->company_id = company() ? company()->id : null;
        $distributor->name = strip_tags($request->name);
        $distributor->email = $request->email;
        $distributor->phone = $request->phone;
        $distributor->company_name = $request->company_name;
        $distributor->address = $request->address;
        $distributor->shipping_address = $request->shipping_address;
        $distributor->tax_number = $request->tax_number;
        $distributor->status = $request->status;
        $distributor->note = $request->note;
        $distributor->save();

        return Reply::successWithData('Distributor created successfully.', [
            'redirectUrl' => route('distributors.index'),
        ]);
    }

    public function edit($id)
    {
        abort_403(!in_array('admin', user_roles()) && $this->editPermission == 'none');
        $this->distributor = Distributor::findOrFail($id);

        if (request()->ajax()) {
            $this->pageTitle = 'Edit Distributor';
            $html = view('distributors.ajax.edit', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return redirect(route('distributors.index'));
    }

    public function update(UpdateDistributorRequest $request, $id)
    {
        abort_403(!in_array('admin', user_roles()) && $this->editPermission == 'none');
        $distributor = Distributor::findOrFail($id);
        $distributor->name = strip_tags($request->name);
        $distributor->email = $request->email;
        $distributor->phone = $request->phone;
        $distributor->company_name = $request->company_name;
        $distributor->address = $request->address;
        $distributor->shipping_address = $request->shipping_address;
        $distributor->tax_number = $request->tax_number;
        $distributor->status = $request->status;
        $distributor->note = $request->note;
        $distributor->save();

        return Reply::successWithData('Distributor updated successfully.', [
            'redirectUrl' => route('distributors.index'),
        ]);
    }

    public function destroy($id)
    {
        abort_403(!in_array('admin', user_roles()) && $this->deletePermission == 'none');
        Distributor::destroy($id);
        return Reply::success('Distributor deleted successfully.');
    }
}
