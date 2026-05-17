@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="d-flex justify-content-between action-bar mb-3">
        <div id="table-actions" class="d-flex align-items-center">
            @if(user()->permission('add_work_order') != 'none')
                <x-forms.link-primary :link="route('work-orders.create')" class="mr-3 openRightModal" icon="plus">
                    @lang('workorder::modules.workOrder.createWorkOrder')
                </x-forms.link-primary>
            @endif
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
            <div class="bg-white p-20 rounded b-shadow-4 d-flex justify-content-between align-items-center">
                <div class="d-block text-capitalize">
                    <h5 class="f-15 f-w-500 mb-20 text-darkest-grey">Total Work Orders</h5>
                    <div class="d-flex">
                        <p class="mb-0 f-21 font-weight-bold text-blue d-grid"><span>{{ $totalCount }}</span></p>
                    </div>
                </div>
                <div class="d-block"><i class="fa fa-file-contract f-27 text-lightest"></i></div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
            <div class="bg-white p-20 rounded b-shadow-4 d-flex justify-content-between align-items-center">
                <div class="d-block text-capitalize">
                    <h5 class="f-15 f-w-500 mb-20 text-darkest-grey">Pending Approval</h5>
                    <div class="d-flex">
                        <p class="mb-0 f-21 font-weight-bold text-red d-grid"><span>{{ $pendingCount }}</span></p>
                    </div>
                </div>
                <div class="d-block"><i class="fa fa-clock f-27 text-lightest"></i></div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
            <div class="bg-white p-20 rounded b-shadow-4 d-flex justify-content-between align-items-center">
                <div class="d-block text-capitalize">
                    <h5 class="f-15 f-w-500 mb-20 text-darkest-grey">Approved</h5>
                    <div class="d-flex">
                        <p class="mb-0 f-21 font-weight-bold text-success d-grid"><span>{{ $approvedCount }}</span></p>
                    </div>
                </div>
                <div class="d-block"><i class="fa fa-check-circle f-27 text-lightest"></i></div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
            <div class="bg-white p-20 rounded b-shadow-4 d-flex justify-content-between align-items-center">
                <div class="d-block text-capitalize">
                    <h5 class="f-15 f-w-500 mb-20 text-darkest-grey">Completed</h5>
                    <div class="d-flex">
                        <p class="mb-0 f-21 font-weight-bold text-dark-green d-grid"><span>{{ $completedCount }}</span></p>
                    </div>
                </div>
                <div class="d-block"><i class="fa fa-flag-checkered f-27 text-lightest"></i></div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="d-flex flex-column w-tables bg-white rounded">
        <div class="p-20">
            <div class="table-responsive">
                <table class="table table-hover border-0 w-100">
                    <thead>
                        <tr>
                            <th>@lang('workorder::modules.workOrder.workOrderNo')</th>
                            <th>@lang('workorder::modules.workOrder.event')</th>
                            <th>@lang('workorder::modules.workOrder.vendor')</th>
                            <th>@lang('workorder::modules.workOrder.woDate')</th>
                            <th>@lang('workorder::modules.workOrder.grandTotal')</th>
                            <th>@lang('workorder::modules.workOrder.status')</th>
                            <th class="text-right">@lang('app.action')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($workOrders as $wo)
                            <tr>
                                <td>
                                    <a href="{{ route('work-orders.show', $wo->id) }}" class="openRightModal text-darkest-grey font-weight-bold">
                                        {{ $wo->wo_number }}
                                    </a>
                                </td>
                                <td>{{ $wo->event->event_name ?? '--' }}</td>
                                <td>{{ $wo->vendor->vendor_name ?? '--' }}</td>
                                <td>{{ $wo->wo_date ? $wo->wo_date->format(company()->date_format) : '--' }}</td>
                                <td>{{ number_format($wo->grand_total, 2) }}</td>
                                <td>
                                    @php
                                        $statusClasses = [
                                            'draft'            => 'badge-secondary',
                                            'pending_approval' => 'badge-warning',
                                            'approved'         => 'badge-success',
                                            'rejected'         => 'badge-danger',
                                            'in_progress'      => 'badge-info',
                                            'completed'        => 'badge-primary',
                                            'cancelled'        => 'badge-dark',
                                        ];
                                        $cls = $statusClasses[$wo->status] ?? 'badge-secondary';
                                    @endphp
                                    <span class="badge {{ $cls }}">
                                        {{ ucwords(str_replace('_', ' ', $wo->status)) }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <div class="task_view">
                                        <a href="{{ route('work-orders.show', $wo->id) }}" class="openRightModal text-darkest-grey mr-2" title="@lang('app.view')">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        @if(!$wo->isLocked() && (user()->permission('edit_work_order') == 'all' || (in_array(user()->permission('edit_work_order'), ['added','owned','both']) && $wo->created_by == user()->id)))
                                            <a href="{{ route('work-orders.edit', $wo->id) }}" class="openRightModal text-darkest-grey mr-2" title="@lang('app.edit')">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('work-orders.pdf', $wo->id) }}" class="text-darkest-grey mr-2" title="@lang('workorder::modules.workOrder.downloadPdf')" target="_blank">
                                            <i class="fa fa-file-pdf"></i>
                                        </a>
                                        @if(user()->permission('delete_work_order') == 'all' || (in_array(user()->permission('delete_work_order'), ['added','owned','both']) && $wo->created_by == user()->id))
                                            <a href="javascript:;" class="text-darkest-grey delete-wo-row" data-id="{{ $wo->id }}" title="@lang('app.delete')">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="shadow-none">
                                    <x-cards.no-record icon="file-contract" :message="__('messages.noRecordFound')" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end">
                {{ $workOrders->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        $('body').on('click', '.delete-wo-row', function () {
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
                    var url = "{{ route('work-orders.destroy', ':id') }}".replace(':id', id);
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
