<?php

namespace Modules\CRMEmail\DataTables;

use App\DataTables\BaseDataTable;
use Modules\CRMEmail\Entities\Campaign;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class CampaignDataTable extends BaseDataTable
{
    private $editPermission;
    private $deletePermission;
    private $addPermission;

    public function __construct()
    {
        parent::__construct();
        $this->editPermission   = user()->permission('edit_crm_email');
        $this->deletePermission = user()->permission('delete_crm_email');
        $this->addPermission    = user()->permission('add_crm_email');
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
                $editUrl = route('crm-email-campaigns.edit', $row->id);
                return '<h5 class="mb-0 f-13 text-darkest-grey">
                            <a href="' . $editUrl . '" class="openRightModal">' . e($row->name) . '</a>
                        </h5>';
            })
            ->editColumn('status', function ($row) {
                return $this->renderStatusBadge($row->status);
            })
            ->addColumn('recipients', function ($row) {
                $total  = $row->emails()->count();
                $sent   = $row->emails()->where('status', 'sent')->count();
                $failed = $row->emails()->where('status', 'failed')->count();

                if ($total === 0) {
                    return '<span class="text-muted">--</span>';
                }

                return '<span class="badge badge-secondary">' . $total . ' total</span> '
                    . '<span class="badge badge-success">' . $sent . ' sent</span> '
                    . ($failed > 0 ? '<span class="badge badge-danger">' . $failed . ' failed</span>' : '');
            })
            ->editColumn('launched_at', function ($row) {
                return $row->launched_at ? $row->launched_at->format(company()->date_format) : '<span class="text-muted">--</span>';
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at->format(company()->date_format);
            })
            ->addColumn('action', function ($row) {
                return $this->renderActionMenu($row);
            })
            ->addIndexColumn()
            ->smart(false)
            ->setRowId(function ($row) {
                return 'row-' . $row->id;
            })
            ->rawColumns(['check', 'name', 'status', 'recipients', 'launched_at', 'action']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param Campaign $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Campaign $model)
    {
        $query = $model->newQuery()->with(['template', 'segment', 'addedBy']);

        if (request()->searchText != '') {
            $query->where('name', 'like', '%' . request()->searchText . '%');
        }

        if (request()->status && request()->status !== 'all') {
            $query->where('status', request()->status);
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
        return $this->setBuilder('crm-email-campaigns-table')
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["crm-email-campaigns-table"].buttons().container()
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
                'title'      => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                'exportable' => false,
                'orderable'  => false,
                'searchable' => false,
            ],
            '#'           => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            'Campaign'    => ['data' => 'name', 'name' => 'name', 'exportable' => true, 'title' => 'Campaign'],
            'Status'      => ['data' => 'status', 'name' => 'status', 'exportable' => true, 'title' => 'Status'],
            'Recipients'  => ['data' => 'recipients', 'name' => 'recipients', 'exportable' => false, 'orderable' => false, 'searchable' => false, 'title' => 'Recipients'],
            'Launched'    => ['data' => 'launched_at', 'name' => 'launched_at', 'exportable' => true, 'title' => 'Launched'],
            'Created'     => ['data' => 'created_at', 'name' => 'created_at', 'exportable' => true, 'title' => 'Created'],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20'),
        ];
    }

    /**
     * Render the status badge HTML.
     *
     * @param string $status
     * @return string
     */
    private function renderStatusBadge(string $status): string
    {
        $map = [
            'draft'     => ['secondary', 'Draft'],
            'scheduled' => ['info', 'Scheduled'],
            'sending'   => ['warning', 'Sending'],
            'completed' => ['success', 'Completed'],
            'paused'    => ['dark', 'Paused'],
            'canceled'  => ['danger', 'Canceled'],
        ];

        [$color, $label] = $map[$status] ?? ['secondary', ucfirst($status)];

        return '<span class="badge badge-' . $color . '">'
            . '<i class="fa fa-circle mr-1 text-' . $color . ' f-10"></i>' . $label
            . '</span>';
    }

    /**
     * Render the dropdown action menu for a campaign row.
     *
     * @param Campaign $row
     * @return string
     */
    private function renderActionMenu(Campaign $row): string
    {
        $action = '<div class="task_view">
            <div class="dropdown">
                <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                    id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="icon-options-vertical icons"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

        // View Stats
        $action .= '<a href="javascript:;" class="dropdown-item view-campaign-stats" data-campaign-id="' . $row->id . '">
                    <i class="fa fa-chart-bar mr-2"></i>Stats
                   </a>';

        // Launch — only for draft/scheduled campaigns
        if (
            in_array($row->status, ['draft', 'scheduled'])
            && ($this->addPermission == 'all' || ($this->addPermission == 'added' && $row->added_by == user()->id))
        ) {
            $action .= '<a href="javascript:;" class="dropdown-item launch-campaign" data-campaign-id="' . $row->id . '">
                        <i class="fa fa-paper-plane mr-2"></i>Launch
                       </a>';
        }

        // Pause — only for actively sending campaigns
        if (
            $row->status === 'sending'
            && ($this->editPermission == 'all' || ($this->editPermission == 'added' && $row->added_by == user()->id))
        ) {
            $action .= '<a href="javascript:;" class="dropdown-item pause-campaign" data-campaign-id="' . $row->id . '">
                        <i class="fa fa-pause mr-2"></i>Pause
                       </a>';
        }

        // Edit — only for draft campaigns
        if (
            $row->status === 'draft'
            && ($this->editPermission == 'all' || ($this->editPermission == 'added' && $row->added_by == user()->id))
        ) {
            $action .= '<a class="dropdown-item openRightModal" href="' . route('crm-email-campaigns.edit', $row->id) . '">
                        <i class="fa fa-edit mr-2"></i>' . __('app.edit') . '
                       </a>';
        }

        // Delete — only for non-sending campaigns
        if (
            !in_array($row->status, ['sending'])
            && ($this->deletePermission == 'all' || ($this->deletePermission == 'added' && $row->added_by == user()->id))
        ) {
            $action .= '<a class="dropdown-item delete-campaign-row" href="javascript:;" data-campaign-id="' . $row->id . '">
                        <i class="fa fa-trash mr-2"></i>' . __('app.delete') . '
                       </a>';
        }

        $action .= '</div>
            </div>
        </div>';

        return $action;
    }
}
