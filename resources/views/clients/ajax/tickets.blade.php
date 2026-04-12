@php
$addTicketPermission = user()->permission('add_tickets');
@endphp

 <!-- Add Task Export Buttons Start -->
 <div class="d-flex justify-content-between action-bar">
    <div id="table-actions" class="flex-grow-1 align-items-center mt-3">
        @if ($addTicketPermission == 'all' || $addTicketPermission == 'added')
            <x-forms.link-primary :link="route('tickets.create').'?default_client='.$client->id" class="mr-3 openRightModal float-left"
                icon="plus" data-redirect-url="{{ route('clients.show', $client->id) . '?tab=tickets' }}">
                @lang('modules.tickets.addTicket')
            </x-forms.link-primary>
        @endif

        @if (in_array('admin', user_roles()))
            <x-forms.button-secondary icon="pencil-alt" class="mr-3 float-left" id="add-ticket">
                @lang('modules.ticketForm')
            </x-forms.button-secondary>
        @endif

    </div>

    @if (!in_array('client', user_roles()))
        <x-datatable.actions>
            <div class="select-status mr-3 pl-3">
                <select name="action_type" class="form-control select-picker" id="quick-action-type" disabled>
                    <option value="">@lang('app.selectAction')</option>
                    <option value="change-status">@lang('modules.tasks.changeStatus')</option>
                    <option value="delete">@lang('app.delete')</option>
                </select>
            </div>
            <div class="select-status mr-3 d-none quick-action-field" id="change-status-action">
                <select name="status" class="form-control select-picker">
                    <option value="open">@lang('app.open')</option>
                    <option value="pending">@lang('app.pending')</option>
                    <option value="resolved">@lang('app.resolved')</option>
                    <option value="closed">@lang('app.closed')</option>
                </select>
            </div>
        </x-datatable.actions>
    @endif

</div>

<!-- Add Task Export Buttons End -->
<!-- Task Box Start -->
<div class="d-flex flex-column w-tables rounded mt-3 bg-white">

    {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}

</div>
<!-- Task Box End -->

@include('sections.datatable_js')

<script>
    (function() {
        var $body = $('body');
        var namespace = '.clientsTickets';
        var $table = $('#ticket-table');
        var ticketFilterStatus = "{{ request('ticketStatus') }}";

        $table.off('preXhr.dt' + namespace).on('preXhr.dt' + namespace, function(e, settings, data) {
            var agentId = $('#agent_id').val() || 0;
            var clientID = "{{ $client->id }}";
            data['agentId'] = agentId;
            data['client_id'] = clientID;
            data['ticketStatus'] = ticketFilterStatus;
        });

        var showTable = function() {
            if (window.LaravelDataTables["ticket-table"]) {
                window.LaravelDataTables["ticket-table"].draw(false);
            }
        };

        $body.off('.clientsTickets');

        $body.on('change.clientsTickets', '#quick-action-type', function() {
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
            var rowdIds = $("#ticket-table input:checkbox:checked").map(function() {
                return $(this).val();
            }).get();

            var url = "{{ route('tickets.apply_quick_action') }}?row_ids=" + rowdIds;

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

        $body.on('click.clientsTickets', '#quick-action-apply', function() {
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

        $body.on('click.clientsTickets', '.delete-table-row', function() {
            var id = $(this).data('ticket-id');
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
                    var url = "{{ route('tickets.destroy', ':id') }}".replace(':id', id);
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

        $body.on('click.clientsTickets', '#add-ticket', function() {
            window.location.href = "{{ route('ticket-form.index') }}";
        });

        var refreshCount = function() {
            var agentId = $('#agent_id').val() || 0;
            var status = $('#ticket-status').val() || 0;
            var priority = $('#priority').val() || 0;
            var channelId = $('#channel_id').val() || 0;
            var typeId = $('#type_id').val() || 0;

            var url = "{{ route('tickets.refresh_count') }}";
            $.easyAjax({
                type: 'POST',
                url: url,
                data: {
                    'agentId': agentId,
                    'ticketStatus': status,
                    'priority': priority,
                    'channelId': channelId,
                    'typeId': typeId,
                    '_token': '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#totalTickets').html(response.totalTickets);
                    $('#closedTickets').html(response.closedTickets);
                    $('#openTickets').html(response.openTickets);
                    $('#pendingTickets').html(response.pendingTickets);
                    $('#resolvedTickets').html(response.resolvedTickets);
                }
            });
        };

        refreshCount();

        document.addEventListener("turbo:before-cache", function cleanup() {
            $body.off(namespace);
            $table.off('preXhr.dt' + namespace);
            document.removeEventListener("turbo:before-cache", cleanup);
        }, { once: true });
    })();
</script>
