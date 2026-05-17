<div class="row">
    {{-- Add Mapping Form --}}
    <div class="col-xl-4 col-lg-5 mb-4">
        <x-form id="save-mapping-form" method="POST">
            <div class="bg-white rounded p-20 b-shadow-4">
                <h4 class="mb-4 f-21 font-weight-normal text-capitalize border-bottom-grey pb-3">
                    @lang('workorder::modules.workOrder.addApprovalMapping')
                </h4>

                <div class="row">
                    <div class="col-md-12">
                        <x-forms.select fieldId="creator_id" :fieldLabel="__('workorder::modules.workOrder.creator')"
                            fieldName="creator_id" fieldRequired="true" search="true">
                            <option value="">-- Select Creator / Requester --</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->employeeDetail->designation->name ?? '--' }})</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-md-12 mt-3">
                        <x-forms.select fieldId="approver_id" :fieldLabel="__('workorder::modules.workOrder.approver')"
                            fieldName="approver_id" fieldRequired="true" search="true">
                            <option value="">-- Select Mapped Approver --</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->employeeDetail->designation->name ?? '--' }})</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-md-12 mt-4">
                        <x-forms.button-primary id="save-mapping-btn" icon="check" class="w-100">
                            @lang('app.save')
                        </x-forms.button-primary>
                    </div>
                </div>
            </div>
        </x-form>
    </div>

    {{-- Mappings List --}}
    <div class="col-xl-8 col-lg-7">
        <div class="bg-white rounded p-20 b-shadow-4">
            <h4 class="mb-4 f-21 font-weight-normal text-capitalize border-bottom-grey pb-3">
                @lang('workorder::modules.workOrder.approvalMappingsList')
            </h4>

            <div class="table-responsive">
                <table class="table table-hover border-0 w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang('workorder::modules.workOrder.creator')</th>
                            <th>@lang('workorder::modules.workOrder.approver')</th>
                            <th class="text-right">@lang('app.action')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mappings as $i => $mapping)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="d-block">
                                            <p class="mb-0 text-dark-grey font-weight-bold f-14">{{ $mapping->creator->name ?? '--' }}</p>
                                            <p class="text-lightest f-12 mb-0">{{ $mapping->creator->employeeDetail->designation->name ?? '--' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="d-block">
                                            <p class="mb-0 text-dark-grey font-weight-bold f-14">{{ $mapping->approver->name ?? '--' }}</p>
                                            <p class="text-lightest f-12 mb-0">{{ $mapping->approver->employeeDetail->designation->name ?? '--' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-mapping-row" data-id="{{ $mapping->id }}" title="@lang('app.delete')">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center shadow-none py-5">
                                    <x-cards.no-record icon="users" :message="__('messages.noRecordFound')" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    // Save Mapping via AJAX
    $('#save-mapping-btn').click(function () {
        $.easyAjax({
            url: "{{ route('approval-mappings.store') }}",
            container: '#save-mapping-form',
            type: 'POST',
            disableButton: true,
            blockUI: true,
            buttonSelector: '#save-mapping-btn',
            data: $('#save-mapping-form').serialize(),
            success: function (response) {
                if (response.status === 'success') {
                    window.location.reload();
                }
            }
        });
    });

    // Delete Mapping via AJAX
    $('body').on('click', '.delete-mapping-row', function () {
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
                var url = "{{ route('approval-mappings.destroy', ':id') }}".replace(':id', id);
                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: { '_token': '{{ csrf_token() }}', '_method': 'DELETE' },
                    success: function (response) {
                        if (response.status === 'success') {
                            window.location.reload();
                        }
                    }
                });
            }
        });
    });
});
</script>
