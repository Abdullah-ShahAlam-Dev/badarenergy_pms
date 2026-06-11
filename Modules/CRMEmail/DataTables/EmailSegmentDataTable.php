<?php

namespace Modules\CRMEmail\DataTables;

use App\DataTables\BaseDataTable;
use Modules\CRMEmail\Entities\EmailSegment;
use Modules\CRMEmail\Services\SegmentResolverService;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class EmailSegmentDataTable extends BaseDataTable
{
    private $editPermission;
    private $deletePermission;
    private $addPermission;
    private $resolverService;

    public function __construct()
    {
        parent::__construct();
        $this->editPermission = user()->permission('edit_crm_email');
        $this->deletePermission = user()->permission('delete_crm_email');
        $this->addPermission = user()->permission('add_crm_email');
        $this->resolverService = new SegmentResolverService();
    }

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="select-table-row" id="datatable-row-' . $row->id . '" name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
            })
            ->editColumn('name', function ($row) {
                $editUrl = route('crm-email-segments.edit', $row->id);
                return '<h5 class="mb-0 f-13 text-darkest-grey">
                            <a href="' . $editUrl . '" class="openRightModal">' . ucfirst($row->name) . '</a>
                        </h5>';
            })
            ->editColumn('sources', function ($row) {
                $sources = $row->sources ?: [];
                $html = '';
                foreach ($sources as $source) {
                    $class = 'badge-info';
                    if ($source === 'leads') {
                        $class = 'badge-warning text-dark';
                    } elseif ($source === 'contacts') {
                        $class = 'badge-success';
                    }
                    $html .= '<span class="badge ' . $class . ' mr-1">' . ucfirst($source) . '</span>';
                }
                return $html ?: '--';
            })
            ->addColumn('size', function ($row) {
                try {
                    $count = $this->resolverService->estimateCount($row->sources ?: [], $row->criteria ?: []);
                    return '<span class="badge badge-primary">' . $count . '</span>';
                } catch (\Exception $e) {
                    return '<span class="badge badge-secondary" data-toggle="tooltip" title="' . e($e->getMessage()) . '">Error</span>';
                }
            })
            ->editColumn('added_by', function ($row) {
                if ($row->addedBy) {
                    $profileUrl = route('employees.show', $row->addedBy->id);
                    return '<div class="media align-items-center">
                                <a href="' . $profileUrl . '">
                                    <img src="' . $row->addedBy->image_url . '" class="mr-3 taskEmployeeImg rounded-circle" alt="' . $row->addedBy->name . '" width="26" height="26">
                                </a>
                                <div class="media-body">
                                    <h5 class="mb-0 f-13 text-darkest-grey"><a href="' . $profileUrl . '">' . $row->addedBy->name . '</a></h5>
                                </div>
                            </div>';
                }
                return '--';
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at->format(company()->date_format);
            })
            ->addColumn('action', function ($row) {
                $action = '<div class="task_view">
                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                // Edit Action
                if ($this->editPermission == 'all' || ($this->editPermission == 'added' && $row->added_by == user()->id)) {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('crm-email-segments.edit', $row->id) . '">
                                <i class="fa fa-edit mr-2"></i>' . __('app.edit') . '
                               </a>';
                }

                // Duplicate Action
                if ($this->addPermission == 'all' || ($this->addPermission == 'added' && $row->added_by == user()->id)) {
                    $action .= '<a class="dropdown-item duplicate-segment" href="javascript:;" data-segment-id="' . $row->id . '">
                                <i class="fa fa-clone mr-2"></i>' . __('app.duplicate') . '
                               </a>';
                }

                // Delete Action
                if ($this->deletePermission == 'all' || ($this->deletePermission == 'added' && $row->added_by == user()->id)) {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-segment-id="' . $row->id . '">
                                <i class="fa fa-trash mr-2"></i>' . __('app.delete') . '
                               </a>';
                }

                $action .= '</div>
                    </div>
                </div>';

                return $action;
            })
            ->addIndexColumn()
            ->smart(false)
            ->setRowId(function ($row) {
                return 'row-' . $row->id;
            })
            ->rawColumns(['check', 'name', 'sources', 'size', 'added_by', 'action']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param EmailSegment $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(EmailSegment $model)
    {
        $query = $model->newQuery()->with('addedBy');

        if (request()->searchText != '') {
            $query->where('name', 'like', '%' . request()->searchText . '%');
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
        return $this->setBuilder('crm-email-segments-table')
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["crm-email-segments-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("body").tooltip({
                        selector: \'[data-toggle="tooltip"]\'
                    });
                }',
            ])
            ->buttons(Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> ' . trans('app.exportExcel')]));
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
                'title' => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                'exportable' => false,
                'orderable' => false,
                'searchable' => false
            ],
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            'name' => ['data' => 'name', 'name' => 'name', 'exportable' => true, 'title' => 'Segment Name'],
            'sources' => ['data' => 'sources', 'name' => 'sources', 'exportable' => true, 'title' => 'Sources'],
            'size' => ['data' => 'size', 'name' => 'size', 'exportable' => false, 'orderable' => false, 'title' => 'Estimated Size'],
            'added_by' => ['data' => 'added_by', 'name' => 'added_by', 'exportable' => true, 'title' => 'Created By'],
            'created_at' => ['data' => 'created_at', 'name' => 'created_at', 'exportable' => true, 'title' => 'Created Date'],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];
    }
}
