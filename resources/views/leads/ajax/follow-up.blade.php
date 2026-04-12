@php
$addLeadFollowUpPermission = user()->permission('add_lead_follow_up');
$viewLeadFollowUpPermission = user()->permission('view_lead_follow_up');
$editLeadFollowUpPermission = user()->permission('edit_lead_follow_up');
$deleteLeadFollowUpPermission = user()->permission('delete_lead_follow_up');
@endphp

<!-- ROW START -->
<div class="row">
    <!--  USER CARDS START -->
    <div class="col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4">
       <div class="d-flex" id="table-actions">

            @if (($addLeadFollowUpPermission == 'all' || $addLeadFollowUpPermission == 'added') && $lead->next_follow_up == 'yes')
         <x-forms.button-primary icon="plus" id="add-lead-followup" class="mr-3">
                @lang('modules.followup.newFollowUp')
            </x-forms.button-primary>
            @endif
        </div>
        @if ($viewLeadFollowUpPermission == 'all' || $viewLeadFollowUpPermission == 'added')
            <div class="d-flex flex-column w-tables rounded mt-3 bg-white">
                {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
            </div>
        @endif

    </div>
    <!--  USER CARDS END -->
</div>
<!-- ROW END -->
@include('sections.datatable_js')
<script>
    (function() {
        var $body = $('body');
        var $table = $('#leadfollowup-table');
        var namespace = '.leadsFollowUp';

        $table.off('preXhr.dt').on('preXhr.dt' + namespace, function(e, settings, data) {
            var leadId = "{{ $lead->id }}";
            data['leadId'] = leadId;
        });

        function showTable() {
            if (window.LaravelDataTables && window.LaravelDataTables["leadfollowup-table"]) {
                window.LaravelDataTables["leadfollowup-table"].draw(false);
            }
        }
        window.showTable = showTable;

        $body.off(namespace);

        $body.on('click' + namespace, '#add-lead-followup', function() {
            var url = "{{ route('leads.follow_up', $leadId) }}";
            $.ajaxModal(MODAL_LG, url);
        });

        $body.on('click' + namespace, '.edit-table-row-lead', function() {
            var id = $(this).data('followup-id');
            var url = "{{ route('leads.follow_up_edit', ':id') }}".replace(':id', id);
            $.ajaxModal(MODAL_LG, url);
        });

        $body.on('click' + namespace, '.delete-table-row-lead', function() {
            var id = $(this).data('followup-id');
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
                    var url = "{{ route('leads.follow_up_delete', ':id') }}".replace(':id', id);
                    var token = "{{ csrf_token() }}";
                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: { '_token': token },
                        success: function(response) {
                            if (response.status == "success") { showTable(); }
                        }
                    });
                }
            });
        });

        $body.on('change' + namespace, '.status', function() {
            var status = $(this).val();
            var followUpId = $(this).data('followup-id');
            var url = "{{ route('leads.change_follow_up_status') }}";
            var token = "{{ csrf_token() }}";

            $.easyAjax({
                url: url, type: 'POST', blockUI: true,
                data: { '_token': token, id: followUpId, status: status, sortBy: 'id' }
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
