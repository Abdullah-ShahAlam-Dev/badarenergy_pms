@php
$addInvoicesPermission = user()->permission('add_invoices');
@endphp

<!-- ROW START -->
<div class="row pb-5">
    <div class="col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4">
        <!-- Add Task Export Buttons Start -->
        <div class="d-flex" id="table-actions">
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
        var $table = $('#invoices-table');

        $table.off('preXhr.dt').on('preXhr.dt', function(e, settings, data) {
            var clientID = "{{ $client->id }}";
            data['clientID'] = clientID;
        });

        var showTable = function() {
            if (window.LaravelDataTables["invoices-table"]) {
                window.LaravelDataTables["invoices-table"].draw(false);
            }
        };

        $body.off('.clientsCreditNotes');

        $body.on('change.clientsCreditNotes', '#quick-action-type', function() {
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
            var rowdIds = $("#invoices-table input:checkbox:checked").map(function() {
                return $(this).val();
            }).get();

            var url = "{{ route('invoices.apply_quick_action') }}?row_ids=" + rowdIds;

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
                    }
                }
            });
        };

        $body.on('click.clientsCreditNotes', '#quick-action-apply', function() {
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

        $body.on('click.clientsCreditNotes', '.delete-table-row', function() {
            var id = $(this).data('credit-notes-id');
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
                    var url = "{{ route('creditnotes.destroy', ':id') }}".replace(':id', id);
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

        $body.on('click.clientsCreditNotes', '.sendButton', function() {
            var id = $(this).data('invoice-id');
            var url = "{{ route('invoices.send_invoice', ':id') }}".replace(':id', id);
            var token = "{{ csrf_token() }}";
            $.easyAjax({
                type: 'POST',
                url: url,
                container: '#invoices-table',
                blockUI: true,
                data: { '_token': token },
                success: function(response) {
                    if (response.status == "success") { showTable(); }
                }
            });
        });

        $body.on('click.clientsCreditNotes', '.reminderButton', function() {
            var id = $(this).data('invoice-id');
            var url = "{{ route('invoices.payment_reminder', ':id') }}".replace(':id', id);
            $.easyAjax({
                type: 'GET',
                container: '#invoices-table',
                blockUI: true,
                url: url,
                success: function(response) {
                    if (response.status == "success") { showTable(); }
                }
            });
        });

        $body.on('click.clientsCreditNotes', '.credit-notes-upload', function() {
            var creditNoteId = $(this).data('credit-notes-id');
            var url = "{{ route('creditnotes.file_upload') }}?credit_note=" + creditNoteId;
            $.ajaxModal(MODAL_LG, url);
        });

        document.addEventListener("turbo:before-cache", function() {
            $body.off('.clientsCreditNotes');
            $table.off('preXhr.dt');
        }, { once: true });
    })();
</script>
