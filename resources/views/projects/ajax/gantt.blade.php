@php
$addTaskPermission = ($project->project_admin == user()->id) ? 'all' : user()->permission('add_tasks');
$editTaskPermission = ($project->project_admin == user()->id) ? 'all' : user()->permission('edit_tasks');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/frappe/frappe-gantt.css') }}">
<script src="{{ asset('vendor/frappe/frappe-gantt.js') }}"></script>

<!-- ROW START -->
<div class="row py-3 py-lg-5 py-lg-5">
    <div class="col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4">
        <!-- Add Task Export Buttons Start -->
        <div class="d-flex" id="table-actions">
            @if (($addTaskPermission == 'all' || $addTaskPermission == 'added') && !$project->trashed())
                <x-forms.link-primary :link="route('tasks.create').'?project_id='.$project->id"
                    class="mr-3 openRightModal" icon="plus" data-redirect-url="{{ url()->full() }}">
                    @lang('app.add')
                    @lang('app.task')
                </x-forms.link-primary>
            @endif

        </div>
        <!-- Add Task Export Buttons End -->
        <!-- Task Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">

            <div class="d-flex">
                <!-- ASSIGN START -->
                <div class="select-box py-2 px-2 mr-3">
                    <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('modules.tasks.assignTo')
                    </p>
                    <div class="select-status mr-3">
                        <select class="form-control select-picker" id="assignedTo" data-live-search="true"
                            data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($project->projectMembers as $employee)
                                <x-user-option :user="$employee" />
                            @endforeach
                        </select>
                    </div>
                </div>
                <!-- ASSIGN END -->

                <!-- ASSIGN START -->
                <div class="select-box py-2 px-lg-2 px-md-2 px-0">
                    <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.view')
                    </p>
                    <div class="select-status mr-3">
                        <select class="form-control select-picker" id="gantt-view" data-size="8">
                            <option value="Day">@lang('app.day')</option>
                            <option value="Week">@lang('app.week')</option>
                            <option value="Month">@lang('app.month')</option>
                        </select>
                    </div>
                </div>
                <!-- ASSIGN END -->

                 <!-- ASSIGN START -->
                 <div class="select-box py-2 px-2 mr-3">
                    <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.task')
                    </p>
                    <div class="select-status mr-3">
                        <select class="form-control select-picker" id="projectTask" data-live-search="true"
                            data-size="8" multiple name="projectTask[]">
                            @foreach ($project->tasks as $task)
                                <option value="{{ $task->id}}">{{ mb_ucwords($task->heading) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <!-- ASSIGN END -->

                 <!-- ASSIGN START -->
                 <div class="select-box py-2 px-2 mr-3">
                    <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.task') @lang('app.status')
                    </p>
                    <div class="select-status mr-3">
                        <select class="form-control select-picker" id="task_status" data-live-search="true"
                            data-size="8" multiple name="task_status[]">
                            @foreach ($taskBoardStatus as $status)
                                <option selected value="{{ $status->id }}">{{ $status->slug == 'completed' || $status->slug == 'incomplete' ? __('app.' . $status->slug) : mb_ucwords($status->column_name) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <!-- ASSIGN END -->

                 <!-- ASSIGN START -->
                 <div class="select-box py-2 px-2 mr-3">
                    <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('modules.projects.milestones')
                    </p>
                    <div class="select-status mr-3">
                        <select class="form-control select-picker" id="milestones" data-live-search="true"
                            data-size="8" multiple name="milestones[]">
                            @foreach ($project->milestones as $milestone)
                                <option value="{{ $milestone->id }}">{{ $milestone->milestone_title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <!-- ASSIGN END -->
            </div>


            <div id="gantt"></div>

        </div>
        <!-- Task Box End -->

    </div>

</div>
<!-- ROW END -->

<script>
    function initGantt() {
        if (typeof window.jQuery !== 'undefined' && typeof $.easyAjax === 'function') {
            (function() {
                const $ganttBody = $('body');
                const namespace = '.projectGantt';
                const editTaskPermission = "{{ ($editTaskPermission == 'all' ? 'true' : 'false') }}";

                // Cleanup before re-binding
                $ganttBody.off(namespace);

                function loadData() {
                    const projectID = "{{ $project->id }}";
                    const assignedTo = $('#assignedTo').val();
                    const projectTask = $('#projectTask').val();
                    const taskStatus = $('#task_status').val();
                    const milestones = $('#milestones').val();
                    const viewMode = $('#gantt-view').val();
                    const token = "{{ csrf_token() }}";

                    const url = "{{ route('projects.gantt_data') }}";

                    $.easyAjax({
                        url: url,
                        blockUI: true,
                        container: '.content-wrapper',
                        type: "POST",
                        data: {
                            assignedTo: assignedTo,
                            projectID: projectID,
                            projectTask: projectTask,
                            taskStatus: taskStatus,
                            milestones: milestones,
                            _token: token
                        },
                        success: function(response) {
                            if (!response || !response.length) {
                                $("#gantt").html(
                                    "<div class='d-flex justify-content-center p-20'>{{ __('messages.noRecordFound') }}</div>"
                                );
                                return;
                            }

                            $("#gantt").html("");

                            if (typeof Gantt !== 'undefined') {
                                var gantt = new Gantt("#gantt", response, {
                                    popup_trigger: "mouseover",
                                    view_mode: viewMode,
                                    on_click: function(task) {
                                        taskDetail(task.taskid);
                                    },
                                    on_date_change: function(task, start, end) {
                                        var taskId = task.taskid;
                                        var token = '{{ csrf_token() }}';
                                        var url = "{{ route('tasks.gantt_task_update', ':id') }}";
                                        url = url.replace(':id', taskId);
                                        var startDate = moment.utc(start.toDateString()).format('DD/MM/Y');
                                        var endDate = moment.utc(end.toDateString()).subtract(1, "days").format('DD/MM/Y');

                                        $.easyAjax({
                                            url: url,
                                            type: "POST",
                                            container: '#gantt',
                                            data: {
                                                '_token': token,
                                                'start_date': startDate,
                                                'end_date': endDate
                                            }
                                        });
                                    },
                                    on_progress_change: function(task, progress) {},
                                    on_view_change: function(mode) {}
                                });
                            }
                        }
                    });
                }

                var taskDetail = function(id) {
                    var url = "{{ route('tasks.show', ':id') }}";
                    url = url.replace(':id', id);

                    $.easyAjax({
                        url: url,
                        blockUI: true,
                        container: RIGHT_MODAL,
                        historyPush: true,
                        success: function(response) {
                            if (response.status == "success") {
                                $(RIGHT_MODAL_CONTENT).html(response.html);
                                $(RIGHT_MODAL_TITLE).html(response.title);
                            }
                        }
                    });
                }

                $ganttBody.on('change' + namespace + ' keyup' + namespace, '#assignedTo, #gantt-view, #projectTask, #task_status, #milestones', function() {
                    loadData();
                });

                loadData();

                window.addEventListener('turbo:before-cache', function cleanup() {
                    $ganttBody.off(namespace);
                    window.removeEventListener('turbo:before-cache', cleanup);
                }, { once: true });
            })();
        } else {
            setTimeout(initGantt, 50);
        }
    }

    initGantt();
</script>
