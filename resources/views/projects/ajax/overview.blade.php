<script src="{{ asset('vendor/jquery/frappe-charts.min.iife.js') }}"></script>
<script src="{{ asset('vendor/jquery/Chart.min.js') }}"></script>
<script src="{{ asset('vendor/jquery/gauge.js') }}"></script>

@php
$editProjectPermission = user()->permission('edit_projects');
$addPaymentPermission = user()->permission('add_payments');
$projectBudgetPermission = user()->permission('view_project_budget');
$memberIds = $project->members->pluck('user_id')->toArray();
@endphp

<div class="d-lg-flex">
    <div class="w-100 py-0 py-lg-3 py-md-0 ">
        <div class="d-flex align-items-center flex-lg-row-reverse mb-4">
            @if (!$project->trashed())
                <div class="ml-lg-3 ml-md-0 ml-0 mr-3 mr-lg-0 mr-md-3">
                    @if ($editProjectPermission == 'all' || ($editProjectPermission == 'added' && $project->added_by == user()->id) || ($project->project_admin == user()->id))
                        <select class="form-control select-picker change-status height-35">
                            @foreach ($projectStatus as $status)
                                <option
                                data-content="<i class='fa fa-circle mr-1 f-15' style='color:{{$status->color}}'></i>{{ ucfirst($status->status_name) }}"
                                @if ($project->status == $status->status_name)
                                selected @endif
                                value="{{$status->status_name}}"> {{ $status->status_name }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        @foreach ($projectStatus as $status)
                            @if ($project->status == $status->status_name)
                                <div class="bg-white p-2 border rounded">
                                    <i class='fa fa-circle mr-2' style="color:{{$status->color}}"></i>{{ $status->status_name }}
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>

                <div class="ml-lg-3 ml-md-0 ml-0 mr-3 mr-lg-0 mr-md-3">
                    <div class="dropdown">
                        <button
                            class="btn btn-lg bg-white border height-35 f-15 px-2 py-1 text-dark-grey text-capitalize rounded  dropdown-toggle"
                            type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            @lang('app.action') <i class="icon-options-vertical icons"></i>
                        </button>

                        <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                            aria-labelledby="dropdownMenuLink" tabindex="0">

                            @if ($editProjectPermission == 'all'
                                || ($project->project_admin == user()->id)
                                || ($editProjectPermission == 'added' && user()->id == $project->added_by)
                                || ($editProjectPermission == 'owned' && user()->id == $project->client_id && in_array('client', user_roles()))
                                || ($editProjectPermission == 'owned' && in_array(user()->id, $memberIds) && in_array('employee', user_roles()))
                                || ($editProjectPermission == 'both' && (user()->id == $project->client_id || user()->id == $project->added_by))
                                || ($editProjectPermission == 'both' && in_array(user()->id, $memberIds) && in_array('employee', user_roles())))
                                <a class="dropdown-item openRightModal"
                                    href="{{ route('projects.edit', $project->id) }}">@lang('app.edit')
                                    @lang('app.project')
                                </a>

                                <a class="dropdown-item"
                                    href="{{ route('front.gantt', $project->hash) }}" target="_blank">
                                    @lang('modules.projects.viewPublicGanttChart')
                                </a>

                                <a class="dropdown-item"
                                    href="{{ route('front.taskboard', $project->hash) }}" target="_blank">
                                    @lang('app.public') @lang('modules.tasks.taskBoard')
                                </a>
                                <hr class="my-1">
                            @endif

                            @php $projectPin = $project->pinned() @endphp

                            @if ($projectPin)
                                <a class="dropdown-item" href="javascript:;" id="pinnedItem"
                                    data-pinned="pinned">@lang('app.unpin')
                                    @lang('app.project')</a>
                            @else
                                <a class="dropdown-item" href="javascript:;" id="pinnedItem"
                                    data-pinned="unpinned">@lang('app.pin')
                                    @lang('app.project')</a>
                            @endif
                        </div>
                    </div>
                </div>

                @if ($projectPin)
                    <div class="align-self-center">
                        <span class='badge badge-success'><i class='fa fa-thumbtack'></i> @lang('app.pinned')</span>
                    </div>
                @endif
            @elseif($editProjectPermission == 'all'
            || ($project->project_admin == user()->id)
            || ($editProjectPermission == 'added' && user()->id == $project->added_by)
            || ($editProjectPermission == 'owned' && user()->id == $project->client_id && in_array('client', user_roles()))
            || ($editProjectPermission == 'owned' && in_array(user()->id, $memberIds) && in_array('employee', user_roles()))
            || ($editProjectPermission == 'both' && (user()->id == $project->client_id || user()->id == $project->added_by))
            || ($editProjectPermission == 'both' && in_array(user()->id, $memberIds) && in_array('employee', user_roles())))
                <div class="ml-3">
                    <x-forms.button-primary class="restore-project" icon="undo">@lang('app.unarchive')
                    </x-forms.button-primary>
                </div>
            @endif

            @if(in_array('admin', user_roles()) && isset($widgets))
                <div class="ml-lg-3 ml-md-0 ml-0 mr-3 mr-lg-0 mr-md-3 d-flex align-self-center">
                    <x-form id="projectDashboardWidgetForm" method="POST">
                        <div class="dropdown keep-open">
                            <a class="d-flex align-items-center justify-content-center dropdown-toggle px-4 text-dark"
                                type="link" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                <i class="fa fa-cog" data-original-title="{{__('modules.dashboard.dashboardWidgetsSettings')}}" data-toggle="tooltip"></i>
                            </a>
                            <!-- Dropdown - User Information -->
                            <ul class="dropdown-menu dropdown-menu-right p-20" style="width: 300px;"
                                aria-labelledby="dropdownMenuLink" tabindex="0">
                                <li class="border-bottom mb-3">
                                    <h4 class="heading-h3">@lang('modules.dashboard.dashboardWidgets')</h4>
                                </li>
                                @foreach ($widgets as $widget)
                                    @php
                                        $wname = \Illuminate\Support\Str::camel($widget->widget_name);
                                    @endphp
                                    <li class="mb-2 float-left w-50" style="list-style: none;">
                                        <div class="checkbox checkbox-info ">
                                            <input id="{{ $widget->widget_name }}" name="{{ $widget->widget_name }}"
                                                value="true" @if ($widget->status) checked @endif type="checkbox">
                                            <label for="{{ $widget->widget_name }}">@lang('modules.dashboard.' . $wname)</label>
                                        </div>
                                    </li>
                                @endforeach
                                @if (count($widgets) % 2 != 0)
                                    <li class="mb-2 float-left w-50 height-35" style="list-style: none;"></li>
                                @endif
                                <li class="float-none w-100" style="list-style: none;">
                                    <x-forms.button-primary id="save-dashboard-widget" icon="check">@lang('app.save')
                                    </x-forms.button-primary>
                                </li>
                            </ul>
                        </div>
                    </x-form>
                </div>
            @endif
        </div>
        <!-- PROJECT PROGRESS AND CLIENT START -->
        <div class="row">
            <!-- PROJECT PROGRESS START -->
            <div class="col-md-6 mb-4">
                <x-cards.data :title="__('modules.projects.projectProgress')"
                    otherClasses="d-flex d-xl-flex d-lg-block d-md-flex  justify-content-between align-items-center">

                    <x-gauge-chart id="progressGauge" width="100" :value="$project->completion_percent" />

                    <!-- PROGRESS START DATE START -->
                    <div class="p-start-date mb-xl-0 mb-lg-3">
                        <h5 class="text-lightest f-14 font-weight-normal">@lang('app.startDate')</h5>
                        <p class="f-15 mb-0">{{ $project->start_date->translatedFormat(company()->date_format) }}</p>
                    </div>
                    <!-- PROGRESS START DATE END -->
                    <!-- PROGRESS END DATE START -->
                    <div class="p-end-date">
                        <h5 class="text-lightest f-14 font-weight-normal">@lang('modules.projects.deadline')</h5>
                        <p class="f-15 mb-0">
                            {{ !is_null($project->deadline) ? $project->deadline->translatedFormat(company()->date_format) : '--' }}
                        </p>
                    </div>
                    <!-- PROGRESS END DATE END -->

                </x-cards.data>
            </div>
            <!-- PROJECT PROGRESS END -->
            <!-- CLIENT START -->
            <div class="col-md-6 mb-4">
                @if (!is_null($project->client))
                    <x-cards.data :title="__('app.client')"
                        otherClasses="d-block d-xl-flex d-lg-block d-md-flex  justify-content-between align-items-center">

                        <div class="p-client-detail">
                            <div class="card border-0 ">
                                <div class="card-horizontal">

                                    <div class="card-img m-0">
                                        <img class="" src=" {{ $project->client->image_url }}"
                                            alt="{{ $project->client->name }}">
                                    </div>
                                    <div class="card-body border-0 p-0 ml-4 ml-xl-4 ml-lg-3 ml-md-3">
                                        <h4 class="card-title f-15 font-weight-normal mb-0 text-capitalize">
                                            @if (!in_array('client', user_roles()))
                                               <a href="{{ route('clients.show', $project->client_id) }}" class="text-dark">
                                                    {{ $project->client->name }}
                                                </a>
                                            @else
                                                {{ $project->client->name }}
                                            @endif
                                        </h4>
                                        <p class="card-text f-14 text-lightest mb-0">
                                            {{ $project->client->clientDetails->company_name }}
                                        </p>
                                        @if ($project->client->country_id)
                                            <span
                                                class="card-text f-12 text-lightest text-capitalize d-flex align-items-center">
                                                <span
                                                    class='flag-icon flag-icon-{{ strtolower($project->client->country->iso) }} mr-2'></span>
                                                {{ $project->client->country->nicename }}
                                            </span>
                                        @endif
                                    </div>

                                </div>
                            </div>
                        </div>

                        @if( (in_array('admin', user_roles()) && $messageSetting->allow_client_admin == 'yes') ||
                        (in_array('employee', user_roles()) && $messageSetting->allow_client_employee == 'yes') )
                        <div class="p-client-msg mt-4 mt-xl-0 mt-lg-4 mt-md-0">
                            <button type="button" class="btn-secondary rounded f-15" id="new-chat"
                                data-client-id="{{ $project->client->id }}"> <i class="fab fa-whatsapp mr-1"></i>
                                @lang('app.message')</button>
                        </div>
                @endif


                </x-cards.data>
            @else
                <x-cards.data
                    otherClasses="d-flex d-xl-flex d-lg-block d-md-flex  justify-content-between align-items-center">
                    <x-cards.no-record icon="user" :message="__('messages.noClientAddedToProject')" />
                </x-cards.data>
                @endif

            </div>
            <!-- CLIENT END -->
        </div>
        <!-- PROJECT PROGRESS AND CLIENT END -->

        <!-- TASK STATUS AND BUDGET START -->
        <div class="row mb-4">
            <!-- TASK STATUS START -->
            <div class="col-lg-6 col-md-12">
                <x-cards.data :title="__('app.menu.tasks')" padding="false">
                    <x-pie-chart id="task-chart" :labels="$taskChart['labels']" :values="$taskChart['values']"
                        :colors="$taskChart['colors']" height="220" width="250" />
                </x-cards.data>
            </div>
            <!-- TASK STATUS END -->
            <!-- PLACEHOLDER 1 START -->
            <div class="col-lg-6 col-md-12" id="custom-widget-placeholder-1">
                @if(isset($activeWidgets) && in_array('project_statistics', $activeWidgets))
                    @include('projects.widgets.project_statistics')
                @endif
            </div>
            <!-- PLACEHOLDER 1 END -->
        </div>
        <!-- TASK STATUS AND BUDGET END -->

        <!-- TASK STATUS AND BUDGET START -->
        <div class="row mb-4">
            <!-- PLACEHOLDER 2 START -->
            <div class="col-md-12" id="custom-widget-placeholder-2">
                @if(isset($activeWidgets) && in_array('project_hours_chart', $activeWidgets))
                    @include('projects.widgets.project_hours_chart')
                @endif
            </div>
            <!-- PLACEHOLDER 2 END -->
        </div>
        <!-- TASK STATUS AND BUDGET END -->

        <!-- PROJECT DETAILS START -->
        <div class="row">
            <div class="col-md-12 mb-4">
                <x-cards.data :title="__('app.project') . ' ' . __('app.details')"
                    otherClasses="d-flex justify-content-between align-items-center">
                    @if (is_null($project->project_summary))
                        <x-cards.no-record icon="align-left" :message="__('messages.projectDetailsNotAdded')" />
                    @else
                        <div class="text-dark-grey mb-0 ql-editor p-0">{!! $project->project_summary !!}</div>
                    @endif
                </x-cards.data>
            </div>
        </div>
        <!-- PROJECT DETAILS END -->

        {{-- Custom fields data --}}
        @if (isset($fields) && count($fields) > 0)
            <div class="row mt-4">
                <!-- TASK STATUS START -->
                <div class="col-md-12">
                    <x-cards.data :title="__('modules.client.clientOtherDetails')">
                        <x-forms.custom-field-show :fields="$fields" :model="$project"></x-forms.custom-field-show>
                    </x-cards.data>
                </div>
            </div>
        @endif

    </div>
</div>

<script>
    $(document).ready(function() {
        $('#save-dashboard-widget').click(function() {
            $.easyAjax({
                url: "{{ route('dashboard.widget', 'project-overview-dashboard') }}",
                container: '#projectDashboardWidgetForm',
                blockUI: true,
                type: "POST",
                redirect: true,
                data: $('#projectDashboardWidgetForm').serialize(),
                success: function() {
                    window.location.reload();
                }
            })
        });

        $('.keep-open .dropdown-menu').on({
            "click": function(e) {
                e.stopPropagation();
            }
        });
        $('.change-status').change(function() {
            var status = $(this).val();
            var url = "{{ route('projects.update_status', $project->id) }}";
            var token = '{{ csrf_token() }}'

            $.easyAjax({
                url: url,
                type: "POST",
                container: '.content-wrapper',
                blockUI: true,
                data: {
                    status: status,
                    _token: token
                }
            });
        });

        $('body').on('click', '#pinnedItem', function() {
            var type = $('#pinnedItem').attr('data-pinned');
            var id = '{{ $project->id }}';
            var pinType = 'project';

            var dataPin = type.trim(type);
            if (dataPin == 'pinned') {
                Swal.fire({
                    title: "@lang('messages.sweetAlertTitle')",
                    icon: 'warning',
                    showCancelButton: true,
                    focusConfirm: false,
                    confirmButtonText: "@lang('messages.confirmUnpin')",
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
                        var url = "{{ route('projects.destroy_pin', ':id') }}";
                        url = url.replace(':id', id);

                        var token = "{{ csrf_token() }}";
                        $.easyAjax({
                            type: 'POST',
                            url: url,
                            data: {
                                '_token': token,
                                'type': pinType
                            },
                            success: function(response) {
                                if (response.status == "success") {
                                    window.location.reload();
                                }
                            }
                        })
                    }
                });

            } else {
                Swal.fire({
                    title: "@lang('messages.sweetAlertTitle')",
                    icon: 'warning',
                    showCancelButton: true,
                    focusConfirm: false,
                    confirmButtonText: "@lang('messages.confirmPin')",
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
                        var url = "{{ route('projects.store_pin') }}?type=" + pinType;

                        var token = "{{ csrf_token() }}";
                        $.easyAjax({
                            type: 'POST',
                            url: url,
                            data: {
                                '_token': token,
                                'project_id': id
                            },
                            success: function(response) {
                                if (response.status == "success") {
                                    window.location.reload();
                                }
                            }
                        });
                    }
                });
            }
        });

        $('body').on('click', '.restore-project', function() {
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.unArchiveMessage')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmRevert')",
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
                    var url = "{{ route('projects.archive_restore', $project->id) }}";

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {
                            '_token': token
                        },
                        success: function(response) {
                            if (response.status == "success") {
                                window.location.reload();
                            }
                        }
                    });
                }
            });
        });

        $('body').on('click', '#new-chat', function() {
            let clientId = $(this).data('client-id');
            const url = "{{ route('messages.create') }}?clientId=" + clientId;
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

    });
</script>
