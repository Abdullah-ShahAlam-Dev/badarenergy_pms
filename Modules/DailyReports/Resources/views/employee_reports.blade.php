@extends('layouts.app')

@push('datatable-styles')
    <style>
        .employee-card {
            transition: all 0.3s ease;
            border: 1px solid #f1f1f1;
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
        }
        .employee-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            border-color: var(--header_color);
        }
        .emp-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .stat-badge {
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 600;
        }
        .last-report {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 10px;
            margin-top: 15px;
        }
        .view-btn.active {
            background: var(--header_color);
            color: #fff;
            border-color: var(--header_color);
        }
    </style>
@endpush

@section('content')
    <div class="content-wrapper">
        <div class="d-block d-lg-flex d-md-flex justify-content-between action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center">
                <h2 class="f-21 font-weight-bold mb-0">Employee Wise Reports</h2>
                <p class="text-lightest f-13 mb-0">Overview of daily report submissions for <strong>{{ count($employees) }}</strong> employees</p>
            </div>
            
            <div class="mt-2 mt-md-0 d-flex align-items-center">
                <div class="btn-group mr-3" role="group">
                    <button type="button" class="btn btn-secondary btn-sm view-btn active" id="show-grid" title="Grid View">
                        <i class="fa fa-th-large"></i>
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm view-btn" id="show-list" title="List View">
                        <i class="fa fa-list"></i>
                    </button>
                </div>

                <x-forms.link-secondary :link="route('reports.daily-reports')" icon="list">
                    View All Reports
                </x-forms.link-secondary>
            </div>
        </div>

        {{-- Grid View --}}
        <div class="row mt-4" id="employee-grid">
            @foreach($employees as $employee)
                <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                    <div class="employee-card h-100 p-4 d-flex flex-column">
                        <div class="text-center mb-3">
                            <img src="{{ $employee->image_url }}" class="emp-avatar mb-3" alt="{{ $employee->name }}">
                            <h4 class="f-16 font-weight-bold text-dark mb-1">{{ $employee->name }}</h4>
                            <p class="text-lightest f-12 mb-2">
                                {{ $employee->employeeDetail->designation->name ?? '—' }} | 
                                {{ $employee->employeeDetail->department->team_name ?? '—' }}
                            </p>
                            <div class="d-flex justify-content-center">
                                <span class="stat-badge bg-light-blue text-blue mr-2">
                                    {{ $employee->daily_reports_count }} Total Reports
                                </span>
                            </div>
                        </div>

                        <div class="last-report mt-auto">
                            <p class="f-11 text-lightest text-uppercase font-weight-bold mb-1">Last Submission</p>
                            @if($employee->dailyReports->first())
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="f-13 text-dark-grey">
                                        <i class="fa fa-calendar-check mr-1 text-success"></i>
                                        {{ $employee->dailyReports->first()->report_date->translatedFormat(company()->date_format) }}
                                        @if($employee->dailyReports->first()->files->count() > 0)
                                            <i class="fa fa-paperclip ml-1 text-primary" data-toggle="tooltip" title="Has Attachments"></i>
                                        @endif
                                    </span>
                                    <span class="f-11 text-lightest">
                                        {{ $employee->dailyReports->first()->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            @else
                                <p class="f-13 text-red mb-0">
                                    <i class="fa fa-exclamation-circle mr-1"></i>Never Submitted
                                </p>
                            @endif
                        </div>

                        <div class="mt-3">
                            <a href="{{ route('reports.daily-reports') }}?employee={{ $employee->id }}" 
                               class="btn btn-block btn-outline-light-grey f-14 rounded-pill">
                                View History <i class="fa fa-chevron-right ml-1 f-10"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- List View (Initially Hidden) --}}
        <div class="d-none mt-4 bg-white rounded shadow-sm overflow-hidden" id="employee-list">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="pl-20">Employee</th>
                        <th>Designation</th>
                        <th>Department</th>
                        <th class="text-center">Total Reports</th>
                        <th>Last Submission</th>
                        <th class="text-right pr-20">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $employee)
                        <tr>
                            <td class="pl-20">
                                <x-employee :user="$employee" />
                            </td>
                            <td class="f-13 text-dark-grey">{{ $employee->employeeDetail->designation->name ?? '—' }}</td>
                            <td class="f-13 text-dark-grey">{{ $employee->employeeDetail->department->team_name ?? '—' }}</td>
                            <td class="text-center">
                                <span class="badge badge-light-blue">{{ $employee->daily_reports_count }}</span>
                            </td>
                            <td>
                                @if($employee->dailyReports->first())
                                    <span class="f-13 text-dark-grey">
                                        {{ $employee->dailyReports->first()->report_date->translatedFormat(company()->date_format) }}
                                        @if($employee->dailyReports->first()->files->count() > 0)
                                            <i class="fa fa-paperclip ml-1 text-primary" data-toggle="tooltip" title="Has Attachments"></i>
                                        @endif
                                    </span>
                                    <small class="text-lightest d-block">{{ $employee->dailyReports->first()->created_at->diffForHumans() }}</small>
                                @else
                                    <span class="f-13 text-red">Never</span>
                                @endif
                            </td>
                            <td class="text-right pr-20">
                                <a href="{{ route('reports.daily-reports') }}?employee={{ $employee->id }}" 
                                   class="btn btn-sm btn-outline-secondary rounded-pill">
                                    View Reports
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            var $doc = $(document);
            var namespace = '.drEmployeeWise';
            const STORAGE_KEY = 'dr_employee_view_pref';
            
            $doc.off(namespace);

            function setView(view) {
                if (view === 'list') {
                    $('#employee-grid').addClass('d-none');
                    $('#employee-list').removeClass('d-none');
                    $('#show-list').addClass('active');
                    $('#show-grid').removeClass('active');
                } else {
                    $('#employee-list').addClass('d-none');
                    $('#employee-grid').removeClass('d-none');
                    $('#show-grid').addClass('active');
                    $('#show-list').removeClass('active');
                }
                localStorage.setItem(STORAGE_KEY, view);
            }

            // Init from storage
            const pref = localStorage.getItem(STORAGE_KEY);
            if (pref) setView(pref);

            $doc.on('click' + namespace, '#show-grid', function() { setView('grid'); });
            $doc.on('click' + namespace, '#show-list', function() { setView('list'); });
        })();
    </script>
@endpush
