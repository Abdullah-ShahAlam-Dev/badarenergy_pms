<?php

namespace App\Http\Controllers;

use App\DataTables\WarehouseDataTable;
use App\Helper\Reply;
use App\Http\Requests\Warehouse\StoreWarehouseRequest;
use App\Http\Requests\Warehouse\UpdateWarehouseRequest;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'modules.warehouse.warehouses';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('admin', user_roles()) && user()->permission('view_warehouses') == 'none');

            return $next($request);
        });
    }

    /**
     * Display a listing of warehouses.
     */
    public function index(WarehouseDataTable $dataTable)
    {
        $this->viewPermission   = user()->permission('view_warehouses');
        $this->addPermission    = user()->permission('add_warehouses');

        abort_403($this->viewPermission == 'none' && !in_array('admin', user_roles()));

        return $dataTable->render('warehouses.index', $this->data);
    }

    /**
     * Show the form for creating a new warehouse.
     * Guard: non-AJAX direct navigation is redirected to index.
     */
    public function create()
    {
        // If accessed directly (not via openRightModal AJAX), send back to index
        if (!request()->ajax()) {
            return redirect(route('warehouses.index'));
        }

        $this->addPermission = user()->permission('add_warehouses');
        abort_403($this->addPermission != 'all' && !in_array('admin', user_roles()));

        $this->warehouseTypes = Warehouse::TYPES;
        $this->pageTitle = __('modules.warehouse.addWarehouse');

        if (request()->ajax()) {
            $html = view('warehouses.ajax.create', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('warehouses.ajax.create', $this->data);
    }

    /**
     * Store a newly created warehouse.
     */
    public function store(StoreWarehouseRequest $request)
    {
        $this->addPermission = user()->permission('add_warehouses');
        abort_403($this->addPermission != 'all' && !in_array('admin', user_roles()));

        $warehouse              = new Warehouse();
        $warehouse->name        = strip_tags($request->name);
        $warehouse->code        = strtoupper(strip_tags($request->code));
        $warehouse->type        = $request->type;
        $warehouse->address     = strip_tags($request->address);
        $warehouse->is_active   = $request->has('is_active') ? 1 : 0;
        $warehouse->save();

        return Reply::successWithData(__('messages.recordSaved'), [
            'warehouseID' => $warehouse->id,
            'redirectUrl' => route('warehouses.index'),
        ]);
    }

    /**
     * Show the form for editing a warehouse.
     * Guard: non-AJAX direct navigation is redirected to index.
     */
    public function edit($id)
    {
        // If accessed directly (not via openRightModal AJAX), send back to index
        if (!request()->ajax()) {
            return redirect(route('warehouses.index'));
        }

        $this->editPermission = user()->permission('edit_warehouses');
        abort_403($this->editPermission != 'all' && !in_array('admin', user_roles()));

        $this->warehouse      = Warehouse::findOrFail($id);
        $this->warehouseTypes = Warehouse::TYPES;
        $this->pageTitle = __('modules.warehouse.editWarehouse');

        if (request()->ajax()) {
            $html = view('warehouses.ajax.edit', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('warehouses.ajax.edit', $this->data);
    }

    /**
     * Update the specified warehouse.
     */
    public function update(UpdateWarehouseRequest $request, $id)
    {
        $this->editPermission = user()->permission('edit_warehouses');
        abort_403($this->editPermission != 'all' && !in_array('admin', user_roles()));

        $warehouse            = Warehouse::findOrFail($id);
        $warehouse->name      = strip_tags($request->name);
        $warehouse->code      = strtoupper(strip_tags($request->code));
        $warehouse->type      = $request->type;
        $warehouse->address   = strip_tags($request->address);
        $warehouse->is_active = $request->has('is_active') ? 1 : 0;

        // Business Rule: system must always have at least one active warehouse
        if (!$warehouse->is_active) {
            $otherActiveCount = Warehouse::where('id', '!=', $id)
                ->where('is_active', 1)
                ->count();

            if ($otherActiveCount === 0) {
                return Reply::error(__('modules.warehouse.atLeastOneActiveError'));
            }
        }

        $warehouse->save();

        return Reply::successWithData(__('messages.updateSuccess'), [
            'redirectUrl' => route('warehouses.index'),
        ]);
    }

    /**
     * Remove the specified warehouse.
     */
    public function destroy($id)
    {
        $this->deletePermission = user()->permission('delete_warehouses');
        abort_403($this->deletePermission != 'all' && !in_array('admin', user_roles()));

        $warehouse = Warehouse::findOrFail($id);

        // Business Rule: cannot delete the last active warehouse
        if ($warehouse->is_active) {
            $activeCount = Warehouse::where('is_active', 1)->count();

            if ($activeCount <= 1) {
                return Reply::error(__('modules.warehouse.atLeastOneActiveError'));
            }
        }

        $warehouse->delete();

        return Reply::success(__('messages.deleteSuccess'));
    }

    /**
     * Toggle warehouse active/inactive status (called via AJAX).
     */
    public function toggleStatus(Request $request)
    {
        $this->editPermission = user()->permission('edit_warehouses');
        abort_403($this->editPermission != 'all' && !in_array('admin', user_roles()));

        $warehouse = Warehouse::findOrFail($request->warehouseId);

        // Business Rule: cannot deactivate the last active warehouse
        if ($warehouse->is_active) {
            $activeCount = Warehouse::where('is_active', 1)->count();

            if ($activeCount <= 1) {
                return Reply::error(__('modules.warehouse.atLeastOneActiveError'));
            }
        }

        $warehouse->is_active = !$warehouse->is_active;
        $warehouse->save();

        return Reply::success(__('messages.updateSuccess'));
    }
}
