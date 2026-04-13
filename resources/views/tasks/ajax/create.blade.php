@php
    $addTaskCategoryPermission = user()->permission('add_task_category');
    $viewTaskCategoryPermission = user()->permission('view_task_category');
    $addEmployeePermission = user()->permission('add_employees');
    $addTaskFilePermission = user()->permission('add_task_files');
    $addTaskPermission = user()->permission('add_tasks');
    $viewMilestonePermission = user()->permission('view_project_milestones');
    $checked = request()->has('duplicate_task') ? ($projectId = $task->project_id) : ($projectId = '');
@endphp

<meta name="turbo-cache-control" content="no-cache">

<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-task-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('modules.tasks.taskInfo')</h4>
                <div class="row p-20">

                    <div class="col-lg-6 col-md-6">
                        <x-forms.text :fieldLabel="__('app.title')" fieldName="heading" fieldRequired="true"
                                      fieldId="heading" :fieldPlaceholder="__('placeholders.task')"
                                      :fieldValue="$task ? $task->heading : ''"/>
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
                                        <option
                                            @if (!is_null($task) && $task->task_category_id == $category->id) selected
                                            @endif value="{{ $category->id }}">{{ mb_ucwords($category->category_name) }}
                                        </option>

                                    @endforeach
                                @endif
                            </select>

                            @if ($addTaskCategoryPermission == 'all' || $addTaskCategoryPermission == 'added')
                                <x-slot name="append">
                                    <button id="create_task_category" type="button"
                                            class="btn btn-outline-secondary border-grey"
                                            data-toggle="tooltip"
                                            data-original-title="{{ __('modules.taskCategory.addTaskCategory') }}">@lang('app.add')</button>
                                </x-slot>
                            @endif
                        </x-forms.input-group>
                    </div>

                    <div class="col-md-12 col-lg-6">


                        @if (isset($project) && !is_null($project))
                            <input type="hidden" name="project_id" id="project_id" value="{{ $project->id }}">
                            <input type="hidden" name="task_project_id" value="{{ $project->id }}">
                            <x-forms.text :fieldLabel="__('app.project')" fieldName="projectName" fieldId="projectName"
                                          :fieldValue="$project->project_name" fieldReadOnly="true"
                            />

