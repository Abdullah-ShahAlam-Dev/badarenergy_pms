@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')
    <x-filters.filter-box>
        {{-- Date Range --}}
        <div class="select-box d-flex pr-2 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-3 f-14 text-dark-grey d-flex align-items-center">@lang('app.duration')</p>
            <div class="select-status d-flex">
                <input type="text" class="position-relative text-dark f-14 border-0 p-2"
                    id="datatableRange" placeholder="@lang('placeholders.dateRange')">
            </div>
        </div>

        {{-- Employee --}}
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-3 f-14 text-dark-grey d-flex align-items-center">@lang('app.employee')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="employee" id="dr-employee"
                    data-live-search="true" data-size="8">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($employees as $emp)
                        <x-user-option :user="$emp" :selected="request('employee') == $emp->id" />
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Department --}}
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-3 f-14 text-dark-grey d-flex align-items-center">@lang('app.department')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="department" id="dr-department"
                    data-live-search="true" data-size="8">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->team_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Buttons --}}
        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary id="dr-reset-btn" class="btn-xs d-none" icon="times-circle">@lang('app.clearFilters')</x-forms.button-secondary>
        </div>
    </x-filters.filter-box>
@endsection

@section('content')
    <div class="content-wrapper">
        <!-- Add Task -->
        <div class="d-block d-lg-flex d-md-flex justify-content-between action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center">
                @php $isAdmin = in_array('admin', user_roles()); @endphp
                @if($isAdmin || user()->permission('add_daily_report') != 'none')
                    <x-forms.link-primary :link="route('daily-reports.create')"
                        class="mr-3 openRightModal float-left" icon="plus">
                        Submit My Report
                    </x-forms.link-primary>
                @endif
                
                <a href="{{ route('daily-reports.missing') }}" class="btn btn-outline-danger f-14 float-left mr-3">
                    <i class="fa fa-user-clock mr-1"></i> Missing Reports Tracker
                </a>

                <a href="{{ route('reports.daily-reports.employee-wise') }}" class="btn btn-outline-info f-14 float-left">
                    <i class="fa fa-users mr-1"></i> Employee Wise Reports
                </a>
            </div>
            
            <div class="mt-2 mt-md-0">
                <span class="text-lightest f-12">
                    <i class="fa fa-info-circle mr-1"></i> Data as of {{ now(company()->timezone)->format(company()->time_format) }}
                </span>
            </div>
        </div>

        {{-- Stats Row --}}
        <div class="row mt-4 mb-4">
            <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                <x-cards.data :title="__('Total Employees')" :value="$totalEmployees" icon="users" />
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                <x-cards.data :title="__('Submitted Today')" :value="$submittedToday" icon="check-double" />
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                <x-cards.data :title="__('Missing Today')" :value="$missingToday" icon="user-clock" color="text-red" />
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                <div class="bg-white p-20 rounded b-shadow-4 d-flex justify-content-between align-items-center">
                    <div class="d-block text-capitalize">
                        <h5 class="f-15 f-w-500 mb-2 text-darkest-grey">Compliance Rate</h5>
                        <div class="d-flex align-items-center">
                            <h3 class="f-21 f-w-700 mb-0 mr-2">{{ $complianceRate }}%</h3>
                            <div class="progress flex-grow-1" style="height: 6px; width: 60px;">
                                <div class="progress-bar {{ $complianceRate < 70 ? 'bg-danger' : ($complianceRate < 90 ? 'bg-warning' : 'bg-success') }}" 
                                     role="progressbar" style="width: {{ $complianceRate }}%" 
                                     aria-valuenow="{{ $complianceRate }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                    <div class="column-visible-box">
                        <i class="fa fa-chart-line f-27 text-lightest"></i>
                    </div>
                </div>
            </div>
        </div>

        @if($missingToday > 0)
            <div class="alert alert-soft-danger border-0 d-flex align-items-center mb-4 p-3 rounded">
                <i class="fa fa-bell fa-lg mr-3"></i>
                <div class="flex-grow-1">
                    <span class="font-weight-bold">{{ $missingToday }} employees</span> have not submitted their daily reports yet for today.
                </div>
                <a href="{{ route('daily-reports.missing') }}" class="btn btn-sm btn-danger ml-3">View List</a>
            </div>
        @endif

        {{-- Main Table Card --}}
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive shadow-sm">
            <div class="p-20 border-bottom-grey d-flex justify-content-between align-items-center">
                <h4 class="mb-0 f-18 font-weight-normal">Report Submission History</h4>
                <div class="d-flex">
                    <x-forms.button-secondary id="export-report" class="btn-sm" icon="file-export">@lang('app.export')</x-forms.button-secondary>
                </div>
            </div>
            <div class="p-20">
                {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        (function() {
            var $doc = $(document);
            var namespace = '.drAdminIndex';

            $doc.off(namespace);

            function showTable() {
                if (window.LaravelDataTables && window.LaravelDataTables["daily-report-table"]) {
                    window.LaravelDataTables["daily-report-table"].draw(false);
                }
            }

            $('#daily-report-table').on('preXhr.dt' + namespace, function (e, settings, data) {
                const rangePicker = $('#datatableRange').data('daterangepicker');
                let startDate = null;
                let endDate = null;

                if (rangePicker && $('#datatableRange').val() !== '') {
                    startDate = rangePicker.startDate.format('{{ company()->moment_date_format }}');
                    endDate = rangePicker.endDate.format('{{ company()->moment_date_format }}');
                }

                data['startDate'] = startDate;
                data['endDate'] = endDate;
                data['employee'] = $('#dr-employee').val();
                data['department'] = $('#dr-department').val();
            });

            $doc.on('change' + namespace + ' changed.bs.select' + namespace, '#dr-employee, #dr-department', function () {
                $('#dr-reset-btn').removeClass('d-none');
                showTable();
            });

            $doc.on('click' + namespace, '#dr-reset-btn', function () {
                $('#filter-form').length ? $('#filter-form')[0].reset() : null;
                $('#dr-employee, #dr-department').val('all');
                $('.select-picker').selectpicker('refresh');
                $('#datatableRange').val('');
                $(this).addClass('d-none');
                showTable();
            });

            function setDate() {
                if (!document.getElementById('datatableRange')) return;
                
                const dateRangePickerConfig = {
                    autoUpdateInput: false,
                    locale: {
                        cancelLabel: 'Clear',
                        format: '{{ company()->moment_date_format }}',
                    },
                    ranges: daterangeConfig
                };

                $('#datatableRange').daterangepicker(dateRangePickerConfig);

                $('#datatableRange').off('apply.daterangepicker').on('apply.daterangepicker', function(ev, picker) {
                    $(this).val(picker.startDate.format('{{ company()->moment_date_format }}') + ' @lang("app.to") ' + picker.endDate.format('{{ company()->moment_date_format }}'));
                    $('#dr-reset-btn').removeClass('d-none');
                    showTable();
                });
            }

            var onTurboLoad = function() {
                setDate();
            };

            document.addEventListener('turbo:load', onTurboLoad);
            setDate();

            document.addEventListener('turbo:before-cache', function cleanup() {
                $doc.off(namespace);
                document.removeEventListener('turbo:load', onTurboLoad);
            }, { once: true });

            $('body').off('.drAdminModal').on('click.drAdminModal', '.openRightModal', function(e) {
                e.preventDefault();
                var url = $(this).attr('href');
                $(RIGHT_MODAL_CONTENT).html(loader);
                $(RIGHT_MODAL).modal('show');
                $.easyAjax({
                    url: url,
                    type: "GET",
                    success: function (response) {
                        if (response.html) {
                            $(RIGHT_MODAL_CONTENT).html(response.html);
                            if (response.title && $(RIGHT_MODAL_TITLE).length) {
                                $(RIGHT_MODAL_TITLE).html(response.title);
                            }
                        }
                    }
                });
            });

        })();
    </script>
@endpush
