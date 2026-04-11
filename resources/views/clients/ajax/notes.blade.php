@php
$addClientNotePermission = user()->permission('add_client_note');
@endphp

<!-- ROW START -->
<div class="row pb-5">
    <div class="col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4">
        <!-- Add Task Export Buttons Start -->
        <div class="d-flex justify-content-between action-bar">
            <div id="table-actions" class="d-flex align-items-center">
                @if ($addClientNotePermission == 'all' || $addClientNotePermission == 'added' || $addClientNotePermission == 'both')
                    <x-forms.link-primary :link="route('client-notes.create').'?client='.$client->id"
                        class="mr-3 openRightModal" icon="plus">
                        @lang('modules.client.createNote')
                    </x-forms.link-primary>
                @endif
            </div>

            <x-datatable.actions>
                <div class="select-status mr-3 pl-3">
                    <select name="action_type" class="form-control select-picker" id="quick-action-type" disabled>
                        <option value="">@lang('app.selectAction')</option>
                        <option value="delete">@lang('app.delete')</option>
                    </select>
                </div>
            </x-datatable.actions>


        </div>
        <!-- Add Task Export Buttons End -->
        <!-- Task Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}

        </div>
        <!-- Task Box End -->
    </div>
</div>

@include('sections.datatable_js')

<script>
    (function() {
        var $body = $('body');
        var $table = $('#client-notes-table');

        $table.off('preXhr.dt').on('preXhr.dt', function(e, settings, data) {
            var clientID = "{{ $client->id }}";
            data['clientID'] = clientID;
        });

        var showTable = function() {
            if (window.LaravelDataTables["client-notes-table"]) {
                window.LaravelDataTables["client-notes-table"].draw(false);
            }
        };

        $body.off('.clientsNotes');

        $body.on('change.clientsNotes', '#quick-action-type', function() {
            var actionValue = $(this).val();
            if (actionValue != '') {
                $('#quick-action-apply').removeAttr('disabled');
                if (actionValue == 'change-status') {
                    $('.quick-action-field').addClass('d-none');
                    $('#change-status-action').removeClass('d-none');
                } else {
                    $('.quick-action-field').addClass('d-none');
                }
            } else {
                $('#quick-action-apply').attr('disabled', true);
                $('.quick-action-field').addClass('d-none');
            }
        });

        var applyQuickAction = function() {
            var rowdIds = $("#client-notes-table input:checkbox:checked").map(function() {
                return $(this).val();
            }).get();

            var url = "{{ route('client-notes.apply_quick_action') }}?row_ids=" + rowdIds;

            $.easyAjax({
                url: url,
                container: '#quick-action-form',
                type: "POST",
                disableButton: true,
                buttonSelector: "#quick-action-apply",
                data: $('#quick-action-form').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        showTable();
                        if (typeof resetActionButtons === 'function') resetActionButtons();
                        if (typeof deSelectAll === 'function') deSelectAll();
                    }
                }
            });
        };

        $body.on('click.clientsNotes', '#quick-action-apply', function() {
            var actionValue = $('#quick-action-type').val();
            if (actionValue == 'delete') {
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
                    if (result.isConfirmed) { applyQuickAction(); }
                });
            } else {
                applyQuickAction();
            }
        });

        $body.on('click.clientsNotes', '.delete-table-row', function() {
            var id = $(this).data('user-id');
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
                    var url = "{{ route('client-notes.destroy', ':id') }}".replace(':id', id);
                    var token = "{{ csrf_token() }}";
                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: { '_token': token, '_method': 'DELETE' },
                        success: function(response) {
                            if (response.status == "success") { showTable(); }
                        }
                    });
                }
            });
        });

        $body.on('click.clientsNotes', '.ask-for-password', function() {
            var clientNoteId = $(this).data('client-note-id');
            var url = "{{ route('client_notes.ask_for_password', ':id') }}".replace(':id', clientNoteId);
            $.ajaxModal(MODAL_LG, url);
        });

        // show note detail in right modal
        $body.on('click.clientsNotes', '.get-note-detail', function() {
            var id = $(this).data('note-id');
            if (typeof openTaskDetail === 'function') openTaskDetail();

            var url = "{{ route('client-notes.show_verified', ':id') }}".replace(':id', id);
            var token = "{{ csrf_token() }}";

            $.easyAjax({
                url: url,
                blockUI: true,
                type: "POST",
                container: RIGHT_MODAL,
                historyPush: true,
                data: { '_token': token },
                success: function(response) {
                    if (response.status == "success") {
                        $(RIGHT_MODAL_CONTENT).html(response.html);
                        $(RIGHT_MODAL_TITLE).html(response.title);
                    }
                },
                error: function(request, status, error) {
                    var msg = "Something Went Wrong";
                    if (request.status == 403) msg = "403 | Permission Denied";
                    else if (request.status == 404) msg = "404 | Not Found";
                    else if (request.status == 500) msg = "500 | Something Went Wrong";
                    $(RIGHT_MODAL_CONTENT).html('<div class="align-content-between d-flex justify-content-center mt-105 f-21">' + msg + '</div>');
                }
            });
        });

        document.addEventListener("turbo:before-cache", function() {
            $body.off('.clientsNotes');
            $table.off('preXhr.dt');
        }, { once: true });
    })();
</script>
