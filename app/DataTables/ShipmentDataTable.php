<?php

namespace App\DataTables;

use App\DataTables\BaseDataTable;
use App\Models\Shipment;
use Yajra\DataTables\Html\Column;

class ShipmentDataTable extends BaseDataTable
{
    private $viewShipmentsPermission;
    private $editShipmentsPermission;
    private $deleteShipmentsPermission;

    public function __construct()
    {
        parent::__construct();
        $this->viewShipmentsPermission = user()->permission('view_shipments');
        $this->editShipmentsPermission = user()->permission('edit_shipments');
        $this->deleteShipmentsPermission = user()->permission('delete_shipments');
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

                if ($this->viewShipmentsPermission == 'all' || in_array('admin', user_roles())) {
                    $action .= '<a class="dropdown-item" href="' . route('shipments.show', [$row->id]) . '">
                                <i class="fa fa-eye mr-2"></i>
                                ' . trans('app.view') . '
                            </a>';
                }

                if ($this->editShipmentsPermission == 'all' || in_array('admin', user_roles())) {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('shipments.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
                }

                if ($this->deleteShipmentsPermission == 'all' || in_array('admin', user_roles())) {
                    $hasIntake = $row->stock_intake_vouchers_count > 0;
                    if (!$hasIntake) {
                        $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-shipment-id="' . $row->id . '">
                                    <i class="fa fa-trash mr-2"></i>
                                    ' . trans('app.delete') . '
                                </a>';
                    }
                }

                $action .= '</div>
                    </div>
                </div>';

                return $action;
            })
            ->editColumn('shipment_number', function ($row) {
                return '<a class="text-darkest-grey font-weight-bold" href="' . route('shipments.show', $row->id) . '">'
                    . ucfirst($row->shipment_number) . '</a>';
            })
            ->editColumn('eta', function ($row) {
                return $row->eta ? $row->eta->translatedFormat(company()->date_format) : '-';
            })
            ->editColumn('arrival_date', function ($row) {
                return $row->arrival_date ? $row->arrival_date->translatedFormat(company()->date_format) : '-';
            })
            ->editColumn('status', function ($row) {
                $statusColors = [
                    'in_transit' => 'text-blue',
                    'port_customs' => 'text-warning',
                    'warehouse_receiving' => 'text-info',
                    'completed' => 'text-success',
                    'cancelled' => 'text-danger',
                ];
                $color = $statusColors[$row->status] ?? 'text-dark-grey';
                return '<i class="fa fa-circle mr-1 f-10 ' . $color . '"></i>' . ucwords(str_replace('_', ' ', $row->status));
            })
            ->addColumn('intake_count', function ($row) {
                return '<span class="badge badge-light border px-2 py-1">' . $row->stock_intake_vouchers_count . '</span>';
            })
            ->addIndexColumn()
            ->smart(false)
            ->setRowId(function ($row) {
                return 'row-' . $row->id;
            })
            ->rawColumns(['action', 'shipment_number', 'status', 'intake_count', 'check']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param Shipment $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Shipment $model)
    {
        $request = $this->request();
        $query = Shipment::withCount('stockIntakeVouchers')->where('company_id', company()->id);

        if (!is_null($request->searchText)) {
            $query->where(function ($q) use ($request) {
                $q->where('shipment_number', 'like', '%' . $request->searchText . '%')
                    ->orWhere('container_number', 'like', '%' . $request->searchText . '%')
                    ->orWhere('bill_of_lading', 'like', '%' . $request->searchText . '%')
                    ->orWhere('manufacturing_ref', 'like', '%' . $request->searchText . '%');
            });
        }

        if ($request->status != 'all' && !is_null($request->status)) {
            $query->where('status', $request->status);
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
        return $this->setBuilder('shipments-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["shipments-table"].buttons().container()
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
                'width'      => '50px'
            ],
            'shipment_number' => [
                'title' => 'Shipment Number',
            ],
            'container_number' => [
                'title' => 'Container Number',
            ],
            'bill_of_lading' => [
                'title' => 'Bill of Lading',
            ],
            'eta' => [
                'title' => 'ETA',
            ],
            'arrival_date' => [
                'title' => 'Arrival Date',
            ],
            'status' => [
                'title' => 'Status',
            ],
            'intake_count' => [
                'title' => 'Linked Intakes',
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
