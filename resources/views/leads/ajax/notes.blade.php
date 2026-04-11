@php
$addLeadNotePermission = user()->permission('add_lead_note');
@endphp

<!-- ROW START -->
<div class="row pb-5">
    <div class="col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4">
        <!-- Add Task Export Buttons Start -->
        <div class="d-flex justify-content-between action-bar">
            <div id="table-actions" class="d-flex align-items-center">
                @if ($addLeadNotePermission == 'all' || $addLeadNotePermission == 'added' || $addLeadNotePermission == 'both')
                    <x-forms.link-primary :link="route('lead-notes.create').'?lead='.$lead->id"
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
        var $table = $('#lead-notes-table');

        $table.on('preXhr.dt', function(e, settings, data) {
            var leadID = "{{ $lead->id }}";
            data['leadID'] = leadID;
        });

        var showTable = function() {
            if (window.LaravelDataTables["lead-notes-table"]) {
                window.LaravelDataTables["lead-notes-table"].draw(false);
            }
        };

        $body.off('.leadsNotes');

        $body.on('change.leadsNotes', '#quick-action-type', function() {
            var actionValue = $(this).val();
            if (actionValue != '') {
                $('#quick-action-apply').removeAttr('disabled');
                $('.quick-action-field').addClass('d-none');
                if (actionValue == 'change-status') {
                    $('#change-status-action').removeClass('d-none');
                }
            } else {
                $('#quick-action-apply').attr('disabled', true);
                $('.quick-action-field').addClass('d-none');
            }
        });

        var applyQuickAction = function() {
            var rowdIds = $("#lead-notes-table input:checkbox:checked").map(function() {
                return $(this).val();
            }).get();

            var url = "{{ route('lead-notes.apply_quick_action') }}?row_ids=" + rowdIds;

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

        $body.on('click.leadsNotes', '#quick-action-apply', function() {
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

        $body.on('click.leadsNotes', '.delete-table-row-lead', function() {
            var id = $(this).data('id');
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
                    var url = "{{ route('lead-notes.destroy', ':id') }}".replace(':id', id);
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

        $body.on('click.leadsNotes', '.ask-for-password', function() {
            var leadNoteId = $(this).data('lead-note-id');
            var url = "{{ route('lead_notes.ask_for_password', ':id') }}".replace(':id', leadNoteId);
            $.ajaxModal(MODAL_LG, url);
        });

        $body.on('click.leadsNotes', '.get-note-detail', function() {
            var id = $(this).data('note-id');
            openTaskDetail();
            var url = "{{ route('lead-notes.show', ':id') }}".replace(':id', id);

            $.easyAjax({
                url: url, blockUI: true, container: RIGHT_MODAL, historyPush: true,
                success: function(response) {
                    if (response.status == "success") {
                        $(RIGHT_MODAL_CONTENT).html(response.html);
                        $(RIGHT_MODAL_TITLE).html(response.title);
                    }
                },
                error: function(request) {
                    var msg = "Something Went Wrong";
                    if (request.status == 403) msg = "403 | Permission Denied";
                    else if (request.status == 404) msg = "404 | Not Found";
                    else if (request.status == 500) msg = "500 | Something Went Wrong";
                    $(RIGHT_MODAL_CONTENT).html('<div class="align-content-between d-flex justify-content-center mt-105 f-21">' + msg + '</div>');
                }
            });
        });

        document.addEventListener("turbo:before-cache", function() {
            $body.off('.leadsNotes');
            $table.off('.leadsNotes');
        }, { once: true });
    })();
</script>
