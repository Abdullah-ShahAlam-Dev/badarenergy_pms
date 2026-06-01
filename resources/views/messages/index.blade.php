@extends('layouts.app')

<meta name="turbo-cache-control" content="no-cache">

<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">

@push('styles')
    <style>
        .message-action {
            visibility: hidden;
        }

        .message_wrapper .msg-content-right .chat-box .card:hover .message-action {
            visibility: visible;
        }
        #submitTexts {
            border-top: 1px solid;
        }
        .ql-editor {
            padding-left: 0px !important;
        }

        .ql-editor-disabled {
        border-radius: 6px;
        background-color: rgba(124, 0, 0, 0.2);
        transition-duration: 0.5s;
        }
        .ql-toolbar{
            display: none !important;
        }
        .ql-editor.ql-blank::before{
            font-size: 14px !important;
            font-style: inherit;
            color: #6c757d;
            left: 17px !important;
        }
    </style>
@endpush

@section('content')
    @php
    $allowCreateGroup = true;
    $messageSettings = message_setting();
    if ($messageSettings->allow_create_group) {
        $allowedUsers = $messageSettings->allow_create_group;
        if (is_string($allowedUsers)) {
            $allowedUsers = json_decode($allowedUsers, true);
        }
        if (is_array($allowedUsers) && count($allowedUsers) > 0) {
            $allowedUserIds = array_map('strval', $allowedUsers);
            $currentUser = auth()->user();
            if ($currentUser) {
                $currentUserRoles = $currentUser->roles->pluck('name')->toArray();
                if (!in_array((string)$currentUser->id, $allowedUserIds) && !in_array('admin', $currentUserRoles)) {
                    $allowCreateGroup = false;
                }
            }
        }
    }
    @endphp

    <!-- MESSAGE START -->
    <div class="message_wrapper bg-white border-top-0">
        <!-- MESSAGE HEADER START -->

        <!-- MESSAGE HEADER END -->
        <!-- MESSAGE CONTENT START -->
        <div class="w-100 d-lg-flex d-md-flex d-block">
            <!-- MESSAGE CONTENT LEFT START -->
            <div class="msg-content-left border-top-0 border-bottom-0">
                <div class="msg-header d-flex align-items-center">
                    <div class="msg-header-left d-flex justify-content-between">

                        <div class="flex-lg-grow-1">
                            <form class="mb-0">
                                <div class="input-group rounded py-1">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text border-0">
                                            <i class="fa fa-search f-12 text-lightest"></i>
                                        </span>
                                    </div>
                                    <input type="text" id="user-search" class="form-control border-0 f-14"
                                           placeholder="@lang('modules.messages.searchContact')">
                                </div>
                            </form>
                        </div>

                        <div class="pl-2 d-lg-none d-flex align-items-center">
                            @if ($allowCreateGroup)
                                <x-forms.button-secondary id="create-group-mbl" icon="users" class="mr-1">Group</x-forms.button-secondary>
                            @endif
                            <x-forms.button-primary id="new-chat-mbl" icon="plus">@lang('app.new')
                            </x-forms.button-primary>
                        </div>
                    </div>
                </div>
                <!-- This msgLeft id is for scroll plugin -->
                <div data-menu-vertical="1" data-menu-scroll="1" data-menu-dropdown-timeout="500" id="msgLeft"
                     class="nav nav-tabs border-bottom-0" role="tablist">
                    @include('messages.user_list')
                </div>

            </div>
            <!-- MESSAGE CONTENT LEFT END -->

            <!-- MESSAGE CONTENT RIGHT START -->
            <div class="msg-content-right" id="msgContentRight">
                <div class="msg-header d-none d-lg-flex align-items-center">
                    <div class="msg-header-right w-100 d-flex justify-content-between align-items-center">
                        <div class="msg-sender-name">
                            <p class="f-15 text-capitailize text-dark mb-0 f-w-500 message-user"></p>
                        </div>
                        <div class="d-flex align-items-center">
                            @if ($allowCreateGroup)
                                <x-forms.button-secondary id="create-group" icon="users" class="mr-2">Create Group</x-forms.button-secondary>
                            @endif
                            <x-forms.button-primary id="new-chat" icon="plus">@lang('modules.messages.startConversation')
                            </x-forms.button-primary>
                        </div>
                    </div>
                </div>

                <!-- MOBILE MESSAGE SENDER NAME START -->
                <div
                    class="msg-sender-name d-flex d-lg-none mbl-sender-name align-items-center justify-content-between">
                    <p class="f-15 text-capitailize text-dark mb-0 f-w-500 message-user"></p>
                    <i class="fa fa-long-arrow-alt-right f-16 text-dark" onclick="closeMessageTab()"></i>
                </div>
                <!-- MOBILE MESSAGE SENDER NAME END -->

                <!-- CHAT BOX START -->
                <div class="chat-box">
                    <!-- This chatBox id is for scroll plugin -->
                    <div data-menu-vertical="1" data-menu-scroll="1" data-menu-dropdown-timeout="500" id="chatBox"
                         class="tab-content" data-chat-for-user="">

                        <div id="tab1" class="tabcontent" style="display: block;">
                            <x-cards.no-record icon="comment-alt" :message="__('messages.selectConversation')"/>
                        </div><!-- TAB END -->

                    </div>

                </div>
                <!-- CHAT BOX END -->

                <!-- SEND MESSAGE START -->
                <x-form id="sendMessageForm" class="d-none mb-0">
                    <input type="hidden" name="user_id" id="current_user_id">
                    <input type="hidden" name="message_group_id" id="current_message_group_id">
                    <div class="row">
                        <div class="w-100 col-md-12">
                             <br>
                             <div id="submitTexts" class="form-control rounded-0 f-14 p-3 border-left-0 border-right-0 border-bottom-0" contentEditable=true data-text="@lang('messages.enterText')"></div>
                            <textarea name="message" id="message-text" class="d-none"></textarea>
                        </div>
                       <input type = "hidden" name = "mention_user_id" id = "mentionUserId" class ="mention_user_ids">
                       <div class="col-md-12">
                           <div class="w-100 justify-content-start attach-send bg-white">
                               <a class="f-15 f-w-500" href="javascript:;" id="add-file"><i
                                       class="fa fa-paperclip font-weight-bold mr-1"></i>@lang('modules.projects.uploadFile')
                               </a>
                           </div>
                       </div>
                        <div class="col-md-12 d-none file-container">
                           <x-forms.file-multiple class="mr-0 mr-lg-2 mr-md-2 ml-3"
                                                  :fieldLabel="__('app.menu.addFile')" fieldName="file"
                                                  fieldId="file-upload-dropzone"/>
                           <input type="hidden" name="message_id" id="messageId">
                           <input type="hidden" name="type" id="message">

                           {{-- These inputs fields are used for file attchment --}}
                           <input type="hidden" name="user_list" id="user_list">
                           <input type="hidden" name="message_list" id="message_list">
                           <input type="hidden" name="receiver_id" id="receiver_id">
                        </div>


                    </div>
                    <div class="col-md-12 border-top-grey p-0">
                        <div class="w-100 justify-content-start attach-send bg-white">
                            <x-forms.button-primary id="sendMessage" class="mr-1" icon="location-arrow">
                                @lang('modules.messages.send')
                            </x-forms.button-primary>
                        </div>

                    </div>
                </x-form>
                <!-- SEND MESSAGE END -->

            </div>
            <!-- MESSAGE CONTENT RIGHT START -->
        </div>
        <!-- MESSAGE CONTENT END -->
    </div>
    <!-- MESSAGE END -->
