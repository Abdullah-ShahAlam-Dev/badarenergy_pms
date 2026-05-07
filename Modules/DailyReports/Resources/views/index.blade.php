@extends('layouts.app')

@push('styles')
@endpush

@section('filter-section')
@endsection

@section('content')
<div class="content-wrapper">

    {{-- Header / CTA --}}
    <div class="d-flex justify-content-between action-bar mb-3">
        <div id="table-actions" class="d-flex align-items-center">
            @php 
                $addDailyReportPermission = user()->permission('add_daily_report');
                $editDailyReportPermission = user()->permission('edit_daily_report');
                $deleteDailyReportPermission = user()->permission('delete_daily_report');
            @endphp
            @if($addDailyReportPermission == 'all' || $addDailyReportPermission == 'added')
                @if(!$todayReport)
                    <x-forms.link-primary :link="route('daily-reports.create')"
                        class="mr-3 openRightModal float-left" icon="plus">
                        @lang('app.add') Report
                    </x-forms.link-primary>
                @else
                    <span class="badge badge-success f-14 p-2 mr-3">
                        <i class="fa fa-check-circle mr-1"></i> Today's Report Submitted
                    </span>
                    <a href="{{ route('daily-reports.show', $todayReport->id) }}"
                        class="btn btn-outline-primary btn-sm openRightModal mr-2">
                        <i class="fa fa-eye mr-1"></i> View
                    </a>
                    @if($editDailyReportPermission == 'all' || ($editDailyReportPermission == 'owned' && $todayReport->created_at && $todayReport->created_at->isToday()))
                        <a href="{{ route('daily-reports.edit', $todayReport->id) }}"
                            class="btn btn-outline-secondary btn-sm openRightModal mr-2">
                            <i class="fa fa-edit mr-1"></i> Edit
                        </a>
                    @endif
                    @if($deleteDailyReportPermission == 'all' || ($deleteDailyReportPermission == 'owned' && $todayReport->created_at && $todayReport->created_at->isToday()))
                        <button class="btn btn-outline-danger btn-sm delete-report" data-id="{{ $todayReport->id }}">
                            <i class="fa fa-trash mr-1"></i> Delete
                        </button>
                    @endif
                @endif
            @endif
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card b-shadow-4 border-0 rounded p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary rounded p-3 mr-3">
                        <i class="fa fa-file-alt text-white fa-lg"></i>
                    </div>
                    <div>
                        <p class="mb-0 text-lightest f-12">Total Reports</p>
                        <h4 class="mb-0 font-weight-bold">{{ $reports->total() }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card b-shadow-4 border-0 rounded p-3">
                <div class="d-flex align-items-center">
                    <div class="{{ $todayReport ? 'bg-success' : 'bg-warning' }} rounded p-3 mr-3">
                        <i class="fa fa-{{ $todayReport ? 'check' : 'clock' }} text-white fa-lg"></i>
                    </div>
                    <div>
                        <p class="mb-0 text-lightest f-12">Today's Status</p>
                        <h4 class="mb-0 font-weight-bold">
                            {{ $todayReport ? 'Submitted' : 'Pending' }}
                        </h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card b-shadow-4 border-0 rounded p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-info rounded p-3 mr-3">
                        <i class="fa fa-calendar-check text-white fa-lg"></i>
                    </div>
                    <div>
                        <p class="mb-0 text-lightest f-12">This Month</p>
                        <h4 class="mb-0 font-weight-bold">
                            {{ $reports->filter(fn($r) => $r->report_date->isCurrentMonth())->count() }} reports
                        </h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Reports Table --}}
    <div class="d-flex flex-column w-tables bg-white rounded">
        <div class="p-20">
            <h5 class="f-16 font-weight-bold mb-3">My Report History</h5>
            <div class="table-responsive">
                <table class="table table-hover border-bottom">
                    <thead class="bg-light">
                        <tr>
                            <th>Date</th>
                            <th>Day</th>
                            <th>Total Hours Logged</th>
                            <th>Work Summary</th>
                            <th>Blockers</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                            <tr>
                                <td class="font-weight-bold">
                                    {{ $report->report_date->format(company()->date_format) }}
                                </td>
                                <td class="text-lightest">{{ $report->report_date->format('l') }}</td>
                                <td>
                                    <span class="badge badge-light-blue">
                                        {{ $report->total_hours }}
                                    </span>
                                </td>
                                <td style="max-width:350px" class="text-dark-grey">
                                    {{ Str::limit(strip_tags($report->summary), 100) }}
                                </td>
                                <td>
                                    @if($report->blockers && strip_tags($report->blockers) != '')
                                        <span class="text-danger f-12">
                                            <i class="fa fa-exclamation-triangle mr-1"></i>
                                            {{ Str::limit(strip_tags($report->blockers), 50) }}
                                        </span>
                                    @else
                                        <span class="text-success f-12">@lang('app.none')</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('daily-reports.show', $report->id) }}"
                                        class="btn btn-sm btn-outline-primary openRightModal" title="View">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    @if($editDailyReportPermission == 'all' || ($editDailyReportPermission == 'owned' && $report->created_at && $report->created_at->isToday()))
                                        <a href="{{ route('daily-reports.edit', $report->id) }}"
                                            class="btn btn-sm btn-outline-secondary openRightModal ml-1" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                    @endif
                                    @if($deleteDailyReportPermission == 'all' || ($deleteDailyReportPermission == 'owned' && $report->created_at && $report->created_at->isToday()))
                                        <a href="javascript:;" class="btn btn-sm btn-outline-danger delete-report ml-1" data-id="{{ $report->id }}" title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-lightest">
                                        <i class="fa fa-file-alt fa-3x mb-3 d-block"></i>
                                        No reports submitted yet. Submit your first report!
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-end mt-3">
                {{ $reports->links() }}
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('body').on('click', '.delete-report', function() {
            var id = $(this).data('id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.recoverRecord')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmDelete')",
                cancelButtonText: "@lang('app.cancel')",
                customClass: {
                    confirmButton: 'btn btn-primary mr-3',
                    cancelButton: 'btn btn-secondary'
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    var url = "{{ route('daily-reports.destroy', ':id') }}";
                    url = url.replace(':id', id);
                    
                    var token = "{{ csrf_token() }}";
                    
                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {'_token': token, '_method': 'DELETE'},
                        success: function (response) {
                            if (response.status == "success") {
                                window.location.reload();
                            }
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
