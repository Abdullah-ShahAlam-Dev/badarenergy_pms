<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\ErpWorkflowSetting\UpdateErpWorkflowSettingRequest;
use App\Facades\WorkflowConfig;
use Illuminate\Support\Facades\DB;

class ErpWorkflowSettingController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'ERP Workflow Settings';
        $this->activeSettingMenu = 'erp_workflow_settings';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('admin', user_roles()) && user()->permission('manage_erp_workflow_settings') != 'all');

            return $next($request);
        });
    }

    /**
     * Display a listing of settings.
     */
    public function index()
    {
        $companyId = company() ? company()->id : 1;

        $this->stockOutTrigger = WorkflowConfig::get('inventory', 'stock_out_trigger', 'invoice_approval', $companyId);
        $this->serialPrefix = WorkflowConfig::get('serial', 'prefix', 'BE', $companyId);
        $this->serialDigitLength = WorkflowConfig::get('serial', 'digit_length', 8, $companyId);
        $this->serialIncludeYear = WorkflowConfig::get('serial', 'include_year', true, $companyId);
        $this->approvalsDeliveryOrder = WorkflowConfig::get('approvals', 'delivery_order', false, $companyId);
        $this->barcodeType = WorkflowConfig::get('barcode', 'type', 'code128', $companyId);

        // Shipment settings (Sprint 2 Amendments)
        $this->shipmentPrefix = WorkflowConfig::get('shipment', 'prefix', 'SHP', $companyId);
        $this->shipmentDigitLength = WorkflowConfig::get('shipment', 'digit_length', 6, $companyId);
        $this->shipmentIncludeYear = WorkflowConfig::get('shipment', 'include_year', false, $companyId);
        $this->allowManualShipmentNumber = WorkflowConfig::get('inventory', 'allow_manual_shipment_number', false, $companyId);

        // Intake settings (Sprint 3)
        $this->intakePrefix = WorkflowConfig::get('intake', 'prefix', 'SIV', $companyId);
        $this->intakeDigitLength = WorkflowConfig::get('intake', 'digit_length', 6, $companyId);
        $this->intakeIncludeYear = WorkflowConfig::get('intake', 'include_year', true, $companyId);
        $this->approvalsStockIntake = WorkflowConfig::get('approvals', 'stock_intake', false, $companyId);

        return view('erp-workflow-settings.index', $this->data);
    }

    /**
     * Update the settings.
     */
    public function update(UpdateErpWorkflowSettingRequest $request)
    {
        $companyId = company() ? company()->id : 1;

        DB::transaction(function () use ($request, $companyId) {
            WorkflowConfig::set('inventory', 'stock_out_trigger', $request->stock_out_trigger, $companyId);
            WorkflowConfig::set('serial', 'prefix', $request->serial_prefix, $companyId);
            WorkflowConfig::set('serial', 'digit_length', (int)$request->serial_digit_length, $companyId);
            WorkflowConfig::set('serial', 'include_year', (bool)$request->serial_include_year, $companyId);
            WorkflowConfig::set('approvals', 'delivery_order', (bool)$request->approvals_delivery_order, $companyId);
            WorkflowConfig::set('barcode', 'type', $request->barcode_type, $companyId);

            // Shipment settings (Sprint 2 Amendments)
            WorkflowConfig::set('shipment', 'prefix', $request->shipment_prefix, $companyId);
            WorkflowConfig::set('shipment', 'digit_length', (int)$request->shipment_digit_length, $companyId);
            WorkflowConfig::set('shipment', 'include_year', (bool)$request->shipment_include_year, $companyId);
            WorkflowConfig::set('inventory', 'allow_manual_shipment_number', (bool)$request->allow_manual_shipment_number, $companyId);

            // Intake settings (Sprint 3)
            WorkflowConfig::set('intake', 'prefix', $request->intake_prefix, $companyId);
            WorkflowConfig::set('intake', 'digit_length', (int)$request->intake_digit_length, $companyId);
            WorkflowConfig::set('intake', 'include_year', (bool)$request->intake_include_year, $companyId);
            WorkflowConfig::set('approvals', 'stock_intake', (bool)$request->approvals_stock_intake, $companyId);
        });

        return Reply::success(__('messages.updateSuccess'));
    }
}