@endsection

@push('scripts')

    <script src="{{ asset('vendor/jquery/dropzone.min.js') }}"></script>

    <script>
        (function() {
            var $doc = $(document);
            var namespace = '.messagesIndex';
            var messageInterval;

            $doc.off(namespace);

            $(document).ready(function() {
                getUserMention();
                var atValues = @json($userData);
                quillMention(atValues, '#submitTexts');
            });

            var totalUnreadMessagesCount = parseInt("{{ $unreadMessagesCount }}");

            @if (session('message_user_id'))
            let message_user_id = {{ session('message_user_id') }};

            setTimeout(() => {
                $('a[data-user-id="' + message_user_id + '"]').click();
            }, 500);
            @endif

            // change query parameter from url
            history.replaceState(null, null, "{{route('messages.index')}}");

            /* Upload images */
            Dropzone.autoDiscover = false;

            //Dropzone class
            taskDropzone = new Dropzone("#file-upload-dropzone", {
                dictDefaultMessage: "{{ __('app.dragDrop') }}",
                url: "{{ route('message-file.store') }}",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                paramName: "file",
                maxFilesize: DROPZONE_MAX_FILESIZE,
                maxFiles: DROPZONE_MAX_FILES,
                autoProcessQueue: false,
                uploadMultiple: true,
                addRemoveLinks: true,
                parallelUploads: DROPZONE_MAX_FILES,
                acceptedFiles: DROPZONE_FILE_ALLOW,
                init: function () {
                    taskDropzone = this;
                    this.on("success", function (file, response) {
                        $('#chatBox').html(response.message_list);
                        showContent();
                        $.easyUnblockUI();
                        taskDropzone.removeAllFiles(true);
                    })
                }
            });

            taskDropzone.on('sending', function (file, xhr, formData) {
                var ids = $('#messageId').val();
                formData.append('message_id', ids);
                formData.append('type', 'message');
                formData.append('receiver_id', $('#receiver_id').val());
                $.easyBlockUI();
            });

            taskDropzone.on('uploadprogress', function () {
                $.easyBlockUI();
            });
            taskDropzone.on('removedfile', function () {
                var grp = $('div#file-upload-dropzone').closest(".form-group");
                var label = $('div#file-upload-box').siblings("label");
                $(grp).removeClass("has-error");
                $(label).removeClass("is-invalid");
            });
            taskDropzone.on('error', function (file, message) {
                taskDropzone.removeFile(file);
                var grp = $('div#file-upload-dropzone').closest(".form-group");
                var label = $('div#file-upload-box').siblings("label");
                $(grp).find(".help-block").remove();
                var helpBlockContainer = $(grp);

                if (helpBlockContainer.length == 0) {
                    helpBlockContainer = $(grp);
                }

                helpBlockContainer.append('<div class="help-block invalid-feedback">' + message + '</div>');
                $(grp).addClass("has-error");
                $(label).addClass("is-invalid");
            });

            // Submitting message
            $doc.on('click' + namespace, '#sendMessage', function (e) {
                var note = document.getElementById('submitTexts').children[0].innerHTML;
                document.getElementById('message-text').value = note;
                var mention_user_id = $('#submitTexts span[data-id]').map(function(){
                                    return $(this).attr('data-id')
                                }).get();
                $('#mentionUserId').val(mention_user_id.join(','));
                //getting values by input fields
                var url = "{{ route('messages.store') }}";

                $.easyAjax({
                    url: url,
                    container: '#sendMessageForm',
                    type: "POST",
                    disableButton: true,
                    blockUI: true,
                    buttonSelector: "#sendMessage",
                    data: $('#sendMessageForm').serialize(),
                    success: function (response) {

                        $('#user_list').val(response.user_list);
                        $('#message_list').val(response.message_list);
                        
                        if (response.message_group_id) {
                            $('#receiver_id').val('');
                            $('#current_message_group_id').val(response.message_group_id);
                            $('#current_user_id').val('');
                        } else {
                            $('#receiver_id').val(response.receiver_id);
                            $('#current_user_id').val(response.receiver_id);
                            $('#current_message_group_id').val('');
                        }

                        // Reload left user-list
                        fetchUserList();

                        if (taskDropzone.getQueuedFiles().length > 0) {
                            messageId = response.message_id;
                            $('#messageId').val(response.message_id);
                            taskDropzone.processQueue();
                        } else {
                            showContent();
                        }
                    }
                });

                return false;
            });

            function showContent() {
                $('.ql-editor p').html('');
                $('#sendMessageForm').removeClass('d-none');
                scrollChat();
                $('#msgContentRight').addClass('d-block');
                $('.file-container').addClass('d-none');
                taskDropzone.removeAllFiles(true);

                fetchUserMessages();
            }

            $doc.on('click' + namespace, '#new-chat, #new-chat-mbl', function () {
                const url = "{{ route('messages.create') }}";
                $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
                $.ajaxModal(MODAL_LG, url);
            });

            $doc.on('click' + namespace, '#create-group, #create-group-mbl', function () {
                const url = "{{ route('message-groups.create') }}";
                $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
                $.ajaxModal(MODAL_LG, url);
            });

            $doc.on('click' + namespace, '.manage-group-members', function (e) {
                e.stopPropagation();
                var groupId = $(this).data('group-id');
                var url = "{{ route('message-groups.edit', ':id') }}".replace(':id', groupId);
                $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
                $.ajaxModal(MODAL_LG, url);
            });

            $doc.on('click' + namespace, '.delete-message-group', function (e) {
                e.stopPropagation();
                var groupId = $(this).data('group-id');
                
                Swal.fire({
                    title: "@lang('messages.sweetAlertTitle')",
                    text: "You are about to delete this group. All messages and files in the group will be permanently removed!",
                    icon: 'warning',
                    showCancelButton: true,
                    focusConfirm: false,
                    confirmButtonText: "@lang('messages.confirmDelete')",
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
                        var url = "{{ route('message-groups.destroy', ':id') }}";
                        url = url.replace(':id', groupId);

                        var token = "{{ csrf_token() }}";

                        $.easyAjax({
                            type: 'POST',
                            url: url,
                            data: {
                                '_token': token,
                                '_method': 'DELETE'
                            },
                            success: function (response) {
                                if (response.status == "success") {
                                    // Reload conversation list
                                    fetchUserList();
                                    
                                    // If currently opened chat was this deleted group, reset the chat pane view
                                    var activeChatForUser = $('#chatBox').attr("data-chat-for-user");
                                    if (activeChatForUser === 'group-' + groupId) {
                                        resetChatBoxView();
                                    }
                                }
                            }
                        });
                    }
                });
            });

            $doc.on('keyup' + namespace, '#user-search', function () {
                var url = "{{ route('messages.index') }}";
                var term = $(this).val();

                $.easyAjax({
                    url: url,
                    blockUI: true,
                    container: "#msgLeft",
                    data: {
                        term: term
                    },
                    success: function (response) {
                        if (response.status == "success") {
                            $('#msgLeft').html(response.userList);
                            $('#current_user_id').val('');
                            $('#current_message_group_id').val('');
                            $('#chatBox').html('');
                            $('#sendMessageForm').addClass('d-none');
                        }
                    }
                });
            });

            $doc.on('click' + namespace, '#add-file', function () {
                $('.file-container').toggleClass('d-none');
                window.scrollBy(0, 200);
            });

            $doc.on('click' + namespace, '.show-user-messages', function () {
                var id = $(this).data('user-id');
                var type = $(this).data('chat-type'); // 'group' or 'user'
                var userName = $(this).data('name');
                var isUnreadMessage = $(this).hasClass('unread-message');
                $(this).removeData('unread-message-count')
                var unreadMessageCount = $(this).data('unread-message-count');

                if (isUnreadMessage) {
                    $(this).find('.card-text').removeClass('text-dark');
                    $(this).find('.card-text').removeClass('font-weight-bold');
                    $(this).find('.unread-count').remove();
                }

                $('.message-user').html(userName);
                
                if (type === 'group') {
                    $('#current_message_group_id').val(id);
                    $('#current_user_id').val('');
                } else {
                    $('#current_user_id').val(id);
                    $('#current_message_group_id').val('');
                }
                
                $('.show-user-messages').removeClass('active');
                $(this).addClass('active');

                var url = "{{ route('messages.show', ':id') }}";
                url = url.replace(':id', id);

                $.easyAjax({
                    url: url,
                    blockUI: true,
                    container: "#chatBox",
                    data: {'unreadMessageCount': unreadMessageCount, 'type': type},
                    success: function (response) {
                        if (response.status == "success") {
                            $('#chatBox').html(response.html);
                            
                            if (type === 'group') {
                                $('#group-no-' + response.groupId + ' > a').attr("data-unread-message-count", 0);
                                $('#chatBox').attr("data-chat-for-user", 'group-' + response.groupId);
                            } else {
                                $('#user-no-' + response.id + ' > a').attr("data-unread-message-count", 0);
                                $('#chatBox').attr("data-chat-for-user", response.id);
                            }

                            $('#sendMessageForm').removeClass('d-none');
                            scrollChat();
                            $('#msgContentRight').addClass('d-block');

                            if (totalUnreadMessagesCount > 0 && isUnreadMessage && response.unreadMessages == 0) {
                                var remainingUnreadMessages = parseInt(totalUnreadMessagesCount) - parseInt(unreadMessageCount);
                                if (remainingUnreadMessages > 0) {
                                    $(body).find('.message-menu .menu-item-count').html(remainingUnreadMessages);
                                } else {
                                    $(body).find('.message-menu .menu-item-count').html('');
                                }

                                totalUnreadMessagesCount = remainingUnreadMessages;
                            }
                        }
                    }
                });

            });

            $doc.on('keypress' + namespace, '#submitTexts', function (e) {
                var key = e.which;
                if (key == 13 && !e.shiftKey) // the enter key code
                {
                    e.preventDefault();
                    $('#sendMessage').click();
                    return false;
                }
            });

            function scrollChat(params) {
                $('#chatBox').stop().animate({
                    scrollTop: $("#chatBox")[0].scrollHeight
                }, 800);
            }

            $doc.on('click' + namespace, '.delete-message', function () {
                var id = $(this).data('row-id');
                Swal.fire({
                    title: "@lang('messages.sweetAlertTitle')",
                    text: "@lang('messages.recoverRecord')",
                    icon: 'warning',
                    showCancelButton: true,
                    focusConfirm: false,
                    confirmButtonText: "@lang('messages.confirmDelete')",
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
                        var url = "{{ route('messages.destroy', ':id') }}";
                        url = url.replace(':id', id);

                        var token = "{{ csrf_token() }}";

                        $.easyAjax({
                            type: 'POST',
                            url: url,
                            data: {
                                '_token': token,
                                '_method': 'DELETE'
                            },
                            success: function (response) {
                                if (response.status == "success") {
                                    $('#message-' + id).remove();

                                    // Reload left user-list
                                    fetchUserList();

                                    if (response.chat_details.length == 0) {
                                        resetChatBoxView();
                                    }

                                }
                            }
                        });
                    }
                });
            });

            function resetChatBoxView() {
                $('#chatBox').html(`
                <div id="tab1" class="tabcontent" style="display: block;">
                    <x-cards.no-record icon="comment-alt" :message="__('messages.selectConversation')" />
                </div>
                `);

                $('#sendMessageForm').addClass('d-none');

                $('.message-user').html('');

            }

            function fetchUserList() {
                var url = "{{ route('messages.fetch_user_list') }}";

                $.easyAjax({
                    url: url,
                    type: "GET",
                    success: function (response) {
                        $('#msgLeft').html(response.user_list);

                        let receiverId = $('#chatBox').data('chat-for-user');
                        if (receiverId && receiverId.toString().startsWith('group-')) {
                            let groupId = receiverId.replace('group-', '');
                            $('#group-no-' + groupId + ' a').addClass('active');
                        } else if (receiverId) {
                            $('#user-no-' + receiverId + ' a').addClass('active');
                        }
                    }
                });
            }
            window.fetchUserList = fetchUserList;

            function fetchUserMessages() {
                var currentUserId = $('#current_user_id').val();
                var currentGroupId = $('#current_message_group_id').val();

                if (currentUserId === '' && currentGroupId === '') {
                    return false;
                }

                var id = currentUserId !== '' ? currentUserId : currentGroupId;
                var type = currentUserId !== '' ? 'user' : 'group';

                var url = "{{ route('messages.fetch_messages', ':id') }}";
                url = url.replace(':id', id);
                var token = "{{ csrf_token() }}";

                $.easyAjax({
                    url: url,
                    container: '#sendMessageForm',
                    type: "POST",
                    data: {
                        '_token': token,
                        'type': type
                    },
                    success: function (response) {
                        $('#chatBox').html(response.message_list);
                        scrollChat();
                        $('#msgContentRight').addClass('d-block');
                    }
                });
            }
            window.fetchUserMessages = fetchUserMessages;

            function getUserMention(){
                $('.user_list_box').each(function(i, obj) {
                    var content = $(obj).find('.message-mention').html();
                    var name = $(obj).find('.message-mention p a').data('name');
                    var replacement = '<div class="card-text f-11 text-lightest d-flex justify-content-between message-mention">@' + name + '</div>';
                    if(content !== undefined && replacement !== undefined && name !== undefined){
                        $(obj).find('.message-mention').replaceWith(replacement);

                    }

                });

            }

            @if (isset($client))
            let clientId = '{{ $client->id }}';
            $("a[data-user-id='" + clientId + "']").click();
            @endif

            // Check for ?group=X or ?user=Y in URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const groupParam = urlParams.get('group');
            const userParam = urlParams.get('user');

            if (groupParam) {
                setTimeout(() => {
                    $('a[data-user-id="' + groupParam + '"][data-chat-type="group"]').click();
                }, 500);
            } else if (userParam) {
                setTimeout(() => {
                    $('a[data-user-id="' + userParam + '"][data-chat-type="user"]').click();
                }, 500);
            }

            var channel;
            if ((pusher_setting.status === 1 && pusher_setting.messages === 1) || (pusher_setting.status == "1" && pusher_setting.messages == "1")) {
                channel = pusher.subscribe('messages-channel');
                channel.bind('messages.received', function (data) {
                    fetchUserMessages()

                    if (message_setting.send_sound_notification == 1) {
                        newMessageNotificationPlay();
                    }
                });

                $doc.on('keydown' + namespace, '#submitTexts', function () {
                    var currentUserId = $('#current_user_id').val();
                    if (currentUserId !== '') {
                        let channel2 = Echo.private('chat');
                        setTimeout(() => {
                            channel2.whisper('typing', {
                                from: "{{ user()->id }}",
                                to: currentUserId,
                                typing: true
                            })
                        }, 300)
                    }
                });

                Echo.private('chat').listenForWhisper('typing', (e) => {
                    var currentUserId = $('#current_user_id').val();

                    if (e.to == Laravel.user.id && e.from == currentUserId) {
                        e.typing ? $('#chatBox').find('.typing').removeClass('invisible').addClass('visible') : $('#chatBox').find('.typing').removeClass('visible').addClass('invisible')
                        // remove is typing indicator after 0.9s
                        setTimeout(function () {
                            e.typing = false;
                            $('#chatBox').find('.typing').removeClass('visible').addClass('invisible');
                        }, 1500);
                    }
                });
            } else {
                messageInterval = window.setInterval(function () {
                    fetchUserMessages()
                }, 10000); // Fetch messages every 10 seconds
            }

            $doc.on('click' + namespace, '.delete-file', function () {
                var id = $(this).data('row-id');
                var messageFile = $(this);
                Swal.fire({
                    title: "@lang('messages.sweetAlertTitle')",
                    text: "@lang('messages.recoverRecord')",
                    icon: 'warning',
                    showCancelButton: true,
                    focusConfirm: false,
                    confirmButtonText: "@lang('messages.confirmDelete')",
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
                        var url = "{{ route('message-file.destroy', ':id') }}";
                        url = url.replace(':id', id);

                        var token = "{{ csrf_token() }}";

                        $.easyAjax({
                            type: 'POST',
                            url: url,
                            data: {
                                '_token': token,
                                '_method': 'DELETE'
                            },
                            success: function (response) {
                                if (response.status == "success") {
                                    messageFile.closest('.card').remove();
                                }
                            }
                        });
                    }
                });
            });

            // Force plain text paste for URLs/text in case Clipboard/Quill matcher crashes or strips it
            $doc.on('paste' + namespace, '#submitTexts .ql-editor, #message-new .ql-editor', function (e) {
                var clipboardData = e.originalEvent.clipboardData || window.clipboardData;
                var pastedData = clipboardData.getData('text');
                
                if (pastedData) {
                    e.preventDefault();
                    var qlEditor = this;
                    var container = qlEditor.closest('.ql-container') || qlEditor.parentNode;
                    if (container) {
                        var quill = Quill.find(container);
                        if (quill) {
                            var range = quill.getSelection();
                            if (range) {
                                quill.insertText(range.index, pastedData);
                                quill.setSelection(range.index + pastedData.length);
                            } else {
                                quill.insertText(quill.getLength() - 1, pastedData);
                            }
                        }
                    }
                    return false;
                }
            });

            // Turbo Lifecycle Cleanup
            document.addEventListener('turbo:before-cache', function cleanup() {
                if (messageInterval) {
                    clearInterval(messageInterval);
                }
                if (window.pusher && typeof window.pusher.unsubscribe === 'function' && channel) {
                    window.pusher.unsubscribe('messages-channel');
                }
                if (window.Echo && typeof window.Echo.leave === 'function') {
                    window.Echo.leave('chat');
                }

                $doc.off(namespace);
                if (typeof taskDropzone !== 'undefined' && taskDropzone) {
                    taskDropzone.destroy();
                }

                window.fetchUserList = undefined;
                window.fetchUserMessages = undefined;

                document.removeEventListener('turbo:before-cache', cleanup);
            }, { once: true });
        })();
    </script>
@endpush