{{--                        @elseif($projects->count() > \App\Models\GlobalSetting::SELECT2_SHOW_COUNT)--}}
{{--                            <x-forms.select2-ajax fieldId="project_id" fieldName="project_id"--}}
{{--                                                  :fieldLabel="__('app.project')"--}}
{{--                                                  :route="route('get.projects-ajax')"--}}
{{--                                                  :placeholder="__('placeholders.searchForProjects')"--}}
{{--                            ></x-forms.select2-ajax>--}}
                        @else
                            <x-forms.select fieldId="project_id" fieldName="project_id" :fieldLabel="__('app.project')"
                                            search="true">
                                <option value="">--</option>
                                @foreach ($projects as $data)
                                    <option
                                        @if ((isset($project) && $project->id == $data->id) || ( !is_null($task) && $data->id == $task->project_id)) selected
                                        @endif value="{{ $data->id }}">
                                        {{ mb_ucwords($data->project_name) }}
                                    </option>
                                @endforeach
                            </x-forms.select>
                        @endif
                    </div>
                    <div class="col-md-6 col-lg-6 pt-5" id='clientDetails'></div>

                    {{-- Custom Task Fields Row --}}
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-12">
                                <x-forms.text :fieldLabel="__('Doc SNo#')" fieldName="doc_sno" fieldId="doc_sno" />
                            </div>

                            <div class="col-md-12">
                                <div class="form-group my-3">
                                    <x-forms.label fieldId="description" :fieldLabel="__('app.description')">
                                    </x-forms.label>
                                    <div id="description">{!! $task ? $task->description : '' !!}</div>
                                    <textarea name="description" id="description-text" class="d-none"></textarea>
                                </div>
                            </div>

                            <div class="col-md-2 col-sm-4">
                                <x-forms.text :fieldLabel="__('Annex A')" fieldName="annex_a" fieldId="annex_a" />
                            </div>
                            <div class="col-md-2 col-sm-4">
                                <x-forms.text :fieldLabel="__('Mode')" fieldName="mode" fieldId="mode" />
                            </div>
                            <div class="col-md-2 col-sm-4">
                                <x-forms.number :fieldLabel="__('Days')" fieldName="days" fieldId="days" />
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <x-forms.text :fieldLabel="__('UOM')" fieldName="uom" fieldId="uom" />
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <x-forms.number :fieldLabel="__('Qty')" fieldName="qty" fieldId="qty" />
                            </div>
                        </div>
                    </div>
                    {{-- End Custom Task Fields Row --}}

                    <div class="col-md-5 col-lg-3">
                        <x-forms.datepicker fieldId="task_start_date" fieldRequired="true"
                                            :fieldLabel="__('modules.projects.startDate')" fieldName="start_date"
                                            :fieldValue="(($task) ? $task->start_date->format(company()->date_format) : \Carbon\Carbon::now(company()->timezone)->translatedFormat(company()->date_format))"
                                            :fieldPlaceholder="__('placeholders.date')"/>
                    </div>

                    <div class="col-md-5 col-lg-3 dueDateBox"
                         @if($task && is_null($task->due_date)) style="display: none" @endif>
                        <x-forms.datepicker fieldId="due_date" fieldRequired="true" :fieldLabel="__('app.dueDate')"
                                fieldName="due_date" :fieldPlaceholder="__('placeholders.date')"
                                :fieldValue="(($task && $task->due_date) ? $task->due_date->format(company()->date_format) : \Carbon\Carbon::now(company()->timezone)->translatedFormat(company()->date_format))"/>
                    </div>
                    <div class="col-md-2 col-lg-2 pt-5">
                        <x-forms.checkbox class="mr-0 mr-lg-2 mr-md-2" :checked="$task ? is_null($task->due_date) : ''"
                                          :fieldLabel="__('app.withoutDueDate')"
                                          fieldName="without_duedate" fieldId="without_duedate" fieldValue="yes"/>
                    </div>

                    <div class="col-md-12 col-lg-12">
                    </div>

                    <div class="col-md-12 col-lg-8">
                        <div class="form-group my-3">
                            <x-forms.label fieldId="selectAssignee" :fieldLabel="__('modules.tasks.assignTo')">
                            </x-forms.label>
                            <x-forms.input-group>
                                <select class="form-control multiple-users" multiple name="user_id[]"
                                        id="selectAssignee" data-live-search="true" data-size="8">
                                    @foreach ($employees as $item)
                                        <x-user-option :user="$item"
                                                       :pill="true"
                                                       :selected="(isset($defaultAssignee) && $defaultAssignee == $item->id) || (!is_null($task) && isset($projectMember) && in_array($item->id, $projectMember))"/>

                                    @endforeach
                                </select>

                                <x-slot name="preappend">
                                    <button id="assign-self" type="button"
                                            class="btn btn-outline-secondary border-grey" data-toggle="tooltip"
                                            data-original-title="@lang('modules.tasks.assignMe')">
                                        <img src="{{ user()->image_url }}" width="23"
                                             class="img-fluid rounded-circle">
                                    </button>
                                </x-slot>

                                @if ($addEmployeePermission == 'all' || $addEmployeePermission == 'added')
                                    <x-slot name="append">
                                        <button id="add-employee" type="button"
                                                class="btn btn-outline-secondary border-grey"
                                                data-toggle="tooltip"
                                                data-original-title="{{ __('modules.employees.addNewEmployee') }}">@lang('app.add')</button>
                                    </x-slot>
                                @endif
                            </x-forms.input-group>
                        </div>
                    </div>
                    <div class="col-md-6 show-leave"></div>

                    <div class="col-md-12">


                </div>

                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-top-grey other-details-button">
                    <a href="javascript:;" class="text-dark toggle-other-details"><i class="fa fa-chevron-down"></i>
                        @lang('modules.client.clientOtherDetails')</a>
                </h4>

                <div class="row p-20 d-none" id="other-details">

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
                                                <option
                                                    data-content="<span class='badge badge-secondary' style='background-color: {{ $label->label_color }}'>{{ $label->label_name }}</span>"
                                                    value="{{ $label->id }}"
                                                    @if($task && isset($selectedLabel) && in_array($label->id, $selectedLabel)) selected @endif>{{ $label->label_name }}</option>
                                            @endforeach
                                        </select>

                                        @if (user()->permission('task_labels') == 'all')
                                            <x-slot name="append">
                                                <button id="createTaskLabel" type="button"
                                                        class="btn btn-outline-secondary border-grey"
                                                        data-toggle="tooltip"
                                                        data-original-title="{{ __('modules.taskLabel.addLabel') }}">@lang('app.add')</button>
                                            </x-slot>
                                        @endif
                                    </x-forms.input-group>
                                </div>
                            </div>

                            <div class="col-md-12 col-lg-4">
                                <x-forms.select fieldName="milestone_id" fieldId="milestone-id"
                                                :fieldLabel="__('modules.projects.milestones')">
                                    <option value="">--</option>
                                    @if($project)
                                        @if(in_array($viewMilestonePermission,['all','owned','added']) || user()->id == $project->client_id)
                                            @foreach ($milestones as $item)
                                                <option value="{{ $item->id }}"
                                                        @if (!is_null($task) && $item->id == $task->milestone_id) selected @endif>{{ $item->milestone_title }}</option>
                                            @endforeach
                                        @endif
                                    @endif
                                </x-forms.select>
                            </div>

                            @if (user()->permission('change_status') == 'all')
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
                                            <option
                                                @if ($columnId == $item->id || ( !is_null($task) && $task->board_column_id == $item->id)) selected
                                                @elseif (company()->default_task_status == $item->id) selected
                                                @endif value="{{ $item->id }}" data-content = "{{$icon}}">
                                            </option>
                                        @endforeach
                                    </x-forms.select>
                                </div>
                            @endif

                            <div class="col-lg-3 col-md-6">
                                <x-forms.select fieldId="priority" :fieldLabel="__('modules.tasks.priority')"
                                                fieldName="priority">
                                    <option @if (!is_null($task) && $task->priority == 'high') selected
                                            @endif
                                            data-content="<i class='fa fa-circle mr-2' style='color: #dd0000'></i> @lang('modules.tasks.high')"
                                            value="high">@lang('modules.tasks.high')</option>
                                    <option @if (!is_null($task) && $task->priority == 'medium') selected
                                            @endif value="medium"
                                            data-content="<i class='fa fa-circle mr-2' style='color: #ffc202'></i> @lang('modules.tasks.medium')"
                                            @if (is_null($task)) selected @endif>@lang('modules.tasks.medium')</option>
                                    <option @if (!is_null($task) && $task->priority == 'low') selected
                                            @endif
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
                                                  :checked="$task ? $task->is_private : ''"/>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="form-group">
                            <div class="d-flex mt-5">
                                <x-forms.checkbox :fieldLabel="__('modules.tasks.billable')" :checked="true"
                                                  fieldName="billable" fieldId="billable"
                                                  :popover="__('modules.tasks.billableInfo')"
                                                  :checked="$task ? $task->billable : ''"/>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="form-group">
                            <div class="d-flex mt-5">
                                <x-forms.checkbox :fieldLabel="__('modules.tasks.setTimeEstimate')"
                                                  fieldName="set_time_estimate" fieldId="set_time_estimate"
                                                  :checked="($task ? $task->estimate_hours > 0 || $task->estimate_minutes > 0 : '')"/>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3 d-none" id="set-time-estimate-fields">
                        <div class="form-group mt-5">
                            <input type="number" min="0" class="w-25 border rounded p-2 height-35 f-14"
                                   name="estimate_hours" value="{{ $task ? $task->estimate_hours : '0'}}">
                            @lang('app.hrs')
                            &nbsp;&nbsp;
                            <input type="number" min="0" name="estimate_minutes"
                                   value="{{ $task ? $task->estimate_minutes : '0'}}"
                                   class="w-25 height-35 f-14 border rounded p-2">
                            @lang('app.mins')
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group my-3">
                            <div class="d-flex">
                                <x-forms.checkbox :fieldLabel="__('modules.events.repeat')" fieldName="repeat"
                                                  fieldId="repeat-task" :checked="$task ? $task->repeat : ''"/>
                            </div>
                        </div>

                        <div class="form-group my-3 {{!is_null($task) && $task->repeat ? '' : 'd-none'}}"
                             id="repeat-fields">
                            <div class="row">
                                <div class="col-md-6 mt-3">
                                    <x-forms.label fieldId="repeatEvery" fieldRequired="true"
                                                   :fieldLabel="__('modules.events.repeatEvery')"
                                                    :popover="__('modules.tasks.repeatInfo')">
                                    </x-forms.label>
                                    <x-forms.input-group>
                                        <input type="number" min="1" name="repeat_count"
                                               class="form-control f-14" value="{{$task ? $task->repeat_count : '1'}}">

                                        <x-slot name="append">
                                            <select name="repeat_type" class="select-picker form-control">
                                                <option value="day"
                                                        @if (!is_null($task) && $task->repeat_type == 'day') selected @endif>@lang('app.day')</option>
                                                <option value="week"
                                                        @if (!is_null($task) && $task->repeat_type == 'week') selected @endif>@lang('app.week')</option>
                                                <option value="month"
                                                        @if (!is_null($task) && $task->repeat_type == 'month') selected @endif>@lang('app.month')</option>
                                                <option value="year"
                                                        @if (!is_null($task) && $task->repeat_type == 'year') selected @endif>@lang('app.year')</option>
                                            </select>
                                        </x-slot>
                                    </x-forms.input-group>
                                </div>
                                <div class="col-md-6">
                                    <x-forms.number :fieldLabel="__('modules.events.cycles')" fieldName="repeat_cycles"
                                                    fieldRequired="true"
                                                    :fieldValue="$task ? $task->repeat_cycles : '1'" minValue="1"
                                                    fieldId="repeat_cycles"
                                                    :fieldPlaceholder="__('modules.tasks.cyclesToolTip')"
                                                    :popover="__('modules.tasks.cyclesToolTip')"/>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(is_null($task))
                        <div class="col-md-6">
                            <div class="form-group my-3">
                                <div class="d-flex">
                                    <x-forms.checkbox :fieldLabel="__('modules.tasks.dependent')" fieldName="dependent"
                                                      fieldId="dependent-task"/>
                                </div>
                            </div>

                            <div class="d-none" id="dependent-fields">
                                <x-forms.select fieldId="dependent_task_id"
                                                :fieldLabel="__('modules.tasks.dependentTask')"
                                                fieldName="dependent_task_id" search="true">
                                    <option value="">--</option>
                                    @foreach ($allTasks as $item)
                                        <option value="{{ $item->id }}">{{ $item->heading }} ( @lang('app.dueDate'):
                                            @if(!is_null($item->due_date)) {{ $item->due_date->translatedFormat(company()->date_format) }} ) @else - @endif
                                        </option>
                                    @endforeach
                                </x-forms.select>
                            </div>
                        </div>
                    @endif
                    <input type = "hidden" name = "mention_user_ids" id = "mentionUserId" class ="mention_user_ids">
                    @if ($addTaskFilePermission == 'all' || $addTaskFilePermission == 'added')
                        <div class="col-lg-12">
                            <x-forms.file-multiple class="mr-0 mr-lg-2 mr-md-2"
                                                   :fieldLabel="__('app.menu.addFile')" fieldName="file"
                                                   fieldId="task-file-upload-dropzone"/>
                            <input type="hidden" name="image_url" id="image_url">
                        </div>
                        <input type="hidden" name="taskID" id="taskID">
                        <input type="hidden" name="addedFiles" id="addedFiles">
                    @endif

                    <x-forms.custom-field :fields="$fields" class="col-sm-12"></x-forms.custom-field>

                </div>


                <x-form-actions>
                    <x-forms.button-primary class="mr-3" id="save-task-form" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-secondary class="mr-3 d-none d-md-block" id="save-more-task-form"
                                              icon="check-double">@lang('app.saveAddMore')
                    </x-forms.button-secondary>
                    <x-forms.button-cancel :link="route('tasks.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>

    </div>
