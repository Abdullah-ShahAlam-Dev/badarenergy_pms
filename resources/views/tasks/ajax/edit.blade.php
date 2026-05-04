@php
    $addTaskCategoryPermission = user()->permission('add_task_category');
    $viewTaskCategoryPermission = user()->permission('view_task_category');
    $addEmployeePermission = user()->permission('add_employees');
    $addTaskFilePermission = user()->permission('add_task_files');
    $addTaskPermission = user()->permission('add_tasks');
    $viewMilestonePermission = user()->permission('view_project_milestones');
@endphp

<meta name="turbo-cache-control" content="no-cache">
<script>
    // Force Turbo to clear cache for the current URL to prevent stale data on re-edit
    if (window.Turbo) {
        Turbo.cache.clear();
    }
</script>

<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-task-data-form" method="PUT">
            <div class="add-client bg-white rounded">
                @if(request()->ajax())
                    <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                        @lang('modules.tasks.taskInfo')
                    </h4>
                @endif
                <div class="row p-20">

                    <div class="col-lg-6 col-md-6">
                        <x-forms.text :fieldLabel="__('app.title')" fieldName="heading" fieldRequired="true"
                            fieldId="heading" :fieldPlaceholder="__('placeholders.task')"
                            :fieldValue="$task->heading" />
                    </div>

                    <div class="col-md-6 col-lg-6">
                        <x-forms.label class="my-3" fieldId="category_id"
                            :fieldLabel="__('modules.tasks.taskCategory')">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="category_id" id="task_category_id"
                                data-live-search="true" data-size="8">
                                <option value="">--</option>
                                @if ($viewTaskCategoryPermission == 'all' || $viewTaskCategoryPermission == 'added')
                                    @foreach ($categories as $category)
                                        <option @if ($task->task_category_id == $category->id) selected @endif value="{{ $category->id }}">
                                            {{ mb_ucwords($category->category_name) }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>

                            @if ($addTaskCategoryPermission == 'all' || $addTaskCategoryPermission == 'added')
                                <x-slot name="append">
                                    <button id="create_task_category" type="button"
                                        class="btn btn-outline-secondary border-grey"
                                        data-toggle="tooltip" data-original-title="{{ __('modules.taskCategory.addTaskCategory') }}">@lang('app.add')</button>
                                </x-slot>
                            @endif
                        </x-forms.input-group>
                    </div>

                    <div class="col-md-12 col-lg-6">
                        <x-forms.label class="my-3" fieldId="project-id" :fieldLabel="__('app.project')">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="project_id" id="project-id"
                                data-live-search="true" data-size="8">
                                <option value="">--</option>
                                @foreach ($projects as $project)
                                    <option @if ($project->id == $task->project_id) selected @endif value="{{ $project->id }}">
                                        {{ mb_ucwords($project->project_name) }}
                                    </option>
                                @endforeach
                            </select>
                        </x-forms.input-group>
                    </div>

                    <div class="col-md-4 col-lg-6 pt-5" id='clientDetails' >
                    </div>

                    {{-- Custom Task Fields Row --}}
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-12">
                                <x-forms.text :fieldLabel="__('Doc SNo#')" fieldName="doc_sno" fieldId="doc_sno" :fieldValue="$task->doc_sno" />
                            </div>

                            <div class="col-md-12">
                                <div class="form-group my-3">
                                    <x-forms.label fieldId="description" :fieldLabel="__('app.description')">
                                    </x-forms.label>
                                    <div id="description">{!! $task->description !!}</div>
                                    <textarea name="description" id="description-text" class="d-none"></textarea>
                                </div>
                            </div>

                            <div class="col-md-2 col-sm-4">
                                <x-forms.text :fieldLabel="__('Annex A')" fieldName="annex_a" fieldId="annex_a" :fieldValue="$task->annex_a" />
                            </div>
                            <div class="col-md-2 col-sm-4">
                                <x-forms.text :fieldLabel="__('Mode')" fieldName="mode" fieldId="mode" :fieldValue="$task->mode" />
                            </div>
                            <div class="col-md-2 col-sm-4">
                                <x-forms.number :fieldLabel="__('Days')" fieldName="days" fieldId="days" :fieldValue="$task->days" />
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <x-forms.text :fieldLabel="__('UOM')" fieldName="uom" fieldId="uom" :fieldValue="$task->uom" />
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <x-forms.number :fieldLabel="__('Qty')" fieldName="qty" fieldId="qty" :fieldValue="$task->qty" />
                            </div>
                        </div>
                    </div>
                    {{-- End Custom Task Fields Row --}}

                    <div class="col-md-5 col-lg-4">
                        <x-forms.datepicker fieldId="task_start_date" fieldRequired="true"
                            :fieldLabel="__('modules.projects.startDate')" fieldName="start_date"
                            :fieldValue="(($task->start_date) ? $task->start_date->format(company()->date_format) : '')"
                            :fieldPlaceholder="__('placeholders.date')" />
                    </div>

                    <div class="col-md-5 col-lg-4 dueDateBox" @if(is_null($task->due_date)) style="display: none" @endif>
                        <x-forms.datepicker fieldId="due_date" fieldRequired="true" :fieldLabel="__('app.dueDate')"
                                            fieldName="due_date" :fieldPlaceholder="__('placeholders.date')"
                                            :fieldValue="(($task->due_date) ? $task->due_date->format(company()->date_format) : '')"  />
                    </div>

                    <div class="col-md-2 col-lg-2 pt-5">
                        <x-forms.checkbox class="mr-0 mr-lg-2 mr-md-2" :checked="is_null($task->due_date)" :fieldLabel="__('app.withoutDueDate')"
                                          fieldName="without_duedate" fieldId="without_duedate" fieldValue="yes" />
                    </div>

                    <div class="col-md-12 col-lg-12">
                    </div>

                    <div class="col-md-12 col-lg-6">
                        <div class="form-group my-3">
                            <x-forms.label fieldId="selectAssignee" :fieldLabel="__('modules.tasks.assignTo')">
                            </x-forms.label>
                            <x-forms.input-group>
                                <select class="form-control multiple-users" multiple name="user_id[]"
                                    id="selectAssignee" data-live-search="true" data-size="8">
                                    @foreach ($employees as $employee)
                                        @php
                                            $selected = '';
                                        @endphp

                                        @foreach ($task->users as $item)
                                            @if ($item->id == $employee->id)
                                                @php
                                                    $selected = 'selected';
                                                @endphp
                                            @endif
                                        @endforeach
                                        <x-user-option :user="$employee" :pill=true :selected="$selected"/>
                                    @endforeach
                                </select>

                                @if ($addEmployeePermission == 'all' || $addEmployeePermission == 'added')
                                    <x-slot name="append">
                                        <button id="add-employee" type="button"
                                            class="btn btn-outline-secondary border-grey"
                                            data-toggle="tooltip" data-original-title="{{ __('modules.employees.addNewEmployee') }}">@lang('app.add')</button>
                                    </x-slot>
                                @endif
                            </x-forms.input-group>
                        </div>
                    </div>

                    <div class="col-md-12 show-leave">
                        @if(isset($leaveData))
                            <label id="leave-date"> {{ __("modules.tasks.leaveMessage") }} <i class="fa fa-question-circle" title="{{$leaveData}}" id="leave-tooltip"></i></label>
                        @endif
                    </div>

                    <div class="col-md-12">


                </div>

                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-top-grey">
                    @lang('modules.client.clientOtherDetails')
                </h4>

                <div class="row p-20">
                    <div class="col-sm-12">
                        <div class="row">
                            <div class="col-md-12 col-lg-4">
                                <div class="form-group my-3">
                                    <x-forms.label fieldId="task_labels" :fieldLabel="__('app.label')">
                                    </x-forms.label>
                                    <x-forms.input-group>
                                        <select class="select-picker form-control" multiple name="task_labels[]"
                                            id="task_labels" data-live-search="true" data-size="8">
                                            @foreach ($taskLabels as $label)
                                                @php
                                                    $selected = '';
                                                @endphp

                                                @foreach ($task->label as $item)
                                                    @if ($item->label_id == $label->id)
                                                        @php
                                                            $selected = 'selected';
                                                        @endphp
                                                    @endif
                                                @endforeach
                                                <option {{ $selected }}
                                                    data-content="<span class='badge badge-secondary' style='background-color: {{ $label->label_color }}'>{{ $label->label_name }}</span>"
                                                    value="{{ $label->id }}">{{ $label->label_name }}</option>
                                            @endforeach
                                        </select>


                                        @if (user()->permission('task_labels') == 'all')
                                            <x-slot name="append">
                                                <button id="createTaskLabel" type="button"
                                                    class="btn btn-outline-secondary border-grey"
                                                    data-toggle="tooltip" data-original-title="{{ __('modules.taskLabel.addLabel') }}">@lang('app.add')</button>
                                            </x-slot>
                                        @endif
                                    </x-forms.input-group>
                                </div>
                            </div>

                            <div class="col-md-12 col-lg-4">
                                <div class="form-group my-3">
                                    <x-forms.label fieldId="cc_users" :fieldLabel="__('CC Users')">
                                    </x-forms.label>
                                    <x-forms.input-group>
                                        <select class="form-control multiple-users" multiple name="cc_user_id[]"
                                                id="cc_users" data-live-search="true" data-size="8">
                                            @foreach ($employees as $employee)
                                                @php
                                                    $selected = '';
                                                @endphp

                                                @foreach ($task->ccUsers as $item)
                                                    @if ($item->id == $employee->id)
                                                        @php
                                                            $selected = 'selected';
                                                        @endphp
                                                    @endif
                                                @endforeach
                                                <x-user-option :user="$employee" :pill="true" :selected="$selected"/>
                                            @endforeach
                                        </select>
                                    </x-forms.input-group>
                                </div>
                            </div>

                            <div class="col-md-12 col-lg-4">
                                <x-forms.select fieldName="milestone_id" fieldId="milestone-id"
                                    :fieldLabel="__('modules.projects.milestones')">
                                    <option value="">--</option>
                                    @if ($task->project && count($task->project->milestones) > 0)
                                        @foreach ($task->project->milestones as $milestone)
                                            <option @if ($milestone->id == $task->milestone_id) selected @endif value="{{ $milestone->id }}">
                                                {{ $milestone->milestone_title }}</option>
                                        @endforeach
                                    @endif
                                </x-forms.select>
                            </div>


                            @if ($changeStatusPermission == 'all'
                            || ($changeStatusPermission == 'added' && $task->added_by == user()->id)
                            || ($changeStatusPermission == 'owned' && in_array(user()->id, $taskUsers))
                            || ($changeStatusPermission == 'both' && (in_array(user()->id, $taskUsers) || $task->added_by == user()->id))
                            )
                                <div class="col-lg-3 col-md-6">
                                    <x-forms.select fieldId="board_column_id" :fieldLabel="__('app.status')"
                                        fieldName="board_column_id" search="true">
                                        @foreach ($taskboardColumns as $item)
                                        @php
                                                if ($item->slug == 'completed' || $item->slug == 'incomplete') {
                                                    if ($item->slug == 'completed') {
                                                        $icon = "<i class='fa fa-circle mr-2 text-dark-green'></i>".__('app.' . $item->slug);
                                                    }
                                                    elseif($item->slug == 'incomplete'){
                                                        $icon = "<i class='fa fa-circle mr-2 text-red'></i>".__('app.' . $item->slug);
                                                    }
                                                }
                                                else {
                                                    if ($item->slug == 'to_do') {
                                                        $icon = "<i class='fa fa-circle mr-2 text-yellow'></i>".$item->column_name;
                                                    }
                                                    elseif($item->slug == 'doing'){
                                                        $icon = "<i class='fa fa-circle mr-2 text-blue'></i>".$item->column_name;
                                                    }
                                                    else {
                                                        $icon = "<i class='fa fa-circle mr-2 text-black'></i>". $item->column_name;
                                                    }
                                                }
                                            @endphp
                                            <option @if ($task->board_column_id == $item->id) selected @endif value="{{ $item->id }}" data-content = "{{$icon}}">
                                            </option>
                                        @endforeach
                                    </x-forms.select>
                                </div>
                            @endif

                            <div class="col-lg-3 col-md-6">
                                <x-forms.select fieldId="priority" :fieldLabel="__('modules.tasks.priority')"
                                    fieldName="priority">
                                    <option @if ($task->priority == 'high') selected @endif
                                        data-content="<i class='fa fa-circle mr-2' style='color: #dd0000'></i> @lang('modules.tasks.high')"
                                        value="high">@lang('modules.tasks.high')</option>
                                    <option @if ($task->priority == 'medium') selected @endif
                                        data-content="<i class='fa fa-circle mr-2' style='color: #ffc202'></i> @lang('modules.tasks.medium')"
                                        value="medium">
                                        @lang('modules.tasks.medium')</option>
                                    <option @if ($task->priority == 'low') selected @endif
                                        data-content="<i class='fa fa-circle mr-2' style='color: #0a8a1f'></i> @lang('modules.tasks.low')"
                                        value="low">@lang('modules.tasks.low')</option>
                                </x-forms.select>
                            </div>
                        </div>
                    </div>


                    <div class="col-md-6 col-lg-3">
                        <div class="form-group">
                            <div class="d-flex mt-5">
                                <x-forms.checkbox :fieldLabel="__('modules.tasks.makePrivate')" fieldName="is_private"
                                    fieldId="is_private" :popover="__('modules.tasks.privateInfo')"
                                    :checked="$task->is_private" />
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="form-group">
                            <div class="d-flex mt-5">
                                <x-forms.checkbox :fieldLabel="__('modules.tasks.billable')" fieldName="billable"
                                    fieldId="billable" :popover="__('modules.tasks.billableInfo')"
                                    :checked="$task->billable" />
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="form-group">
                            <div class="d-flex mt-5">
                                <x-forms.checkbox :fieldLabel="__('modules.tasks.setTimeEstimate')"
                                    fieldName="set_time_estimate" fieldId="set_time_estimate"
                                    :checked="($task->estimate_hours > 0 || $task->estimate_minutes > 0)" />
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3 {{ $task->estimate_hours == 0 && $task->estimate_minutes == 0 ? 'd-none' : '' }}"
                        id="set-time-estimate-fields">
                        <div class="form-group mt-5">
                            <input type="number" min="0" class="w-25 border rounded p-2 height-35 f-14"
                                name="estimate_hours" value="{{ $task->estimate_hours }}">
                            @lang('app.hrs')
                            &nbsp;&nbsp;
                            <input type="number" min="0" name="estimate_minutes"
                                value="{{ $task->estimate_minutes }}" class="w-25 height-35 f-14 border rounded p-2">
                            @lang('app.mins')
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group my-3">
                            <div class="d-flex">
                                <x-forms.checkbox :fieldLabel="__('modules.events.repeat')" fieldName="repeat" :checked="$task->repeat"
                                    fieldId="repeat-task" />
                            </div>
                        </div>

                        <div class="form-group my-3 {{$task->repeat ? '' : 'd-none'}}" id="repeat-fields">
                            <div class="row">
                                <div class="col-md-6 mt-3">
                                    <x-forms.label fieldId="repeatEvery" fieldRequired="true"
                                        :fieldLabel="__('modules.events.repeatEvery')">
                                    </x-forms.label>
                                    <x-forms.input-group>
                                        <input type="number" min="1" value="{{$task->repeat_count}}" name="repeat_count"
                                            class="form-control f-14">

                                        <x-slot name="append">
                                            <select name="repeat_type" class="select-picker form-control">
                                                <option @if ($task->repeat_type == 'day') selected @endif value="day">
                                                    @lang('app.day')</option>
                                                <option @if ($task->repeat_type == 'week') selected @endif value="week">
                                                    @lang('app.week')</option>
                                                <option @if ($task->repeat_type == 'month') selected @endif value="month">
                                                    @lang('app.month')</option>
                                                <option @if ($task->repeat_type == 'year') selected @endif value="year">
                                                    @lang('app.year')</option>
                                            </select>
                                        </x-slot>
                                    </x-forms.input-group>
                                </div>
                                <div class="col-md-6">
                                    <x-forms.number :fieldLabel="__('modules.events.cycles')" fieldName="repeat_cycles"
                                        fieldRequired="true" :fieldValue="$task->repeat_cycles" minValue="1" fieldId="repeat_cycles"
                                        :fieldPlaceholder="__('modules.tasks.cyclesToolTip')"
                                        :popover="__('modules.tasks.cyclesToolTip')" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group my-3">
                            <div class="d-flex">
                                <x-forms.checkbox :fieldLabel="__('modules.tasks.dependent')" fieldName="dependent"
                                    fieldId="dependent-task" :checked="$task->dependent_task_id" />
                            </div>
                        </div>

                        <div class="{{ !$task->dependent_task_id ? 'd-none' : '' }}" id="dependent-fields">
                            <x-forms.select fieldId="dependent_task_id" :fieldLabel="__('modules.tasks.dependentTask')"
                                fieldName="dependent_task_id" search="true">
                                <option value="">--</option>
                                @foreach ($allTasks as $item)
                                    <option @if ($item->id == $task->dependent_task_id) selected @endif value="{{ $item->id }}">
                                        {{ $item->heading }}
                                        (@lang('app.dueDate'):
                                        @if(!is_null($item->due_date))
                                            {{ $item->due_date->translatedFormat(company()->date_format) }})
                                        @endif
                                    </option>
                                @endforeach
                            </x-forms.select>
                        </div>
                    </div>
                    <input type = "hidden" name = "mention_user_ids" id = "mentionUserId" class ="mention_user_ids">

                    @if ($addTaskFilePermission == 'all' || $addTaskFilePermission == 'added')
                        <div class="col-lg-12">
                            <x-forms.file-multiple class="mr-0 mr-lg-2 mr-md-2"
                                :fieldLabel="__('app.menu.addFile')" fieldName="file"
                                fieldId="task-files-upload-dropzone" />
                            <input type="hidden" name="image_url" id="image_url">
                        </div>
                        <input type="hidden" name="addedFiles" id="addedFiles">
                    @endif

                </div>
                <x-forms.custom-field :fields="$fields" :model="$task"></x-forms.custom-field>

                <x-form-actions>
                    <x-forms.button-primary id="save-task-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('tasks.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>

    </div>
</div>


<script src="{{ asset('vendor/jquery/dropzone.min.js') }}"></script>
<script>
    $(document).ready(function() {
        var $body = $('body');
        var namespace = '.taskEdit';

        // Ensure we start with a clean slate for event listeners
        $body.off(namespace);
        var dp1, dp2;
        var taskDropzone;

        function checkLeaves() {
            var startDate = $('#task_start_date').val();
            var dueDate = $('#due_date').val();
            var userId = $('#selectAssignee').val();

            $.easyAjax({
                url: "{{ route('tasks.checkLeaves') }}",
                type: 'GET',
                data: { due_date: dueDate, start_date: startDate, user_id: userId },
                success: function(response) {
                    $('.show-leave').removeClass('d-none');
                    var leaveData = [];
                    $.each(response.data, function(index, value) {
                        leaveData.push(index + " {{ __('modules.tasks.leaveOn') }} " + value + "\n");
                    });
                    var label = '<label id="leave-date"> {{ __("modules.tasks.leaveMessage") }} <i class="fa fa-question-circle" data-toggle="tooltip" data-original-title="' + leaveData.join('') + '" id="leave-tooltip"></i></label>';
                    $(".show-leave").html(label);
                    $('#leave-tooltip').tooltip();
                }
            });
        }

        $(".select-picker").selectpicker();

        $('.custom-date-picker').each(function(ind, el) {
            datepicker(el, {
                position: 'bl',
                ...datepickerConfig
            });
        });

        $body.on('change' + namespace, '#project-id, #project_id', function() {
            var id = $(this).val();
            if (!id) {
                $('#clientDetails').hide();
                return;
            }
            $('#clientDetails').show();

            $.easyAjax({
                url: "{{ route('tasks.clientDetail') }}",
                type: "GET",
                data: { id: id },
                success: function(response) {
                    $('#clientDetails').html(response.data);
                }
            });

            // Milestones
            $.easyAjax({
                url: "{{ route('milestones.by_project', ':id') }}".replace(':id', id),
                container: '#save-task-data-form',
                type: "GET",
                blockUI: true,
                success: function(response) {
                    if (response.status == 'success') {
                        $('#milestone-id').html(response.data).selectpicker('refresh');
                    }
                }
            });

            // Members and Quill
            $.easyAjax({
                url: "{{ route('projects.members', ':id') }}".replace(':id', id),
                type: "GET",
                container: '#save-task-data-form',
                blockUI: true,
                success: function (data) {
                    destory_editor('#description');
                    quillMention(data.userData, '#description');
                    $('#selectAssignee').html(data.data).selectpicker('refresh');
                    $('#cc_users').html(data.data).selectpicker('refresh');
                    $('.projectId').text(data.unique_id);
                }
            });

            // Dependent Tasks
            $.easyAjax({
                url: "{{ route('tasks.project_tasks', ':id') }}".replace(':id', id),
                type: "GET",
                container: '#save-task-data-form',
                blockUI: true,
                success: function (data) {
                    $('#dependent_task_id').html(data.data).selectpicker('refresh');
                    $('.projectId').text(data.unique_id);
                }
            });

            // Labels
            $.easyAjax({
                url: "{{ route('projects.labels', ':id') }}".replace(':id', id),
                type: "GET",
                container: '#save-task-data-form',
                blockUI: true,
                success: function (data) {
                    $('#task_labels').html(data.data).selectpicker('refresh');
                }
            });
        });

        if ("{{ $addTaskFilePermission }}" == "all" || "{{ $addTaskFilePermission }}" == "added") {
            Dropzone.autoDiscover = false;
            taskDropzone = new Dropzone("div#task-files-upload-dropzone", {
                dictDefaultMessage: "{{ __('app.dragDrop') }}",
                url: "{{ route('task-files.store') }}",
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                paramName: "file",
                maxFilesize: DROPZONE_MAX_FILESIZE,
                maxFiles: DROPZONE_MAX_FILES,
                autoProcessQueue: false,
                uploadMultiple: true,
                addRemoveLinks: true,
                parallelUploads: DROPZONE_MAX_FILES,
                acceptedFiles: DROPZONE_FILE_ALLOW,
                init: function() {
                    window.taskDropzone = this;
                }
            });

            taskDropzone.on('sending', function(file, xhr, formData) {
                formData.append('task_id', "{{ $task->id }}");
                $.easyBlockUI();
            });

            taskDropzone.on('queuecomplete', function() {
                window.location.href = "{{ route('tasks.index') }}";
                setTimeout(function() { window.location.reload(); }, 1500);
            });

            taskDropzone.on('error', function (file, message) {
                taskDropzone.removeFile(file);
                var $grp = $('div#task-files-upload-dropzone').closest(".form-group");
                $grp.find(".help-block").remove();
                $grp.append('<div class="help-block invalid-feedback">' + message + '</div>').addClass("has-error");
                $grp.siblings("label").addClass("is-invalid");
            });
        }

        $("#selectAssignee").selectpicker({
            actionsBox: true,
            selectAllText: "{{ __('modules.permission.selectAll') }}",
            deselectAllText: "{{ __('modules.permission.deselectAll') }}",
            multipleSeparator: " ",
            selectedTextFormat: "count > 8",
            countSelectedText: (selected, total) => selected + " {{ __('app.membersSelected') }} "
        });

        $("#cc_users").selectpicker({
            actionsBox: true,
            selectAllText: "{{ __('modules.permission.selectAll') }}",
            deselectAllText: "{{ __('modules.permission.deselectAll') }}",
            multipleSeparator: " ",
            selectedTextFormat: "count > 8",
            countSelectedText: (selected, total) => selected + " {{ __('app.membersSelected') }} "
        });

        quillMention(@json($userData), '#description');

        dp1 = datepicker('#task_start_date', {
            position: 'bl',
            dateSelected: new Date("{{ $task->start_date ? str_replace('-', '/', $task->start_date) : str_replace('-', '/', now()) }}"),
            onSelect: (instance, date) => {
                if (dp2) {
                    if (!dp2.dateSelected || dp2.dateSelected.getTime() < date.getTime()) {
                        dp2.setDate(date, true);
                    }
                    dp2.setMin(date);
                }
                checkLeaves();
            },
            ...datepickerConfig
        });

        dp2 = datepicker('#due_date', {
            position: 'bl',
            dateSelected: new Date("{{ $task->due_date ? str_replace('-', '/', $task->due_date) : str_replace('-', '/', now()) }}"),
            onSelect: (instance, date) => {
                if (dp1) dp1.setMax(date);
                checkLeaves();
            },
            ...datepickerConfig
        });
        // Store original data-content for each option so we can restore/modify it
        function cacheOriginalContent(selector) {
            $(selector + ' option').each(function () {
                if (!$(this).data('original-content')) {
                    var existing = $(this).attr('data-content');
                    $(this).data('original-content', existing || null);
                }
            });
        }

        function updateCcUsersDropdown() {
            var assignees = $('#selectAssignee').val() || [];
            $('#cc_users option').each(function () {
                var orig = $(this).data('original-content');
                if (assignees.includes($(this).val())) {
                    $(this).prop('disabled', true);
                    $(this).prop('selected', false);
                    var badgeHtml = orig
                        ? orig + ' <span style="background:#17a2b8;color:#fff;border-radius:3px;padding:1px 5px;font-size:10px;font-weight:600;">Assigned</span>'
                        : $(this).text() + ' <span style="background:#17a2b8;color:#fff;border-radius:3px;padding:1px 5px;font-size:10px;font-weight:600;">Assigned</span>';
                    $(this).attr('data-content', badgeHtml);
                } else {
                    $(this).prop('disabled', false);
                    if (orig) {
                        $(this).attr('data-content', orig);
                    } else {
                        $(this).removeAttr('data-content');
                    }
                }
            });
            $('#cc_users').selectpicker('refresh');
        }

        function updateAssigneeDropdown() {
            var ccUsers = $('#cc_users').val() || [];
            $('#selectAssignee option').each(function () {
                var orig = $(this).data('original-content');
                if (ccUsers.includes($(this).val())) {
                    $(this).prop('disabled', true);
                    $(this).prop('selected', false);
                    var badgeHtml = orig
                        ? orig + ' <span style="background:#6c757d;color:#fff;border-radius:3px;padding:1px 5px;font-size:10px;font-weight:600;">CC</span>'
                        : $(this).text() + ' <span style="background:#6c757d;color:#fff;border-radius:3px;padding:1px 5px;font-size:10px;font-weight:600;">CC</span>';
                    $(this).attr('data-content', badgeHtml);
                } else {
                    $(this).prop('disabled', false);
                    if (orig) {
                        $(this).attr('data-content', orig);
                    } else {
                        $(this).removeAttr('data-content');
                    }
                }
            });
            $('#selectAssignee').selectpicker('refresh');
        }

        cacheOriginalContent('#cc_users');
        cacheOriginalContent('#selectAssignee');
        updateCcUsersDropdown();
        updateAssigneeDropdown();

        $body.on('change' + namespace, '#selectAssignee', function() {
            checkLeaves();
            updateCcUsersDropdown();
        });

        $body.on('change' + namespace, '#cc_users', function() {
            updateAssigneeDropdown();
        });
        $body.on('click' + namespace, '#save-task-form', function() {
            var $form = $('#save-task-data-form');
            var $desc = $form.find('#description');
            var note = $desc.find('.ql-editor').html() || ($desc.children()[0] ? $desc.children()[0].innerHTML : '');
            $form.find('#description-text').val(note);
            
            var users = $desc.find('span[data-id]').map(function(){ return $(this).attr('data-id'); }).get();
            $form.find('#mentionUserId').val(users.join(','));

            var data = $form.serialize() + '&mention_user_id=' + users.join(',');

            $.easyAjax({
                url: "{{ route('tasks.update', $task->id) }}",
                container: '#save-task-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                file: true,
                buttonSelector: "#save-task-form",
                data: data,
                success: function(response) {
                    if (taskDropzone && taskDropzone.getQueuedFiles().length > 0) {
                        taskDropzone.processQueue();
                    } else if ($(RIGHT_MODAL).hasClass('in')) {
                        document.getElementById('close-task-detail').click();
                        if (window.LaravelDataTables && window.LaravelDataTables["allTasks-table"]) {
                            window.LaravelDataTables["allTasks-table"].draw(false);
                        } else {
                            window.location.href = response.redirectUrl;
                            setTimeout(function() { window.location.reload(); }, 1500);
                        }
                    } else {
                        window.location.href = response.redirectUrl;
                        setTimeout(function() { window.location.reload(); }, 1500);
                    }
                }
            });
        });

        $body.on('click' + namespace, '#create_task_category', function() {
            $.ajaxModal(MODAL_LG, "{{ route('taskCategory.create') }}");
        });

        $body.on('click' + namespace, '#department-setting', function() {
            $.ajaxModal(MODAL_LG, "{{ route('departments.create') }}");
        });

        $body.on('change' + namespace, '#client_view_task', function() {
            $('#clientNotification').toggleClass('d-none');
        });

        $body.on('click' + namespace, '#without_duedate', function() {
            $('.dueDateBox').toggle();
        });

        $body.on('change' + namespace, '#set_time_estimate', function() {
            $('#set-time-estimate-fields').toggleClass('d-none');
        });

        $body.on('change' + namespace, '#repeat-task', function() {
            $('#repeat-fields').toggleClass('d-none');
        });

        $body.on('change' + namespace, '#dependent-task', function() {
            $('#dependent-fields').toggleClass('d-none');
        });

        $body.on('click' + namespace, '#createTaskLabel', function() {
            var projectID = $('#project-id').val() || $('#project_id').val();
            var url = "{{ route('task-label.create') }}?task_id={{ $task->id }}&project_id=" + projectID;
            $.ajaxModal(MODAL_XL, url);
        });

        $body.on('click' + namespace, '#add-project', function() {
            $(MODAL_XL).modal('show');
            $.easyAjax({
                url: "{{ route('projects.create') }}",
                blockUI: true,
                container: MODAL_XL,
                success: function(response) {
                    if (response.status == "success") {
                        $(MODAL_XL + ' .modal-body').html(response.html);
                        $(MODAL_XL + ' .modal-title').html(response.title);
                        init(MODAL_XL);
                    }
                }
            });
        });

        $body.on('click' + namespace, '#add-employee', function() {
            $(MODAL_XL).modal('show');
            $.easyAjax({
                url: "{{ route('employees.create') }}",
                blockUI: true,
                container: MODAL_XL,
                success: function(response) {
                    if (response.status == "success") {
                        $(MODAL_XL + ' .modal-body').html(response.html);
                        $(MODAL_XL + ' .modal-title').html(response.title);
                        init(MODAL_XL);
                    }
                }
            });
        });

        <x-forms.custom-field-filejs/>

        init(RIGHT_MODAL);

        document.addEventListener('turbo:before-cache', function cleanup() {
            // No-op here, we clean up at the start of the next initialization
            // to ensure buttons stay responsive as long as the modal is visible
            document.removeEventListener('turbo:before-cache', cleanup);
        }, { once: true });
    });

    function checkboxChange(parentClass, id) {
        let checkedData = '';
        $('.' + parentClass).find("input[type= 'checkbox']:checked").each(function () {
            checkedData = (checkedData !== '') ? checkedData + ', ' + $(this).val() : $(this).val();
        });
        $('#' + id).val(checkedData);
    }
</script>
