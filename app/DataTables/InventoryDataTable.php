<?php

namespace App\DataTables;

use App\DataTables\BaseDataTable;
use App\Models\Inventory;
use Yajra\DataTables\Html\Column;

class InventoryDataTable extends BaseDataTable
{
    private $adjustInventoryPermission;

    public function __construct()
    {
        parent::__construct();
        $this->adjustInventoryPermission = user()->permission('adjust_inventory');
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
                return '<input type="checkbox" class="select-table-row" id="datatable-row-' . $row->id . '" name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
            })
            ->addColumn('action', function ($row) {
                $action = '<div class="task_view">';

                if ($this->adjustInventoryPermission == 'all' || in_array('admin', user_roles())) {
                    $action .= '<a class="btn btn-xs btn-primary openRightModal mr-2" href="' . route('inventory.create', ['product_id' => $row->product_id, 'warehouse_id' => $row->warehouse_id]) . '">
                                <i class="fa fa-adjust mr-1"></i> ' . __('modules.inventory.adjustStock') . '
                               </a>';
                }

                $action .= '</div>';
                return $action;
            })
            ->addColumn('product_name', function ($row) {
                return $row->product ? $row->product->name : '-';
            })
            ->addColumn('warehouse_name', function ($row) {
                return $row->warehouse ? $row->warehouse->name : '-';
            })
            ->editColumn('quantity', function ($row) {
                return number_format($row->quantity, 2);
            })
            ->addIndexColumn()
            ->smart(false)
            ->setRowId(function ($row) {
                return 'row-' . $row->id;
            })
            ->rawColumns(['action', 'check', 'product_name', 'warehouse_name']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param Inventory $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Inventory $model)
    {
        $request = $this->request();
        $query = Inventory::with(['product', 'warehouse'])->select('inventories.*');

        // Apply filters
        if (!is_null($request->searchText)) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('product', function ($q2) use ($request) {
                    $q2->where('name', 'like', '%' . $request->searchText . '%');
                })->orWhereHas('warehouse', function ($q2) use ($request) {
                    $q2->where('name', 'like', '%' . $request->searchText . '%');
                });
            });
        }

        if ($request->warehouse_id != 'all' && !is_null($request->warehouse_id)) {
            $query->where('inventories.warehouse_id', $request->warehouse_id);
        }

        if ($request->product_id != 'all' && !is_null($request->product_id)) {
            $query->where('inventories.product_id', $request->product_id);
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
        return $this->setBuilder('inventory-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["inventory-table"].buttons().container()
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
            __('modules.inventory.product') => ['data' => 'product_name', 'name' => 'product.name', 'title' => __('modules.inventory.product'), 'exportable' => true, 'orderable' => false],
            __('modules.inventory.warehouse') => ['data' => 'warehouse_name', 'name' => 'warehouse.name', 'title' => __('modules.inventory.warehouse'), 'exportable' => true, 'orderable' => false],
            __('modules.inventory.quantity') => ['data' => 'quantity', 'name' => 'quantity', 'title' => __('modules.inventory.quantity')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20'),
        ];
    }
}
