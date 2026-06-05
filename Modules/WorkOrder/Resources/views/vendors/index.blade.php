@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="d-flex justify-content-between action-bar mb-3">
        <div id="table-actions" class="d-flex align-items-center">
            @if(in_array('admin', user_roles()) || user()->permission('add_vendor') != 'none')
                <x-forms.link-primary :link="route('vendors.create')" class="mr-3 openRightModal" icon="plus">
                    @lang('workorder::modules.vendor.addVendor')
                </x-forms.link-primary>
            @endif

            <x-forms.link-secondary :link="route('work-orders.index')" class="mr-3" icon="file-contract">
                @lang('workorder::modules.workOrder.menuName')
            </x-forms.link-secondary>

            @if(in_array('admin', user_roles()) || user()->permission('manage_approval_mappings') == 'all')
                <x-forms.link-secondary :link="route('approval-mappings.index')" class="mr-3" icon="users">
                    @lang('workorder::modules.workOrder.approvalMappings')
                </x-forms.link-secondary>
            @endif
        </div>
    </div>

    <div class="d-flex flex-column w-tables bg-white rounded">
        <div class="p-20">
            <div class="table-responsive">
                <table class="table table-hover border-0 w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang('workorder::modules.vendor.vendorName')</th>
                            <th>@lang('workorder::modules.vendor.companyName')</th>
                            <th>@lang('workorder::modules.vendor.mobile')</th>
                            <th>@lang('workorder::modules.vendor.email')</th>
                            <th>@lang('workorder::modules.vendor.category')</th>
                            <th>@lang('workorder::modules.vendor.status')</th>
                            <th class="text-right">@lang('app.action')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vendors as $i => $vendor)
                        <tr>
                            <td>{{ $vendors->firstItem() + $i }}</td>
                            <td>
                                <a href="{{ route('vendors.show', $vendor->id) }}" class="openRightModal text-darkest-grey font-weight-bold">
                                    {{ $vendor->vendor_name }}
                                </a>
                            </td>
                            <td>{{ $vendor->company_name ?? '--' }}</td>
                            <td>{{ $vendor->mobile ?? '--' }}</td>
                            <td>{{ $vendor->email ?? '--' }}</td>
                            <td>{{ $vendor->category ?? '--' }}</td>
                            <td>
                                <span class="badge {{ $vendor->status == 'active' ? 'badge-success' : 'badge-danger' }}">
                                    {{ ucfirst($vendor->status) }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="task_view">
                                    <a href="{{ route('vendors.show', $vendor->id) }}" class="openRightModal text-darkest-grey mr-2" title="@lang('app.view')">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    @if(user()->permission('edit_vendor') == 'all' || (in_array(user()->permission('edit_vendor'),['added','owned','both']) && $vendor->added_by == user()->id))
                                    <a href="{{ route('vendors.edit', $vendor->id) }}" class="openRightModal text-darkest-grey mr-2" title="@lang('app.edit')">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    @endif
                                    <a href="{{ route('work-orders.create') }}?vendor_id={{ $vendor->id }}" class="openRightModal text-darkest-grey mr-2" title="Create Work Order">
                                        <i class="fa fa-plus-circle"></i>
                                    </a>
                                    @if(user()->permission('delete_vendor') == 'all' || (in_array(user()->permission('delete_vendor'),['added','owned','both']) && $vendor->added_by == user()->id))
                                    <a href="javascript:;" class="text-darkest-grey delete-vendor-row" data-id="{{ $vendor->id }}" title="@lang('app.delete')">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="shadow-none">
                                <x-cards.no-record icon="store" :message="__('messages.noRecordFound')" />
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end">
                {{ $vendors->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    $('body').on('click', '.delete-vendor-row', function () {
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
                var url = "{{ route('vendors.destroy', ':id') }}".replace(':id', id);
                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: { '_token': '{{ csrf_token() }}', '_method': 'DELETE' },
                    success: function (response) {
                        if (response.status === 'success') { window.location.reload(); }
                    }
                });
            }
        });
    });
});
</script>
@endpush
