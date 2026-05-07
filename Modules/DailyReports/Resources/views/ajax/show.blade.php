<div class="row">
    <div class="col-sm-12">
        <div class="bg-white p-20">

            {{-- Header: Employee Info + Date --}}
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <x-employee :user="$report->user" />
                    @if($report->user->employeeDetail)
                        <p class="text-lightest f-12 mt-1 mb-0">
                            Department:
                            <strong>{{ $report->user->employeeDetail->department->team_name ?? '—' }}</strong>
                        </p>
                    @endif
                </div>
                <div class="text-right">
                    <p class="mb-0 f-13 font-weight-bold text-dark">
                        <i class="fa fa-calendar mr-1 text-primary"></i>
                        {{ $report->report_date->format('l') }},
                        {{ $report->report_date->format(company()->date_format) }}
                    </p>
                    <p class="mb-0 text-lightest f-12 mt-1">
                        <i class="fa fa-clock mr-1"></i>
                        Submitted: {{ $report->created_at->timezone(company()->timezone)->format(company()->date_format . ' ' . company()->time_format) }}
                    </p>
                    @if($report->total_logged_minutes > 0)
                        <span class="badge badge-light-blue mt-2">
                            <i class="fa fa-clock mr-1"></i>{{ $report->total_hours }} Logged
                        </span>
                    @else
                        <span class="badge badge-warning mt-2">No Hours Logged</span>
                    @endif

                    @if(in_array('admin', user_roles()) || ($report->created_at && now()->diffInHours($report->created_at) < 24 && user()->permission('add_daily_report') != 'none' && $report->user_id == user()->id))
                        <div class="mt-3">
                            <a href="{{ route('daily-reports.edit', $report->id) }}"
                                class="btn btn-outline-secondary btn-sm openRightModal">
                                <i class="fa fa-edit mr-1"></i> Edit Report
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <hr class="my-3 border-top-grey">

            {{-- Work Summary --}}
            <div class="mb-4">
                <h6 class="font-weight-bold text-dark mb-2 f-15">
                    <i class="fa fa-tasks text-primary mr-2"></i>Work Summary
                </h6>
                <div class="p-3 bg-light rounded f-14 text-dark-grey ql-editor p-0">{!! $report->summary !!}</div>
            </div>

            {{-- Blockers --}}
            <div class="mb-4">
                <h6 class="font-weight-bold text-dark mb-2 f-15">
                    <i class="fa fa-exclamation-triangle text-warning mr-2"></i>Blockers / Challenges
                </h6>
                @if($report->blockers && $report->blockers != '<p><br></p>')
                    <div class="p-3 bg-light rounded f-14 text-danger ql-editor p-0">{!! $report->blockers !!}</div>
                @else
                    <p class="text-success f-13 mb-0">
                        <i class="fa fa-check-circle mr-1"></i>No blockers reported
                    </p>
                @endif
            </div>

            {{-- Plan for Tomorrow --}}
            @if($report->next_plan && $report->next_plan != '<p><br></p>')
                <div class="mb-4">
                    <h6 class="font-weight-bold text-dark mb-2 f-15">
                        <i class="fa fa-arrow-right text-success mr-2"></i>Plan for Tomorrow
                    </h6>
                    <div class="p-3 bg-light rounded f-14 text-dark-grey ql-editor p-0">{!! $report->next_plan !!}</div>
                </div>
            @endif

            {{-- Linked Timelogs --}}
            <div class="mb-4">
                <h6 class="font-weight-bold text-dark mb-2 f-15">
                    <i class="fa fa-clock text-info mr-2"></i>Logged Activities
                </h6>
                @if($timelogs->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="f-12">#</th>
                                    <th class="f-12">Project</th>
                                    <th class="f-12">Task</th>
                                    <th class="f-12">Memo / Notes</th>
                                    <th class="text-right f-12">Duration</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($timelogs as $i => $log)
                                    <tr>
                                        <td class="text-lightest">{{ $i + 1 }}</td>
                                        <td>
                                            <span class="badge badge-soft-primary">
                                                {{ $log->project->project_name ?? '—' }}
                                            </span>
                                        </td>
                                        <td>{{ $log->task->heading ?? '—' }}</td>
                                        <td class="text-lightest f-12">{{ $log->memo ?: '—' }}</td>
                                        <td class="text-right font-weight-bold">{{ $log->hours }}</td>
                                    </tr>
                                @endforeach
                                <tr class="bg-light">
                                    <td colspan="4" class="text-right font-weight-bold">Total Logged</td>
                                    <td class="text-right font-weight-bold text-primary">
                                        {{ $report->total_hours }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-lightest f-13 mb-0">
                        <i class="fa fa-info-circle mr-1"></i>No timelog entries found for this date.
                    </p>
                @endif
            </div>

            {{-- File Attachments --}}
            @if($report->files->count() > 0)
                <div class="mt-4">
                    <h6 class="font-weight-bold text-dark mb-3 f-15">
                        <i class="fa fa-paperclip text-primary mr-2"></i>Attachments
                    </h6>
                    <div class="d-flex flex-wrap">
                        @foreach($report->files as $file)
                            <x-file-card :fileName="$file->filename" :dateAdded="$file->created_at->diffForHumans()">
                                @if ($file->icon == 'images')
                                    <img src="{{ $file->file_url }}">
                                @else
                                    <i class="fa {{ $file->icon }} text-lightest"></i>
                                @endif
                                <x-slot name="action">
                                    <div class="dropdown ml-auto file-action">
                                        <button class="btn btn-lg f-14 p-0 text-lightest text-capitalize rounded  dropdown-toggle"
                                            type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fa fa-ellipsis-h"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                            aria-labelledby="dropdownMenuLink" tabindex="0">
                                            <a class="dropdown-item" href="{{ $file->file_url }}" target="_blank">@lang('app.view')</a>
                                            <a class="dropdown-item" href="{{ route('daily-reports.download_file', $file->id) }}">@lang('app.download')</a>
                                        </div>
                                    </div>
                                </x-slot>
                            </x-file-card>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
