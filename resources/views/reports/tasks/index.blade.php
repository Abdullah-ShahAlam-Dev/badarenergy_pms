@extends('layouts.app')

@push('datatable-styles')
    <script src="{{ asset('vendor/jquery/frappe-charts.min.iife.js') }}"></script>
    <script src="{{ asset('vendor/jquery/Chart.min.js') }}"></script>
    @include('sections.datatable_css')
@endpush

@section('filter-section')

    <x-filters.filter-box>
        <!-- DATE START -->
        <div class="select-box d-flex pr-2 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.duration')</p>
            <div class="select-status d-flex">
                <input type="text" class="position-relative text-dark form-control border-0 p-2 text-left f-14 f-w-500 border-additional-grey"
                    id="datatableRange" placeholder="@lang('placeholders.dateRange')">
            </div>
        </div>
        <!-- DATE END -->

        <!-- STATUS START -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.status')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="status" id="status">
                    <option value="all">@lang('app.all')</option>
                    <option value="not finished">@lang('modules.tasks.hideCompletedTask')</option>
                    @foreach ($taskBoardStatus as $status)
                        <option value="{{ $status->id }}">{{ $status->slug == 'completed' || $status->slug == 'incomplete' ? __('app.' . $status->slug) : mb_ucwords($status->column_name) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <!-- STATUS END -->

        <!-- PROJECT START -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.project')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="project_id" id="project_id" data-live-search="true"
                    data-size="8">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}">{{ mb_ucwords($project->project_name) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <!-- PROJECT END -->

        <!-- RESET START -->
        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
        <!-- RESET END -->

        <!-- MORE FILTERS START -->
        <x-filters.more-filter-box>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('app.client')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" id="clientID" data-live-search="true" data-container="body" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($clients as $client)
                                <x-user-option :user="$client" />
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('modules.tasks.assignTo')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" id="assignedTo" data-live-search="true" data-container="body" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($employees as $employee)
                                <x-user-option :user="$employee" />
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('modules.tasks.assignBy')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" id="assignedBY" data-live-search="true" data-container="body" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($employees as $employee)
                                <x-user-option :user="$employee" />
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('app.label')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" id="label" data-live-search="true" data-container="body" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($taskLabels as $label)
                                <option
                                    data-content="<span class='badge b-all' style='background:{{ $label->label_color }};'>{{ $label->label_name }}</span> "
                                    value="{{ $label->id }}">{{ $label->label_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize"
                    for="usr">@lang('modules.taskCategory.taskCategory')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" id="category_id" data-live-search="true" data-container="body" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($taskCategories as $categ)
                                <option value="{{ $categ->id }}">{{ mb_ucwords($categ->category_name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('app.billableTask')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" id="billable_task" data-live-search="true" data-container="body" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            <option value="1">@lang('app.yes')</option>
                            <option value="0">@lang('app.no')</option>
                        </select>
                    </div>
                </div>
            </div>

        </x-filters.more-filter-box>
        <!-- MORE FILTERS END -->
    </x-filters.filter-box>

@endsection

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <!-- Add Task Export Buttons Start -->
        <div class="d-flex flex-column">
            <!-- TASK STATUS START -->
            <x-cards.data id="task-chart-card" :title="__('app.menu.tasks')" padding="false">
            </x-cards.data>
            <!-- TASK STATUS END -->

            <div id="table-actions" class="flex-grow-1 align-items-center mt-4">
            </div>

        </div>

        <!-- Add Task Export Buttons End -->
        <!-- Task Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-4 bg-white">

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}


        </div>
        <!-- Task Box End -->
    </div>
    <!-- CONTENT WRAPPER END -->

@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        (function () {

            // ─────────────────────────────────────────────────────────
            // pieChart — reads current filter values and loads chart
            // Defined outside init so it can be called from showTable
            // ─────────────────────────────────────────────────────────
            function pieChart() {
                if (!document.getElementById('allTasks-table')) return;

                var dateRangePicker = $('#datatableRange').data('daterangepicker');
                var startDateVal = $('#datatableRange').val();
                var startDate = null, endDate = null;

                if (startDateVal !== '' && dateRangePicker) {
                    startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                    endDate   = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
                }

                $.easyAjax({
                    url: "{{ route('task-report.chart') }}",
                    container: '#task-chart-card',
                    blockUI: true,
                    type: 'POST',
                    data: {
                        clientID:    $('#clientID').val()    || 'all',
                        assignedBY:  $('#assignedBY').val()  || 'all',
                        assignedTo:  $('#assignedTo').val()  || 'all',
                        status:      $('#status').val()      || 'all',
                        label:       $('#label').val()       || 'all',
                        category_id: $('#category_id').val() || 'all',
                        billable:    $('#billable_task').val() || 'all',
                        projectId:   $('#project_id').val()  || 0,
                        startDate:   startDate,
                        endDate:     endDate,
                        searchText:  $('#search-text-field').val() || '',
                        _token:      '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        $('#task-chart-card').html(response.html);
                    }
                });
            }

            // ─────────────────────────────────────────────────────────
            // initTasksReport — re-runs on every turbo:load navigation
            // ─────────────────────────────────────────────────────────
            function initTasksReport() {
                if (!document.getElementById('allTasks-table')) return;

                // showTable: safe null-guarded redraw
                window.showTable = function () {
                    if (window.LaravelDataTables && window.LaravelDataTables['allTasks-table']) {
                        window.LaravelDataTables['allTasks-table'].draw(false);
                        pieChart();
                    }
                };

                // preXhr — re-attach to the fresh DOM element each navigation
                $('#allTasks-table').off('preXhr.dt.tasksRep').on('preXhr.dt.tasksRep', function (e, settings, data) {
                    var dateRangePicker = $('#datatableRange').data('daterangepicker');
                    var startDateVal    = $('#datatableRange').val();
                    var startDate = null, endDate = null;

                    if (startDateVal !== '' && dateRangePicker) {
                        startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                        endDate   = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
                    }

                    data['clientID']    = $('#clientID').val()    || 'all';
                    data['assignedBY']  = $('#assignedBY').val()  || 'all';
                    data['assignedTo']  = $('#assignedTo').val()  || 'all';
                    data['status']      = $('#status').val()      || 'all';
                    data['label']       = $('#label').val()       || 'all';
                    data['category_id'] = $('#category_id').val() || 'all';
                    data['billable']    = $('#billable_task').val() || 'all';
                    data['projectId']   = $('#project_id').val()  || 0;
                    data['startDate']   = startDate;
                    data['endDate']     = endDate;
                    data['searchText']  = $('#search-text-field').val() || '';
                });

                // Run chart immediately on page load
                pieChart();
            }

            // ─────────────────────────────────────────────────────────
            // Delegated listeners — survive all Turbo navigations.
            // Namespaced to prevent accumulation. Page-guarded.
            // ─────────────────────────────────────────────────────────
            var filterSel = '#billable_task, #status, #clientID, #category_id, #assignedBY, #assignedTo, #label, #project_id';

            $(document)
                .off('change.tasksRepF changed.bs.select.tasksRepF')
                .on('change.tasksRepF changed.bs.select.tasksRepF', filterSel, function () {
                    if (!document.getElementById('allTasks-table')) return;
                    var anyActive = $(filterSel).toArray().some(function (el) {
                        return $(el).val() && $(el).val() !== 'all';
                    });
                    $('#reset-filters').toggleClass('d-none', !anyActive);
                    if (typeof window.showTable === 'function') window.showTable();
                });

            $(document).off('keyup.tasksRepSearch').on('keyup.tasksRepSearch', '#search-text-field', function () {
                if (!document.getElementById('allTasks-table')) return;
                if ($(this).val() !== '') $('#reset-filters').removeClass('d-none');
                if (typeof window.showTable === 'function') window.showTable();
            });

            $(document).off('click.tasksRepReset').on('click.tasksRepReset', '#reset-filters, #reset-filters-2', function () {
                if (!document.getElementById('allTasks-table')) return;
                var $form = $('#filter-form');
                if ($form.length) $form[0].reset();
                $('.filter-box .select-picker').selectpicker('refresh');
                $('#reset-filters').addClass('d-none');
                if (typeof window.showTable === 'function') window.showTable();
            });

            // ─────────────────────────────────────────────────────────
            // Turbo:load hook + immediate call for full-page loads
            // ─────────────────────────────────────────────────────────
            document.addEventListener('turbo:load', function () {
                initTasksReport();
            });

            initTasksReport();

        })();
    </script>
@endpush
