<?php

namespace App\DataTables;

use App\Models\Distributor;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class DistributorsDataTable extends BaseDataTable
{
    private $editDistributorPermission;
    private $deleteDistributorPermission;

    public function __construct()
    {
        parent::__construct();
        $this->editDistributorPermission = user()->permission('edit_distributors');
        $this->deleteDistributorPermission = user()->permission('delete_distributors');
    }

    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('action', function ($row) {
                $action = '<div class="task_view">';
                $action .= '<div class="dropdown">
                    <button class="btn btn-xs dropdown-toggle bg-white rounded f-14 p-1 text-dark-grey d-flex align-items-center" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-ellipsis-v"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0">';

                if (in_array('admin', user_roles()) || $this->editDistributorPermission == 'all') {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('distributors.edit', [$row->id]) . '"><i class="fa fa-edit mr-2"></i>' . __('app.edit') . '</a>';
                }

                if (in_array('admin', user_roles()) || $this->deleteDistributorPermission == 'all') {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-user-id="' . $row->id . '"><i class="fa fa-trash mr-2"></i>' . __('app.delete') . '</a>';
                }

                $action .= '</div>
                </div>';
                $action .= '</div>';
                return $action;
            })
            ->editColumn('name', function ($row) {
                return '<div class="media align-items-center"><div class="media-body"><h5 class="mb-0 f-13 text-darkest-grey">' . e($row->name) . '</h5></div></div>';
            })
            ->editColumn('company_name', function ($row) {
                return e($row->company_name ?: '--');
            })
            ->editColumn('email', function ($row) {
                return e($row->email ?: '--');
            })
            ->editColumn('phone', function ($row) {
                return e($row->phone ?: '--');
            })
            ->editColumn('status', function ($row) {
                if ($row->status == 'active') {
                    return '<i class="fa fa-circle mr-1 text-light-green f-10"></i>' . __('app.active');
                } else {
                    return '<i class="fa fa-circle mr-1 text-red f-10"></i>' . __('app.inactive');
                }
            })
            ->rawColumns(['name', 'action', 'status']);
    }

    public function query(Distributor $model)
    {
        $request = $this->request();
        $query = $model->newQuery();

        if ($request->searchText != '') {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->searchText . '%')
                    ->orWhere('company_name', 'like', '%' . $request->searchText . '%')
                    ->orWhere('email', 'like', '%' . $request->searchText . '%')
                    ->orWhere('phone', 'like', '%' . $request->searchText . '%');
            });
        }

        return $query;
    }

    public function html()
    {
        $dataTable = $this->setBuilder('distributors-table')
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["distributors-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("body").tooltip({
                        selector: \'[data-toggle="tooltip"]\'
                    })
                }',
            ]);

        $dataTable->buttons(Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export mr-1"></i>' . __('app.exportExcel')]));

        return $dataTable;
    }

    protected function getColumns()
    {
        return [
            '#' => ['data' => 'id', 'name' => 'id', 'visible' => false, 'title' => __('app.id')],
            __('app.name') => ['data' => 'name', 'name' => 'name', 'title' => __('app.name')],
            __('modules.client.companyName') => ['data' => 'company_name', 'name' => 'company_name', 'title' => __('modules.client.companyName')],
            __('app.email') => ['data' => 'email', 'name' => 'email', 'title' => __('app.email')],
            __('app.phone') => ['data' => 'phone', 'name' => 'phone', 'title' => __('app.phone')],
            __('app.status') => ['data' => 'status', 'name' => 'status', 'title' => __('app.status')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];
    }
}