</div>


<script src="{{ asset('vendor/jquery/dropzone.min.js') }}"></script>
<script src="{{ asset('vendor/jquery/dropzone.min.js') }}"></script>
<script>
    (function() {
        var $body = $('body');
        var namespace = '.taskCreate';
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
                    if (response.data === null) {
                        $(".show-leave").html('');
                        return;
                    }
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

        $body.on('change' + namespace, '#project_id', function() {
            var id = $(this).val();
            if (!id) {
                $('#clientDetails').html('');
                return;
            }

            $.easyAjax({
                url: "{{ route('tasks.clientDetail') }}",
                type: "GET",
                data: { id: id },
                success: function(response) {
                    $('#clientDetails').html(response.data);
                }
            });
        });

        $('.custom-date-picker').each(function(ind, el) {
            datepicker(el, {
                position: 'bl',
                ...datepickerConfig
            });
        });

        if ("{{ $addTaskFilePermission }}" == "all" || "{{ $addTaskFilePermission }}" == "added") {
            Dropzone.autoDiscover = false;
            taskDropzone = new Dropzone("div#task-file-upload-dropzone", {
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
                init: function () {
                    window.taskDropzone = this;
                }
            });

            taskDropzone.on('sending', function (file, xhr, formData) {
                formData.append('task_id', $('#taskID').val());
                $.easyBlockUI();
            });

            taskDropzone.on('queuecomplete', function () {
                window.location.href = localStorage.getItem("redirect_task");
                setTimeout(function() { window.location.reload(); }, 1500);
            });

            taskDropzone.on('error', function (file, message) {
                taskDropzone.removeFile(file);
                var $grp = $('div#file-upload-dropzone').closest(".form-group");
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

        dp1 = datepicker('#task_start_date', {
            position: 'bl',
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
            onSelect: (instance, date) => {
                if (dp1) dp1.setMax(date);
                checkLeaves();
            },
            ...datepickerConfig
        });

        $body.on('change' + namespace, '#selectAssignee', checkLeaves);

        $body.on('change' + namespace, '#project_id', function () {
            let id = $(this).val() || 0;
            
            // Milestones
            $.easyAjax({
                url: "{{ route('milestones.by_project', ':id') }}".replace(':id', id),
                container: '#save-task-data-form',
                type: "GET",
                blockUI: true,
                success: function (response) {
                    if (response.status == 'success') {
                        $('#milestone-id').html(response.data).selectpicker('refresh');
                    }
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
                    $('.projectId').text(data.unique_id + '-');
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
                    $('.projectId').text(data.unique_id + '-');
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

        quillMention(@json($userData), '#description');

        $body.on('click' + namespace, '#save-more-task-form', function () {
            var $form = $('#save-task-data-form');
            var $desc = $form.find('#description');
            var note = $desc.find('.ql-editor').html() || ($desc.children()[0] ? $desc.children()[0].innerHTML : '');
            $form.find('#description-text').val(note);
            
            var data = $form.serialize() + '&add_more=true';
            saveTask(data, "{{ route('tasks.store') }}?taskId={{$task ? $task->id : ''}}", "#save-more-task-form");
        });

        $body.on('click' + namespace, '#save-task-form', function () {
            var $form = $('#save-task-data-form');
            var $desc = $form.find('#description');
            var note = $desc.find('.ql-editor').html() || ($desc.children()[0] ? $desc.children()[0].innerHTML : '');
            $form.find('#description-text').val(note);
            
            var mention_user_id = $desc.find('span[data-id]').map(function(){ return $(this).attr('data-id'); }).get();
            $form.find('#mentionUserId').val(mention_user_id.join(','));

            var data = $form.serialize() + '&mention_user_id=' + mention_user_id.join(',');
            saveTask(data, "{{ route('tasks.store') }}?taskId={{$task ? $task->id : ''}}", "#save-task-form");
        });

        function saveTask(data, url, buttonSelector) {
            $.easyAjax({
                url: url,
                container: '#save-task-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                file: true,
                buttonSelector: buttonSelector,
                data: data,
                success: function (response) {
                    if (response.status === 'success') {
                        if (taskDropzone && taskDropzone.getQueuedFiles().length > 0) {
                            $('#taskID').val(response.taskID);
                            localStorage.setItem("redirect_task", (response.add_more == true) ? window.location.href : response.redirectUrl);
                            taskDropzone.processQueue();
                        } else if (response.add_more == true) {
                            var $container = $.trim($(RIGHT_MODAL_CONTENT).html()) ? $(RIGHT_MODAL_CONTENT) : $('.content-wrapper');
                            $container.html(response.html.html);
                            if (!$container.is(RIGHT_MODAL_CONTENT)) init('.content-wrapper');
                            $('#add_more').val(false);
                        } else {
                            window.location.href = response.redirectUrl;
                            setTimeout(function() { window.location.reload(); }, 1500);
                        }

                        if (typeof showTable === 'function') showTable();
                    }
                }
            });
        }

        $body.on('click' + namespace, '#assign-self', function () {
            $('#selectAssignee').val('{{ $user->id }}').selectpicker('refresh');
        });

        $body.on('click' + namespace, '#without_duedate', function () {
            $('.dueDateBox').toggle();
        });

        $body.on('click' + namespace, '#create_task_category', function () {
            $.ajaxModal(MODAL_LG, "{{ route('taskCategory.create') }}");
        });

        $body.on('click' + namespace, '#department-setting', function () {
            $.ajaxModal(MODAL_LG, "{{ route('departments.create') }}");
        });

        $body.on('change' + namespace, '#client_view_task', function () {
            $('#clientNotification').toggleClass('d-none');
        });

        $body.on('change' + namespace, '#set_time_estimate', function () {
            $('#set-time-estimate-fields').toggleClass('d-none');
        });

        $body.on('change' + namespace, '#repeat-task', function () {
            $('#repeat-fields').toggleClass('d-none');
        });

        $body.on('change' + namespace, '#dependent-task', function () {
            $('#dependent-fields').toggleClass('d-none');
        });

        $body.on('click' + namespace, '.toggle-other-details', function () {
            $(this).find('svg').toggleClass('fa-chevron-down fa-chevron-up');
            $('#other-details').toggleClass('d-none');
        });

        $body.on('click' + namespace, '#createTaskLabel', function () {
            var url = "{{ route('task-label.create') }}?task_id={{$task ? $task->id : ''}}&project_id=" + $('#project_id').val();
            $.ajaxModal(MODAL_XL, url);
        });

        $body.on('click' + namespace, '#add-project', function () {
            $(MODAL_XL).modal('show');
            $.easyAjax({
                url: "{{ route('projects.create') }}",
                blockUI: true,
                container: MODAL_XL,
                success: function (response) {
                    if (response.status == "success") {
                        $(MODAL_XL + ' .modal-body').html(response.html);
                        $(MODAL_XL + ' .modal-title').html(response.title);
                        init(MODAL_XL);
                    }
                }
            });
        });

        $body.on('click' + namespace, '#add-employee', function () {
            $(MODAL_XL).modal('show');
            $.easyAjax({
                url: "{{ route('employees.create') }}",
                blockUI: true,
                container: MODAL_XL,
                success: function (response) {
                    if (response.status == "success") {
                        $(MODAL_XL + ' .modal-body').html(response.html);
                        $(MODAL_XL + ' .modal-title').html(response.title);
                        init(MODAL_XL);
                    }
                }
            });
        });

        init(RIGHT_MODAL);

        window.addEventListener('turbo:before-cache', function cleanup() {
            $body.off(namespace);
            if (dp1) dp1.destroy();
            if (dp2) dp2.destroy();
            if (taskDropzone) {
                taskDropzone.destroy();
                window.taskDropzone = undefined;
            }
            destory_editor('#description');
            window.removeEventListener('turbo:before-cache', cleanup);
        }, { once: true });
    })();

    function checkboxChange(parentClass, id) {
        let checkedData = '';
        $('.' + parentClass).find("input[type= 'checkbox']:checked").each(function () {
            checkedData = (checkedData !== '') ? checkedData + ', ' + $(this).val() : $(this).val();
        });
        $('#' + id).val(checkedData);
    }
</script>
