
@php
    $addInvoicePermission = user()->permission('add_lead_proposals');
@endphp

<!-- ROW START -->
<div class="row">
    <div class="col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4">
        <!-- Add Task Export Buttons Start -->
        <div class="d-flex" id="table-actions">
            @if ($addInvoicePermission == 'all' || $addInvoicePermission == 'added')
                <x-forms.link-primary data-redirect-url="{{ url()->full() }}" :link="route('proposals.create').'?lead_id='.$lead->id"
                    class="mr-3 openRightModal" icon="plus">
                    @lang('modules.proposal.createProposal')
                </x-forms.link-primary>
            @endif

        </div>
        <!-- Add Task Export Buttons End -->
        <!-- Task Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}

        </div>
        <!-- Task Box End -->

    </div>

</div>
<!-- ROW END -->
@include('sections.datatable_js')

<script>
    (function() {
        var $body = $('body');
        var $table = $('#invoices-table');
        var namespace = '.leadsProposal';

        $table.off('preXhr.dt').on('preXhr.dt' + namespace, function(e, settings, data) {
            var leadId = "{{ $lead->id }}";
            data['leadId'] = leadId;
        });

        function showTable() {
            if (window.LaravelDataTables && window.LaravelDataTables["invoices-table"]) {
                window.LaravelDataTables["invoices-table"].draw(false);
            }
        }
        window.showTable = showTable;

        $body.off(namespace);

        $body.on('click' + namespace, '.delete-table-row', function() {
            var id = $(this).data('proposal-id');
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
                    var url = "{{ route('proposals.destroy', ':id') }}".replace(':id', id);
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

        $body.on('click' + namespace, '.sendButton', function() {
            var id = $(this).data('proposal-id');
            var url = "{{ route('proposals.send_proposal', ':id') }}".replace(':id', id);
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

        document.addEventListener("turbo:before-cache", function cleanup() {
            $body.off(namespace);
            $table.off('preXhr.dt' + namespace);
            delete window.showTable;
            document.removeEventListener("turbo:before-cache", cleanup);
        }, { once: true });
    })();
</script>
