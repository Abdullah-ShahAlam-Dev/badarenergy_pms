<?php

namespace Modules\DailyReports\DataTables;

use App\DataTables\BaseDataTable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Modules\DailyReports\Entities\DailyReport;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class DailyReportDataTable extends BaseDataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->editColumn('user_id', function ($row) {
                return view('components.employee', ['user' => $row->user]);
            })
            ->editColumn('report_date', function ($row) {
                return $row->report_date->format(company()->date_format);
            })
            ->addColumn('submitted_at', function ($row) {
                return $row->created_at->timezone(company()->timezone)
                    ->format(company()->date_format . ' ' . company()->time_format);
            })
            ->addColumn('total_hours', function ($row) {
                return '<span class="badge badge-light-blue">' . $row->total_hours . '</span>';
            })
            ->editColumn('summary', function ($row) {
                return '<div class="text-wrap" style="max-width:350px">'
                    . e(\Str::limit(strip_tags($row->summary), 120))
                    . '</div>';
            })
            ->addColumn('blockers', function ($row) {
                $text = strip_tags($row->blockers ?? '');
                if (!$text || trim($text) === '') {
                    return '<span class="text-success f-12"><i class="fa fa-check-circle mr-1"></i>None</span>';
                }
                return '<span class="text-danger f-12"><i class="fa fa-exclamation-triangle mr-1"></i>'
                    . e(\Str::limit($text, 80))
                    . '</span>';
            })
            ->addColumn('files', function ($row) {
                if ($row->files->count() > 0) {
                    return '<i class="fa fa-paperclip text-primary mr-1"></i> ' . $row->files->count();
                }
                return '--';
            })
            ->addColumn('action', function ($row) {
                return '<a href="' . route('daily-reports.show', [$row->id]) . '"
                        class="btn btn-sm btn-primary openRightModal"
                        data-redirect-url="' . route('daily-reports.show', [$row->id]) . '">
                        <i class="fa fa-eye mr-1"></i>View
                    </a>';
            })
            ->rawColumns(['user_id', 'summary', 'blockers', 'total_hours', 'files', 'action']);
    }

    public function query(DailyReport $model)
    {
        $request = $this->request();
        $query = $model->with('user', 'user.employeeDetail', 'user.employeeDetail.designation');

        if ($request->employee && $request->employee != 'all') {
            $query->where('user_id', $request->employee);
        }

        if ($request->department && $request->department != 'all') {
            $query->whereHas('user.employeeDetail', function ($q) use ($request) {
                $q->where('department_id', $request->department);
            });
        }

        if ($request->startDate && $request->startDate != 'null' && $request->startDate != '') {
            $startDate = Carbon::createFromFormat(company()->date_format, $request->startDate)->toDateString();
            $query->whereDate('report_date', '>=', $startDate);
        }

        if ($request->endDate && $request->endDate != 'null' && $request->endDate != '') {
            $endDate = Carbon::createFromFormat(company()->date_format, $request->endDate)->toDateString();
            $query->whereDate('report_date', '<=', $endDate);
        }

        return $query->orderBy('report_date', 'desc');
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('daily-report-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1, 'desc')
            ->destroy(true)
            ->responsive(true)
            ->serverSide(true)
            ->stateSave(false)
            ->processing(true)
            ->language(__('app.datatable'))
            ->buttons(
                Button::make(['extend' => 'export', 'buttons' => ['excel', 'csv'], 'className' => 'btn-sm'])
            )
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["daily-report-table"].buttons().container()
                        .appendTo( "#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("body").tooltip({ selector: \'[data-toggle="tooltip"]\' })
                }',
            ]);
    }

    protected function getColumns()
    {
        return [
            Column::make('user_id')->title('Employee')->width(180),
            Column::make('report_date')->title('Date')->width(110),
            Column::computed('submitted_at')->title('Submitted At')->width(145),
            Column::computed('total_hours')->title('Hours Logged')->width(110),
            Column::make('summary')->title('Work Summary'),
            Column::computed('blockers')->title('Blockers')->width(160),
            Column::computed('files')->title('Files')->width(80),
            Column::computed('action')->title('Action')
                ->exportable(false)->printable(false)
                ->orderable(false)->searchable(false)
                ->width(90)->addClass('text-center'),
        ];
    }
}
