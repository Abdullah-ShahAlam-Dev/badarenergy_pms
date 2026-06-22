<?php

namespace App\DataTables;

use App\DataTables\BaseDataTable;
use App\Models\Warehouse;
use Yajra\DataTables\Html\Column;

class WarehouseDataTable extends BaseDataTable
{
    private $editWarehousePermission;
    private $deleteWarehousePermission;

    public function __construct()
    {
        parent::__construct();
        $this->editWarehousePermission   = user()->permission('edit_warehouses');
        $this->deleteWarehousePermission = user()->permission('delete_warehouses');
    }

    /**
     * Build DataTable class.
     *
     * @param mixed $query
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="select-table-row" id="datatable-row-' . $row->id . '"  name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
            })
            ->addColumn('action', function ($row) {
                $action = '<div class="task_view">';

                $action .= '<div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                if ($this->editWarehousePermission == 'all' || in_array('admin', user_roles())) {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('warehouses.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
                }

                if ($this->deleteWarehousePermission == 'all' || in_array('admin', user_roles())) {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-warehouse-id="' . $row->id . '">
                                <i class="fa fa-trash mr-2"></i>
                                ' . trans('app.delete') . '
                            </a>';
                }

                $action .= '</div>
                    </div>
                </div>';

                return $action;
            })
            ->editColumn('name', function ($row) {
                return '<a class="text-darkest-grey openRightModal" href="' . route('warehouses.edit', $row->id) . '">'
                    . ucfirst($row->name) . '</a>';
            })
            ->editColumn('type', function ($row) {
                return $row->type_label;
            })
            ->editColumn('is_active', function ($row) {
                if ($this->editWarehousePermission == 'all' || in_array('admin', user_roles())) {
                    $checked = $row->is_active ? 'checked' : '';
                    return '<div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input toggle-warehouse-status" '
                                . $checked . ' id="warehouse-status-' . $row->id . '" data-warehouse-id="' . $row->id . '">
                                <label class="custom-control-label" for="warehouse-status-' . $row->id . '"></label>
                            </div>';
                }

                return $row->is_active
                    ? '<i class="fa fa-circle mr-1 text-dark-green f-10"></i>' . __('app.active')
                    : '<i class="fa fa-circle mr-1 text-red f-10"></i>' . __('app.inactive');
            })
            ->addIndexColumn()
            ->smart(false)
            ->setRowId(function ($row) {
                return 'row-' . $row->id;
            })
            ->rawColumns(['action', 'name', 'is_active', 'check']);
    }

    /**
     * @param Warehouse $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Warehouse $model)
    {
        $request = $this->request();

        $query = Warehouse::select('warehouses.*');

        if (!is_null($request->searchText)) {
            $query->where(function ($q) {
                $q->where('warehouses.name', 'like', '%' . request('searchText') . '%')
                    ->orWhere('warehouses.code', 'like', '%' . request('searchText') . '%')
                    ->orWhere('warehouses.address', 'like', '%' . request('searchText') . '%');
            });
        }

        if ($request->type != 'all' && !is_null($request->type)) {
            $query->where('warehouses.type', $request->type);
        }

        if ($request->status != 'all' && !is_null($request->status)) {
            $query->where('warehouses.is_active', $request->status == 'active' ? 1 : 0);
        }

        return $query;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->setBuilder('warehouses-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["warehouses-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
            ])
            ->buttons(\Yajra\DataTables\Html\Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> ' . trans('app.exportExcel')]));
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            'check' => [
                'title'      => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                'exportable' => false,
                'orderable'  => false,
                'searchable' => false,
            ],
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            __('app.id') => ['data' => 'id', 'name' => 'id', 'visible' => false, 'exportable' => false, 'title' => __('app.id')],
            __('modules.warehouse.name') => ['data' => 'name', 'name' => 'name', 'title' => __('modules.warehouse.name'), 'exportable' => false],
            __('modules.warehouse.code') => ['data' => 'code', 'name' => 'code', 'title' => __('modules.warehouse.code')],
            __('modules.warehouse.type') => ['data' => 'type', 'name' => 'type', 'title' => __('modules.warehouse.type')],
            __('modules.warehouse.address') => ['data' => 'address', 'name' => 'address', 'title' => __('modules.warehouse.address')],
            __('app.status') => ['data' => 'is_active', 'name' => 'is_active', 'title' => __('app.status'), 'exportable' => false],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20'),
        ];
    }
}
