<?php

namespace App\Http\Controllers;

use App\DataTables\ShipmentDataTable;
use App\Helper\Reply;
use App\Http\Requests\Shipment\StoreShipmentRequest;
use App\Http\Requests\Shipment\UpdateShipmentRequest;
use App\Models\Shipment;
use App\Facades\WorkflowConfig;
use App\Services\ShipmentGeneratorService;
use Illuminate\Http\Request;

class ShipmentController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Shipments';
        $this->activeMenu = 'shipments';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('admin', user_roles()) && user()->permission('view_shipments') == 'none');

            return $next($request);
        });
    }

    /**
     * Display a listing of shipments.
     */
    public function index(ShipmentDataTable $dataTable)
    {
        $this->viewPermission = user()->permission('view_shipments');
        $this->addPermission = user()->permission('add_shipments');

        abort_403($this->viewPermission == 'none' && !in_array('admin', user_roles()));

        return $dataTable->render('shipments.index', $this->data);
    }

    /**
     * Show the form for creating a new shipment.
     */
    public function create()
    {
        if (!request()->ajax()) {
            return redirect(route('shipments.index'));
        }

        $this->addPermission = user()->permission('add_shipments');
        abort_403($this->addPermission != 'all' && !in_array('admin', user_roles()));

        $companyId = company() ? company()->id : 1;
        $this->allowManualShipmentNumber = WorkflowConfig::get('inventory', 'allow_manual_shipment_number', false, $companyId);
        $this->pageTitle = 'Add Shipment';

        if (request()->ajax()) {
            $html = view('shipments.ajax.create', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('shipments.ajax.create', $this->data);
    }

    /**
     * Store a newly created shipment.
     */
    public function store(StoreShipmentRequest $request)
    {
        $this->addPermission = user()->permission('add_shipments');
        abort_403($this->addPermission != 'all' && !in_array('admin', user_roles()));

        $companyId = company() ? company()->id : 1;
        $allowManual = WorkflowConfig::get('inventory', 'allow_manual_shipment_number', false, $companyId);

        $shipment = new Shipment();
        $shipment->company_id = $companyId;

        // Auto shipment number generation logic (Amendment 1)
        if (!$allowManual || empty($request->shipment_number) || $request->shipment_number === 'SHP-[Auto Generated]') {
            $generator = app(ShipmentGeneratorService::class);
            $shipment->shipment_number = $generator->generate($companyId);
        } else {
            $shipment->shipment_number = strip_tags($request->shipment_number);
        }

        $shipment->container_number = strip_tags($request->container_number);
        $shipment->bill_of_lading = strip_tags($request->bill_of_lading);
        $shipment->manufacturing_ref = strip_tags($request->manufacturing_ref);
        $shipment->port_of_origin = strip_tags($request->port_of_origin);
        $shipment->port_of_discharge = strip_tags($request->port_of_discharge);
        if ($request->eta) {
            try {
                $shipment->eta = \Carbon\Carbon::createFromFormat(company()->date_format, $request->eta)->format('Y-m-d');
            } catch (\Exception $e) {
                $shipment->eta = \Carbon\Carbon::parse($request->eta)->format('Y-m-d');
            }
        }
        if ($request->arrival_date) {
            try {
                $shipment->arrival_date = \Carbon\Carbon::createFromFormat(company()->date_format, $request->arrival_date)->format('Y-m-d');
            } catch (\Exception $e) {
                $shipment->arrival_date = \Carbon\Carbon::parse($request->arrival_date)->format('Y-m-d');
            }
        }
        $shipment->status = $request->status;
        $shipment->remarks = strip_tags($request->remarks);
        $shipment->save();

        return Reply::successWithData(__('messages.recordSaved'), [
            'redirectUrl' => route('shipments.index'),
        ]);
    }

    /**
     * Show the form for editing a shipment.
     */
    public function edit($id)
    {
        if (!request()->ajax()) {
            return redirect(route('shipments.index'));
        }

        $this->editPermission = user()->permission('edit_shipments');
        abort_403($this->editPermission != 'all' && !in_array('admin', user_roles()));

        // Scoped by company_id (Amendment 2)
        $companyId = company() ? company()->id : 1;
        $this->shipment = Shipment::where('company_id', $companyId)->findOrFail($id);
        $this->allowManualShipmentNumber = WorkflowConfig::get('inventory', 'allow_manual_shipment_number', false, $companyId);
        $this->pageTitle = 'Edit Shipment';

        if (request()->ajax()) {
            $html = view('shipments.ajax.edit', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('shipments.ajax.edit', $this->data);
    }

    /**
     * Update the specified shipment.
     */
    public function update(UpdateShipmentRequest $request, $id)
    {
        $this->editPermission = user()->permission('edit_shipments');
        abort_403($this->editPermission != 'all' && !in_array('admin', user_roles()));

        // Scoped by company_id (Amendment 2)
        $companyId = company() ? company()->id : 1;
        $shipment = Shipment::where('company_id', $companyId)->findOrFail($id);
        $allowManual = WorkflowConfig::get('inventory', 'allow_manual_shipment_number', false, $companyId);

        if ($allowManual && !empty($request->shipment_number)) {
            $shipment->shipment_number = strip_tags($request->shipment_number);
        }

        $shipment->container_number = strip_tags($request->container_number);
        $shipment->bill_of_lading = strip_tags($request->bill_of_lading);
        $shipment->manufacturing_ref = strip_tags($request->manufacturing_ref);
        $shipment->port_of_origin = strip_tags($request->port_of_origin);
        $shipment->port_of_discharge = strip_tags($request->port_of_discharge);
        if ($request->eta) {
            try {
                $shipment->eta = \Carbon\Carbon::createFromFormat(company()->date_format, $request->eta)->format('Y-m-d');
            } catch (\Exception $e) {
                $shipment->eta = \Carbon\Carbon::parse($request->eta)->format('Y-m-d');
            }
        } else {
            $shipment->eta = null;
        }
        if ($request->arrival_date) {
            try {
                $shipment->arrival_date = \Carbon\Carbon::createFromFormat(company()->date_format, $request->arrival_date)->format('Y-m-d');
            } catch (\Exception $e) {
                $shipment->arrival_date = \Carbon\Carbon::parse($request->arrival_date)->format('Y-m-d');
            }
        } else {
            $shipment->arrival_date = null;
        }
        $shipment->status = $request->status;
        $shipment->remarks = strip_tags($request->remarks);
        $shipment->save();

        return Reply::successWithData(__('messages.updateSuccess'), [
            'redirectUrl' => route('shipments.index'),
        ]);
    }

    /**
     * Display details of a shipment.
     */
    public function show($id)
    {
        $this->viewPermission = user()->permission('view_shipments');
        abort_403($this->viewPermission == 'none' && !in_array('admin', user_roles()));

        // Scoped by company_id (Amendment 2)
        $companyId = company() ? company()->id : 1;
        $this->shipment = Shipment::where('company_id', $companyId)
            ->with('stockIntakeVouchers.creator')
            ->findOrFail($id);
        $this->pageTitle = 'Shipment Details - ' . $this->shipment->shipment_number;

        if (request()->ajax()) {
            $html = view('shipments.ajax.show', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('shipments.show', $this->data);
    }

    /**
     * Remove the specified shipment.
     */
    public function destroy($id)
    {
        $this->deletePermission = user()->permission('delete_shipments');
        abort_403($this->deletePermission != 'all' && !in_array('admin', user_roles()));

        // Scoped by company_id (Amendment 2)
        $companyId = company() ? company()->id : 1;
        $shipment = Shipment::where('company_id', $companyId)->findOrFail($id);

        // Delete Protection Audit (Amendment 3)
        if ($shipment->stockIntakeVouchers()->exists()) {
            return Reply::error('Cannot delete shipment because it is linked to Stock Intake Vouchers.');
        }

        $shipment->delete();

        return Reply::success(__('messages.deleteSuccess'));
    }
}
