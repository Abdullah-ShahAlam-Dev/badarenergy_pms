@php
$addTaskPermission = ($project->project_admin == user()->id) ? 'all' : user()->permission('add_tasks');
@endphp

<link rel='stylesheet' href="{{ asset('vendor/css/dragula.css') }}" type='text/css' />
<link rel='stylesheet' href="{{ asset('vendor/css/drag.css') }}" type='text/css' />
<link rel="stylesheet" href="{{ asset('vendor/css/bootstrap-colorpicker.css') }}" />
<style>
    #colorpicker .form-group {
        width: 87%;
    }

    .b-p-tasks {
        min-height: 90%;
    }

    .content-wrapper {
        padding: 0;
    }

</style>

<!-- CONTENT WRAPPER START -->
<div class="w-task-board-box px-4 py-2 pt-3 bg-white">
    <!-- Add Task Export Buttons Start -->
    <div class="d-block d-lg-flex d-md-flex my-3">

        <x-alert type="warning" icon="info" class="d-lg-none">@lang('messages.dragDropScreenInfo')</x-alert>

        <div id="table-actions" class="flex-grow-1 align-items-center">
            @if (($addTaskPermission == 'all' || $addTaskPermission == 'added') && !$project->trashed())
                <x-forms.link-primary :link="route('tasks.create').'?task_project_id='.$project->id"
                    class="mr-3 openRightModal float-left" icon="plus" data-redirect-url="{{ url()->full() }}">
                    @lang('app.add')
                    @lang('app.task')
                </x-forms.link-primary>
            @endif
            @if (user()->permission('change_status') == 'all' && !$project->trashed())
                <x-forms.button-secondary icon="plus" id="add-column">
                    @lang('modules.tasks.addBoardColumn')
                </x-forms.button-secondary>
            @endif
        </div>
    </div>

    <div class="w-task-board-panel d-flex" id="taskboard-columns">
    </div>
</div>
<!-- CONTENT WRAPPER END -->

<script src="{{ asset('vendor/jquery/dragula.js') }}"></script>

<script>
    function initTaskboard() {
        if (typeof window.jQuery !== 'undefined' && typeof $.easyAjax === 'function') {
            (function() {
                const $taskboardBody = $('body');
                const namespace = '.projectTaskboard';
                let channel = null;

                function loadData() {
                    const projectID = "{{ $project->id }}";
                    let startDate = null;
                    let endDate = null;
                    const projectAdmin = "{{ ($project->project_admin == user()->id) ? 1 : 0 }}";

                    const url = "{{ route('taskboards.index') }}?startDate=" + encodeURIComponent(startDate) +
                        '&endDate=' + encodeURIComponent(endDate) + '&projectID=' + projectID + '&project_admin=' + projectAdmin;

                    console.log("Taskboard: Loading data from", url);
                    $.easyAjax({
                        url: url,
                        container: '#taskboard-columns',
                        type: "GET",
                        success: function(response) {
                            console.log("Taskboard: Data received", response.status);
                            if (response.status == 'success') {
                                $('#taskboard-columns').html(response.view);
                                if (typeof $taskboardBody.tooltip === 'function') {
                                    $taskboardBody.tooltip({ selector: '[data-toggle="tooltip"]' });
                                }
                            }
                        },
                        error: function(err) {
                            console.error("Taskboard: AJAX error", err);
                        }
                    });
                }

                $taskboardBody.off(namespace);

                $taskboardBody.on('click' + namespace, '.load-more-tasks', function() {
                    var columnId = $(this).data('column-id');
                    var totalTasks = $(this).data('total-tasks');
                    var currentTotalTasks = $('#drag-container-' + columnId + ' .task-card').length;
                    var projectAdmin = "{{ ($project->project_admin == user()->id) ? 1 : 0 }}";
                    var projectID = "{{ $project->id }}";

                    var url = "{{ route('taskboards.load_more') }}?projectID=" + projectID + '&columnId=' + columnId +
                        '&currentTotalTasks=' + currentTotalTasks + '&totalTasks=' + totalTasks + '&project_admin=' + projectAdmin;

                    $.easyAjax({
                        url: url,
                        container: '#drag-container-' + columnId,
                        blockUI: true,
                        type: "GET",
                        success: function(response) {
                            $('#drag-container-' + columnId).append(response.view);
                            if (response.load_more != 'show') {
                                $('#drag-container-' + columnId).closest('.b-p-body').find('.load-more-tasks').remove();
                            }
                            if (typeof $taskboardBody.tooltip === 'function') {
                                $taskboardBody.tooltip({ selector: '[data-toggle="tooltip"]' });
                            }
                        }
                    });
                });

                $taskboardBody.on('click' + namespace, '#add-column', function() {
                    const url = "{{ route('taskboards.create') }}";
                    $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
                    $.ajaxModal(MODAL_LG, url);
                });

                $taskboardBody.on('click' + namespace, '.edit-column', function() {
                    var id = $(this).data('column-id');
                    var url = "{{ route('taskboards.edit', ':id') }}".replace(':id', id);
                    $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
                    $.ajaxModal(MODAL_LG, url);
                });

                $taskboardBody.on('click' + namespace, '.delete-column', function() {
                    var id = $(this).data('column-id');
                    var url = "{{ route('taskboards.destroy', ':id') }}".replace(':id', id);

                    Swal.fire({
                        title: "@lang('messages.sweetAlertTitle')",
                        text: "@lang('messages.recoverRecord')",
                        icon: 'warning',
                        showCancelButton: true,
                        focusConfirm: false,
                        confirmButtonText: "@lang('messages.confirmDelete')",
                        cancelButtonText: "@lang('app.cancel')",
                        customClass: { confirmButton: 'btn btn-primary mr-3', cancelButton: 'btn btn-secondary' },
                        showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.easyAjax({
                                url: url,
                                type: 'POST',
                                data: { '_token': '{{ csrf_token() }}', '_method': 'DELETE' },
                                success: function(response) {
                                    if (response.status == 'success') { window.location.reload(); }
                                }
                            });
                        }
                    });
                });

                $taskboardBody.on('click' + namespace, '.collapse-column', function() {
                    var boardColumnId = $(this).data('column-id');
                    var type = $(this).data('type');

                    $.easyAjax({
                        url: "{{ route('taskboards.collapse_column') }}",
                        type: 'POST',
                        container: '#taskboard-columns',
                        blockUI: true,
                        data: { boardColumnId: boardColumnId, type: type, '_token': '{{ csrf_token() }}' },
                        success: function(response) {
                            if (response.status == 'success') { loadData(); }
                        }
                    });
                });

                if (typeof pusher_setting !== 'undefined' && ((pusher_setting.status === 1 && pusher_setting.taskboard === 1) || (pusher_setting.status == "1" && pusher_setting.taskboard == "1"))) {
                    channel = pusher.subscribe('task-updated-channel');
                    channel.bind('task-updated', function() { loadData(); });
                }

                loadData();

                document.addEventListener('turbo:before-cache', function cleanup() {
                    $taskboardBody.off(namespace);
                    if (channel && typeof pusher !== 'undefined') {
                        channel.unbind('task-updated');
                        pusher.unsubscribe('task-updated-channel');
                    }
                    $('.tooltip').remove();
                    document.removeEventListener('turbo:before-cache', cleanup);
                }, { once: true });
            })();
        } else {
            setTimeout(initTaskboard, 50);
        }
    }

    initTaskboard();
</script>
