<?php

namespace Modules\CRMEmail\DataTables;

use App\DataTables\BaseDataTable;
use Modules\CRMEmail\Entities\EmailMarketingTemplate;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class EmailTemplateDataTable extends BaseDataTable
{
    private $editPermission;
    private $deletePermission;
    private $addPermission;

    public function __construct()
    {
        parent::__construct();
        $this->editPermission = user()->permission('edit_crm_email');
        $this->deletePermission = user()->permission('delete_crm_email');
        $this->addPermission = user()->permission('add_crm_email');
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
            ->editColumn('title', function ($row) {
                $editUrl = route('crm-email-templates.edit', $row->id);
                return '<h5 class="mb-0 f-13 text-darkest-grey">
                            <a href="' . $editUrl . '" class="openRightModal">' . ucfirst($row->title) . '</a>
                        </h5>';
            })
            ->editColumn('status', function ($row) {
                if ($row->status == 'active') {
                    return '<span class="badge badge-success"><i class="fa fa-circle mr-1 text-success f-10"></i>' . __('app.active') . '</span>';
                }
                return '<span class="badge badge-danger"><i class="fa fa-circle mr-1 text-danger f-10"></i>' . __('app.inactive') . '</span>';
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

                // Preview Action (centered Modal)
                $action .= '<a href="javascript:;" class="dropdown-item preview-template" data-template-id="' . $row->id . '">
                            <i class="fa fa-eye mr-2"></i>' . __('app.preview') . '
                           </a>';

                // Edit Action
                if ($this->editPermission == 'all' || ($this->editPermission == 'added' && $row->added_by == user()->id)) {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('crm-email-templates.edit', $row->id) . '">
                                <i class="fa fa-edit mr-2"></i>' . __('app.edit') . '
                               </a>';
                }

                // Duplicate Action
                if ($this->addPermission == 'all' || ($this->addPermission == 'added' && $row->added_by == user()->id)) {
                    $action .= '<a class="dropdown-item duplicate-template" href="javascript:;" data-template-id="' . $row->id . '">
                                <i class="fa fa-clone mr-2"></i>' . __('app.duplicate') . '
                               </a>';
                }

                // Delete Action
                if ($this->deletePermission == 'all' || ($this->deletePermission == 'added' && $row->added_by == user()->id)) {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-template-id="' . $row->id . '">
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
            ->rawColumns(['check', 'title', 'status', 'added_by', 'action']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param EmailMarketingTemplate $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(EmailMarketingTemplate $model)
    {
        $query = $model->newQuery()->with('addedBy');

        if (request()->searchText != '') {
            $query->where(function($q) {
                $q->where('title', 'like', '%' . request()->searchText . '%')
                  ->orWhere('subject', 'like', '%' . request()->searchText . '%');
            });
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
        return $this->setBuilder('crm-email-templates-table')
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["crm-email-templates-table"].buttons().container()
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
            __('modules.emailMarketing.templateName') => ['data' => 'title', 'name' => 'title', 'exportable' => true, 'title' => 'Template Name'],
            __('modules.emailMarketing.subject') => ['data' => 'subject', 'name' => 'subject', 'exportable' => true, 'title' => 'Subject'],
            __('app.status') => ['data' => 'status', 'name' => 'status', 'exportable' => true, 'title' => 'Status'],
            __('app.addedBy') => ['data' => 'added_by', 'name' => 'added_by', 'exportable' => true, 'title' => 'Created By'],
            __('app.createdOn') => ['data' => 'created_at', 'name' => 'created_at', 'exportable' => true, 'title' => 'Created Date'],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];
    }
}
