<?php

namespace App\DataTables;

use App\DataTables\BaseDataTable;
use App\Models\StockIntakeVoucher;
use Yajra\DataTables\Html\Column;

class StockIntakeVoucherDataTable extends BaseDataTable
{
    private $viewPermission;
    private $editPermission;
    private $deletePermission;
    private $approvePermission;

    public function __construct()
    {
        parent::__construct();
        $this->viewPermission = user()->permission('view_stock_intake');
        $this->editPermission = user()->permission('edit_stock_intake');
        $this->deletePermission = user()->permission('delete_stock_intake');
        $this->approvePermission = user()->permission('approve_stock_intake');
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

                if ($this->viewPermission == 'all' || in_array('admin', user_roles())) {
                    $action .= '<a class="dropdown-item" href="' . route('stock-intakes.show', [$row->id]) . '">
                                <i class="fa fa-eye mr-2"></i>
                                ' . trans('app.view') . '
                            </a>';
                }

                if (($this->editPermission == 'all' || in_array('admin', user_roles())) && !in_array($row->status, ['completed', 'approved'])) {
                    $action .= '<a class="dropdown-item" href="' . route('stock-intakes.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
                }

                if (($this->approvePermission == 'all' || in_array('admin', user_roles())) && !in_array($row->status, ['completed', 'approved'])) {
                    $action .= '<a class="dropdown-item approve-voucher" href="javascript:;" data-voucher-id="' . $row->id . '">
                                <i class="fa fa-check mr-2"></i>
                                Approve
                            </a>';
                }

                if (($this->deletePermission == 'all' || in_array('admin', user_roles())) && !in_array($row->status, ['completed', 'approved'])) {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-voucher-id="' . $row->id . '">
                                <i class="fa fa-trash mr-2"></i>
                                ' . trans('app.delete') . '
                            </a>';
                }

                $action .= '</div>
                    </div>
                </div>';

                return $action;
            })
            ->editColumn('voucher_number', function ($row) {
                return '<a class="text-darkest-grey font-weight-bold" href="' . route('stock-intakes.show', $row->id) . '">'
                    . ucfirst($row->voucher_number) . '</a>';
            })
            ->editColumn('intake_date', function ($row) {
                return $row->intake_date ? $row->intake_date->translatedFormat(company()->date_format) : '-';
            })
            ->editColumn('shipment', function ($row) {
                return $row->shipment ? $row->shipment->shipment_number : '<span class="text-lightest">-</span>';
            })
            ->editColumn('warehouse', function ($row) {
                return $row->warehouse ? $row->warehouse->name : '-';
            })
            ->editColumn('status', function ($row) {
                $statusColors = [
                    'draft' => 'text-muted',
                    'pending' => 'text-warning',
                    'approved' => 'text-info',
                    'completed' => 'text-success',
                    'cancelled' => 'text-danger',
                ];
                $color = $statusColors[$row->status] ?? 'text-dark-grey';
                return '<i class="fa fa-circle mr-1 f-10 ' . $color . '"></i>' . ucwords($row->status);
            })
            ->addColumn('items_count', function ($row) {
                return '<span class="badge badge-light border px-2 py-1">' . $row->items_count . '</span>';
            })
            ->addIndexColumn()
            ->smart(false)
            ->setRowId(function ($row) {
                return 'row-' . $row->id;
            })
            ->rawColumns(['action', 'voucher_number', 'shipment', 'status', 'items_count', 'check']);
    }

    /**
     * Get query source of dataTable.
     */
    public function query(StockIntakeVoucher $model)
    {
        $request = $this->request();
        $query = StockIntakeVoucher::with(['shipment', 'warehouse'])
            ->withCount('items')
            ->where('company_id', company()->id)
            ->orderBy('stock_intake_vouchers.id', 'desc');

        if (!is_null($request->searchText)) {
            $query->where(function ($q) use ($request) {
                $q->where('voucher_number', 'like', '%' . $request->searchText . '%')
                    ->orWhere('remarks', 'like', '%' . $request->searchText . '%');
            });
        }

        if ($request->status != 'all' && !is_null($request->status)) {
            $query->where('status', $request->status);
        }

        return $query;
    }

    /**
     * Optional method if you want to use html builder.
     */
    public function html()
    {
        return $this->setBuilder('stock-intakes-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["stock-intakes-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'order' => [1, 'desc'],
            ])
            ->buttons(\Yajra\DataTables\Html\Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> ' . trans('app.exportExcel')]));
    }

    /**
     * Get columns.
     */
    protected function getColumns()
    {
        return [
            'check' => [
                'title'      => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                'exportable' => false,
                'orderable'  => false,
                'searchable' => false,
                'width'      => '50px'
            ],
            'voucher_number' => [
                'title' => 'Voucher Number',
            ],
            'shipment' => [
                'title' => 'Shipment',
                'exportable' => false,
                'orderable'  => false,
                'searchable' => false,
            ],
            'warehouse' => [
                'title' => 'Warehouse',
            ],
            'intake_date' => [
                'title' => 'Intake Date',
            ],
            'status' => [
                'title' => 'Status',
            ],
            'items_count' => [
                'title' => 'Items Count',
                'exportable' => false,
                'orderable'  => false,
                'searchable' => false,
            ],
            Column::computed('action', trans('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->width(150)
                ->addClass('text-right')
        ];
    }
}
