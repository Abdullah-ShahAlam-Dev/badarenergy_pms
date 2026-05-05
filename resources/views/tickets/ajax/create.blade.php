@php
    $manageTypePermission = user()->permission('manage_ticket_type');
    $manageAgentPermission = user()->permission('manage_ticket_agent');
    $manageChannelPermission = user()->permission('manage_ticket_channel');
    $manageGroupPermission = user()->permission('manage_ticket_groups');
@endphp

<meta name="turbo-cache-control" content="no-cache">

<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/css/tagify.css') }}">

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-ticket-data-form">
            <input type="hidden" id="replyID">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('modules.tickets.ticketDetail')</h4>
                <div class="row p-20">
                    @if (!in_array('client', user_roles()))
                        @if ($addPermission == 'all')
                            @if (!(isset($client)) && !(isset($employee)) && in_array('clients', user_modules()) && in_array('employees', user_modules()))
                                <div class="col-md-4">
                                    <div class="form-group my-3">
                                        <x-forms.label fieldId="requester-client"
                                                       :fieldLabel="__('modules.tickets.requester')"/>
                                        <div class="d-flex">
                                            <x-forms.radio fieldId="requester-client" :fieldLabel="__('app.client')"
                                                           fieldName="requester_type" fieldValue="client"
                                                           checked="true">
                                            </x-forms.radio>
                                            <x-forms.radio fieldId="requester-employee" :fieldLabel="__('app.employee')"
                                                           fieldValue="employee"
                                                           fieldName="requester_type"></x-forms.radio>
                                        </div>
                                    </div>
                                </div>
                            @elseif(!in_array('employees', user_modules()))
                                <input type="hidden" name="requester_type" id="requester_type" value="client">
                            @elseif(!in_array('clients', user_modules()))
                                <input type="hidden" name="requester_type" id="requester_type" value="employee">
                            @endif
                            <input type = "hidden" name = "mention_user_ids" id = "mentionUserId" class ="mention_user_ids">

                            <div class="col-md-4  @if (isset($employee) || !in_array('clients', user_modules())) d-none @endif " id="client-requester">
                                @if (isset($client) && !is_null($client))
                                    <x-forms.label fieldId="requester-client" class="mt-3"
                                                   :fieldLabel="__('modules.tickets.requesterName')"/>
                                    <input type="hidden" name="client_id" id="client_id" value="{{ $client->id }}">
                                    <input type="text" value="{{ ucfirst($client->name) }}"
                                           class="form-control height-35 f-15 readonly-background" readonly>
                                @else
                                    <x-forms.select fieldId="client_id"
                                                    :fieldLabel="__('modules.tickets.requesterName')"
                                                    fieldName="client_id"
                                                    search="true" alignRight="true" fieldRequired="true">
                                        <option value="">--</option>
                                        @foreach ($clients as $client)
                                            <x-user-option :user="$client" :additionalText="$client->clientDetails->company_name" />
                                        @endforeach
                                    </x-forms.select>
                                @endif
                            </div>

                            <div class="col-md-4 @if (!(isset($employee)) && in_array('clients', user_modules())) d-none @endif" id="employee-requester">
                                @if(isset($employee) && !is_null($employee))
                                    <x-forms.label class="my-3" fieldId="requestuser_id"
                                                   :fieldLabel="__('modules.tickets.requesterName')"
                                                   fieldRequired="true">
                                    </x-forms.label>
                                    <input type="hidden" name="user_id" id="user_id" value="{{ $employee->id }}">
                                    <input type="text" value="{{ $employee->name }}"
                                           class="form-control height-35 f-15 readonly-background" readonly>
                                @else
                                    <x-forms.label class="my-3" fieldId="requestuser_id"
                                                   :fieldLabel="__('modules.tickets.requesterName')"
                                                   fieldRequired="true">
                                    </x-forms.label>
                                    <x-forms.input-group>
                                        <select class="form-control select-picker" name="user_id" id="user_id"
                                                data-live-search="true" data-size="8">
                                            <option value="">--</option>
                                            @foreach ($employees as $employee)
                                                <x-user-option :user="$employee" />
                                            @endforeach
                                        </select>
                                    </x-forms.input-group>
                                @endif
                            </div>
                        @else
                            <input type="hidden" name="requester_type" value="employee">
                            <input type="hidden" name="user_id" value="{{ user()->id }}">
                        @endif
                    @else
                        <input type="hidden" name="requester_type" value="client">
                        <input type="hidden" name="client_id" value="{{ user()->id }}">
                    @endif
                    <div class="col-md-4 assign_group">
                        <x-forms.label class="mt-3" fieldId="ticket_group" fieldRequired="true"
                            :fieldLabel="__('modules.tickets.assignGroup')">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" id="ticket_group" name="group_id"
                                data-live-search="true">
                                @foreach ($groups as $group)
                                    <option value="{{ $group->id }}">{{ mb_ucwords($group->group_name) }}</option>
                                @endforeach
                            </select>
                            @if($manageGroupPermission == 'all')
                                <x-slot name="append">
                                    <button id="manage-groups" type="button"
                                        class="btn btn-outline-secondary border-grey">@lang('app.add')</button>
                                </x-slot>
                            @endif
                        </x-forms.input-group>
                    </div>

                    @if (!in_array('client', user_roles()))
                        <div class="col-md-6 col-lg-3">
                            <x-forms.label class="mt-3" fieldId="ticket_agent_id" :fieldLabel="__('modules.tickets.agent')">
                            </x-forms.label>
                            <x-forms.input-group>
                                <select class="form-control select-picker" name="agent_id" id="ticket_agent_id"
                                        data-live-search="true" data-size="8">
                                    <option value="">--</option>
                                    @foreach ($groups as $group)
                                        @if (count($group->enabledAgents) > 0)
                                            <optgroup label="{{ mb_ucwords($group->group_name) }}">
                                                @foreach ($group->enabledAgents as $agent)

                                                    <x-user-option :user="$agent->user" :agent="true"></x-user-option>

                                                @endforeach
                                            </optgroup>
                                        @endif
                                    @endforeach
                                </select>
                                @if ($manageAgentPermission == 'all')
                                    <x-slot name="append">
                                        <button id="add-agent" type="button"
                                                class="btn btn-outline-secondary border-grey"
                                                data-toggle="tooltip" data-original-title="{{ __('app.addNew').' '.__('modules.tickets.agent') }}">@lang('app.add')</button>
                                    </x-slot>
                                @endif
                            </x-forms.input-group>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <x-forms.label class="mt-3" fieldId="cc_users" :fieldLabel="'CC Users'">
                            </x-forms.label>
                            <x-forms.input-group>
                                <select class="form-control select-picker" name="cc_users[]" id="cc_users"
                                        data-live-search="true" data-size="8" multiple>
                                    @foreach ($employees as $employee)
                                        <x-user-option :user="$employee" />
                                    @endforeach
                                </select>
                            </x-forms.input-group>
                        </div>

                    @endif
                    <div class="col-md-12">
                        <x-forms.text :fieldLabel="__('modules.tickets.ticketSubject')" fieldName="subject"
                                      fieldRequired="true" fieldId="subject"/>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group my-3">
                            <x-forms.label fieldId="description" :fieldLabel="__('app.description')"
                                           fieldRequired="true">
                            </x-forms.label>
                            <div id="description"></div>
                            <textarea name="description" id="description-text" class="d-none"></textarea>
                        </div>
                        <div class="my-3">
                            <a class="f-15 f-w-500" href="javascript:;" id="add-file"><i
                                    class="fa fa-paperclip font-weight-bold mr-1"></i>@lang('modules.projects.uploadFile')
                            </a>
                        </div>
                    </div>
                </div>

                <div class="row px-4">
                    <div class="col-md-12">
                        <x-forms.file-multiple class="mr-0 mr-lg-2 mr-md-2 upload-section d-none"
                                               :fieldLabel="__('app.menu.addFile')"
                                               fieldName="file" fieldId="ticket-file-upload-dropzone"/>
                        <input type="hidden" name="image_url" id="image_url">
                    </div>

                </div>

                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-top-grey">
                    <a href="javascript:;" class="text-dark toggle-other-details"><i class="fa fa-chevron-down"></i>
                        @lang('modules.client.clientOtherDetails')</a>
                </h4>

                <div class="row p-20 d-none" id="other-details">

                    <div class="col-md-6 col-lg-4">
                        <x-forms.select fieldId="priority" :fieldLabel="__('modules.tasks.priority')"
                                        fieldName="priority">
                            <option data-content="<i class='fa fa-circle mr-2 text-dark-green'></i> {{ __('app.low')}}"
                                value="low">{{ __('app.low') }}</option>
                            <option data-content="<i class='fa fa-circle mr-2 text-blue'></i> {{ __('app.medium')}}"
                                value="medium">@lang('app.medium')</option>
                            <option data-content="<i class='fa fa-circle mr-2 text-warning'></i> {{ __('app.high')}}"
                                value="high">@lang('app.high')</option>
                            <option data-content="<i class='fa fa-circle mr-2 text-red'></i> {{ __('app.urgent')}}"
                                value="urgent">@lang('app.urgent')</option>
                        </x-forms.select>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <x-forms.label class="my-3" fieldId="ticket_type_id" :fieldLabel="__('modules.invoices.type')">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="type_id" id="ticket_type_id"
                                    data-live-search="true" data-size="8">
                                <option value="">--</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}">{{ mb_ucwords($type->type) }}</option>
                                @endforeach
                            </select>
                            @if ($manageTypePermission == 'all')
                                <x-slot name="append">
                                    <button id="add-type" type="button"
                                            class="btn btn-outline-secondary border-grey"
                                            data-toggle="tooltip" data-original-title="{{ __('app.addNew').' '.__('modules.tickets.ticketType') }}">@lang('app.add')</button>
                                </x-slot>
                            @endif
                        </x-forms.input-group>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <x-forms.label class="my-3" fieldId="ticket_channel_id"
                                       :fieldLabel="__('modules.tickets.channelName')">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="channel_id" id="ticket_channel_id"
                                    data-live-search="true" data-size="8">
                                <option value="">--</option>
                                @foreach ($channels as $channel)
                                    <option value="{{ $channel->id }}">{{ mb_ucwords($channel->channel_name) }}
                                    </option>
                                @endforeach
                            </select>
                            @if ($manageChannelPermission == 'all')
                                <x-slot name="append">
                                    <button id="add-channel" type="button"
                                            class="btn btn-outline-secondary border-grey"
                                            data-toggle="tooltip" data-original-title="{{ __('app.add').' '.__('modules.tickets.ticketChannel') }}">@lang('app.add')</button>
                                </x-slot>
                            @endif
                        </x-forms.input-group>
                    </div>

                    <div class="col-md-12">
                        <x-forms.text fieldId="tags" :fieldLabel="__('modules.tickets.tags')" fieldName="tags"/>
                    </div>

                    <x-forms.custom-field :fields="$fields" class="col-md-12"></x-forms.custom-field>

                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-ticket-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('tickets.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>

    </div>
</div>


<script src="{{ asset('vendor/jquery/dropzone.min.js') }}"></script>
<script src="{{ asset('vendor/jquery/tagify.min.js') }}"></script>
<script>
    (function() {
        var $body = $('body');
        var namespace = '.ticketsCreate';
        var ticketDropzone;
        var tagify;

        $body.off(namespace);

        $body.on('click' + namespace, '#add-file', function () {
            $('.upload-section').removeClass('d-none');
            $(this).addClass('d-none');
            window.scrollTo(0, document.body.scrollHeight);
        });

        // ── CC ↔ Agent bidirectional badge helpers ──────────────────────────────
        function cacheOriginalContent(selector) {
            $(selector + ' option').each(function () {
                if (!$(this).data('original-content')) {
                    var existing = $(this).attr('data-content');
                    $(this).data('original-content', existing || null);
                }
            });
        }

        function syncCcUsers() {
            let agentId = $('#ticket_agent_id').val();
            let ccUsers = $('#cc_users').val() || [];

            // Update CC Users dropdown labels
            $('#cc_users option').each(function() {
                var orig = $(this).data('original-content');
                var userId = $(this).val();
                
                if (agentId && userId == agentId) {
                    var badge = orig
                        ? orig + ' <span style="background:#17a2b8;color:#fff;border-radius:3px;padding:1px 5px;font-size:10px;font-weight:600;">Agent</span>'
                        : $(this).text() + ' <span style="background:#17a2b8;color:#fff;border-radius:3px;padding:1px 5px;font-size:10px;font-weight:600;">Agent</span>';
                    $(this).attr('data-content', badge);
                } else {
                    if (orig) $(this).attr('data-content', orig);
                    else $(this).removeAttr('data-content');
                }
            });

            // Update Agent dropdown labels
            $('#ticket_agent_id option').each(function() {
                var orig = $(this).data('original-content');
                var userId = $(this).val();

                if (userId && ccUsers.includes(userId)) {
                    var badge = orig
                        ? orig + ' <span style="background:#6c757d;color:#fff;border-radius:3px;padding:1px 5px;font-size:10px;font-weight:600;">CC</span>'
                        : $(this).text() + ' <span style="background:#6c757d;color:#fff;border-radius:3px;padding:1px 5px;font-size:10px;font-weight:600;">CC</span>';
                    $(this).attr('data-content', badge);
                } else {
                    if (orig) $(this).attr('data-content', orig);
                    else $(this).removeAttr('data-content');
                }
            });

            $('#cc_users, #ticket_agent_id').selectpicker('refresh');
        }

        $body.on('change' + namespace, '#ticket_agent_id, #cc_users', function() {
            syncCcUsers();
        });

        function getAgents(groupId) {
            var url = "{{ route('tickets.agent_group', ':id')}}".replace(':id', groupId);
            $.easyAjax({
                url: url,
                type: "GET",
                success: function(response) {
                    var userValues = response.groupData;
                    if (typeof destroy_editor === 'function') destroy_editor('#description');
                    else if (typeof destory_editor === 'function') destory_editor('#description');

                    if (typeof quillMention === 'function') {
                        quillMention(userValues, '#description');
                    }

                    var options = [];
                    if ($.isArray(response.data)) {
                        $.each(response.data, function(index, value) {
                            options.push(value);
                        });
                        $('#ticket_agent_id').html('<option value="">--</option>' + options);
                    } else {
                        $('#ticket_agent_id').html(response.data);
                    }
                    $('#ticket_agent_id').selectpicker('refresh');

                    // Re-cache and sync badges now that agent list has changed
                    cacheOriginalContent('#ticket_agent_id');
                    syncCcUsers();
                }
            });
        }

        if (Dropzone.instances.length > 0) {
            Dropzone.instances.forEach(function(dz) {
                if (dz.element.id === 'ticket-file-upload-dropzone') dz.destroy();
            });
        }

        ticketDropzone = new Dropzone("div#ticket-file-upload-dropzone", {
            dictDefaultMessage: "{{ __('app.dragDrop') }}",
            url: "{{ route('ticket-files.store') }}",
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
                ticketDropzone = this;
            }
        });

        ticketDropzone.on('sending', function (file, xhr, formData) {
            var ids = $('#replyID').val();
            formData.append('ticket_reply_id', ids);
            $.easyBlockUI();
        });

        ticketDropzone.on('queuecomplete', function () {
            window.location.href = "{{ route('tickets.index') }}";
        });

        ticketDropzone.on('error', function (file, message) {
            this.removeFile(file);
            var grp = $(this.element).closest(".form-group");
            grp.find(".help-block").remove();
            grp.append('<div class="help-block invalid-feedback">' + message + '</div>');
            grp.addClass("has-error");
            grp.find("label").addClass("is-invalid");
        });

        var tagInput = document.querySelector('input[name=tags]');
        if (tagInput) tagify = new Tagify(tagInput);

        $body.on('click' + namespace, "input[name=requester_type]", function () {
            $('#client-requester, #employee-requester').toggleClass('d-none');
        });

        $body.on('click' + namespace, '#add-agent', function () {
            $.ajaxModal(MODAL_LG, "{{ route('ticket-agents.create') }}");
        });

        $body.on('click' + namespace, '#add-channel', function () {
            $.ajaxModal(MODAL_LG, "{{ route('ticketChannels.create') }}");
        });

        $body.on('click' + namespace, '#add-type', function () {
            $.ajaxModal(MODAL_LG, "{{ route('ticketTypes.create') }}");
        });

        $body.on('click' + namespace, '#save-ticket-form', function () {
            var $description = $('#description');
            if ($description.length > 0 && $description[0].children[0]) {
                var note = $description[0].children[0].innerHTML;
                $('#description-text').val(note);
                
                var mention_user_id = $description.find('span[data-id]').map(function(){
                    return $(this).attr('data-id');
                }).get();
                $('#mentionUserId').val(mention_user_id.join(','));
            }

            $.easyAjax({
                url: "{{ route('tickets.store') }}",
                container: '#save-ticket-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                file: true,
                buttonSelector: "#save-ticket-form",
                data: $('#save-ticket-data-form').serialize(),
                success: function (response) {
                    if (response.status == 'success') {
                        if (ticketDropzone.getQueuedFiles().length > 0) {
                            $('#replyID').val(response.replyID);
                            ticketDropzone.processQueue();
                        } else {
                            window.location.href = response.redirectUrl;
                        }
                    }
                }
            });
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

        $body.on('click' + namespace, '.toggle-other-details', function () {
            $(this).find('svg').toggleClass('fa-chevron-down fa-chevron-up');
            $('#other-details').toggleClass('d-none');
        });

        $body.on('click' + namespace, '#manage-groups', function() {
            $.ajaxModal(MODAL_LG, "{{ route('ticket-groups.create') }}");
        });

        $body.on('change' + namespace, '#ticket_group', function() {
            getAgents($(this).val());
        });

        // Initialize Everything
        if (typeof init === 'function') {
            init(RIGHT_MODAL || '#save-ticket-data-form');
        }

        $("#cc_users").selectpicker({
            actionsBox: true,
            selectAllText: "{{ __('modules.permission.selectAll') }}",
            deselectAllText: "{{ __('modules.permission.deselectAll') }}",
            multipleSeparator: " ",
            selectedTextFormat: "count > 8",
            countSelectedText: function(selected) { return selected + " {{ __('app.membersSelected') }} "; }
        });

        cacheOriginalContent('#cc_users');
        cacheOriginalContent('#ticket_agent_id');
        getAgents($('#ticket_group').val());

        window.addEventListener('turbo:before-cache', function cleanup() {
            $body.off(namespace);
            if (ticketDropzone) ticketDropzone.destroy();
            if (tagify) tagify.destroy();
            if (typeof destroy_editor === 'function') destroy_editor('#description');
            else if (typeof destory_editor === 'function') destory_editor('#description');
            window.removeEventListener('turbo:before-cache', cleanup);
        }, { once: true });
    })();
</script>
