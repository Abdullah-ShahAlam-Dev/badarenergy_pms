@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="d-flex justify-content-between action-bar mb-3">
        <div id="table-actions" class="d-flex align-items-center">
            @if(user()->permission('add_gate_pass') == 'all' || user()->permission('add_gate_pass') == 'added')
                <x-forms.link-primary :link="route('gate-pass.create')" class="mr-3 openRightModal" icon="plus">
                    @lang('gatepass::modules.gatePass.addRequest')
                </x-forms.link-primary>
            @endif
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
            <div class="bg-white p-20 rounded b-shadow-4 d-flex justify-content-between align-items-center">
                <div class="d-block text-capitalize">
                    <h5 class="f-15 f-w-500 mb-20 text-darkest-grey">Total Requests</h5>
                    <div class="d-flex">
                        <a href="javascript:;"><p class="mb-0 f-21 font-weight-bold text-blue d-grid"><span id="totalRequests">{{ $requests->total() }}</span></p></a>
                    </div>
                </div>
                <div class="d-block">
                    <i class="fa fa-file-invoice f-27 text-lightest"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
            <div class="bg-white p-20 rounded b-shadow-4 d-flex justify-content-between align-items-center">
                <div class="d-block text-capitalize">
                    <h5 class="f-15 f-w-500 mb-20 text-darkest-grey">Pending HOD</h5>
                    <div class="d-flex">
                        <a href="javascript:;"><p class="mb-0 f-21 font-weight-bold text-red d-grid"><span id="pendingHod">{{ $requests->where('status', 'pending_hod')->count() }}</span></p></a>
                    </div>
                </div>
                <div class="d-block">
                    <i class="fa fa-user-clock f-27 text-lightest"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
            <div class="bg-white p-20 rounded b-shadow-4 d-flex justify-content-between align-items-center">
                <div class="d-block text-capitalize">
                    <h5 class="f-15 f-w-500 mb-20 text-darkest-grey">Authorized</h5>
                    <div class="d-flex">
                        <a href="javascript:;"><p class="mb-0 f-21 font-weight-bold text-success d-grid"><span>{{ $requests->where('status', 'pending_security')->count() }}</span></p></a>
                    </div>
                </div>
                <div class="d-block">
                    <i class="fa fa-check-double f-27 text-lightest"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
            <div class="bg-white p-20 rounded b-shadow-4 d-flex justify-content-between align-items-center">
                <div class="d-block text-capitalize">
                    <h5 class="f-15 f-w-500 mb-20 text-darkest-grey">Completed</h5>
                    <div class="d-flex">
                        <a href="javascript:;"><p class="mb-0 f-21 font-weight-bold text-dark-green d-grid"><span>{{ $requests->where('status', 'completed')->count() }}</span></p></a>
                    </div>
                </div>
                <div class="d-block">
                    <i class="fa fa-door-open f-27 text-lightest"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-column w-tables bg-white rounded">
        <div class="p-20">
            <div class="table-responsive">
                <table class="table table-hover border-0 w-100">
                    <thead>
                        <tr>
                            <th>@lang('gatepass::modules.gatePass.requestNumber')</th>
                            <th>@lang('app.employee')</th>
                            <th>@lang('gatepass::modules.gatePass.requestDate')</th>
                            <th>@lang('gatepass::modules.gatePass.type')</th>
                            <th>@lang('gatepass::modules.gatePass.status')</th>
                            <th class="text-right">@lang('app.action')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $request)
                            <tr>
                                <td>
                                    <a href="{{ route('gate-pass.show', $request->id) }}" class="openRightModal text-darkest-grey font-weight-bold">
                                        {{ $request->request_number }}
                                    </a>
                                </td>
                                <td>
                                    <x-employee :user="$request->user" />
                                </td>
                                <td>{{ $request->request_date->format(company()->date_format) }}</td>
                                <td>
                                    <span class="badge badge-light">
                                        {{ strtoupper($request->type) }} ({{ $request->return_type == 'returnable' ? 'R' : 'NR' }})
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $statusClass = 'badge-warning';
                                        if($request->status == 'completed') $statusClass = 'badge-success';
                                        if(str_contains($request->status, 'rejected')) $statusClass = 'badge-danger';
                                        if($request->status == 'pending_security') $statusClass = 'badge-info';
                                    @endphp
                                    <span class="badge {{ $statusClass }}">
                                        {{ str_replace('_', ' ', strtoupper($request->status)) }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <div class="task_view">
                                        <a href="{{ route('gate-pass.show', $request->id) }}" class="openRightModal text-darkest-grey mr-2" title="@lang('app.view')">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        @if(
                                            user()->permission('edit_gate_pass') == 'all' || 
                                            (user()->permission('edit_gate_pass') == 'added' && $request->user_id == user()->id) ||
                                            (user()->permission('edit_gate_pass') == 'owned' && $request->user_id == user()->id) ||
                                            (user()->permission('edit_gate_pass') == 'both' && $request->user_id == user()->id)
                                        )
                                            @if($request->status == 'pending_hod' || $request->status == 'draft' || $request->status == 'sent_back')
                                                <a href="{{ route('gate-pass.edit', $request->id) }}" class="openRightModal text-darkest-grey mr-2" title="@lang('app.edit')">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            @endif
                                        @endif
                                        @if(
                                            user()->permission('delete_gate_pass') == 'all' || 
                                            (user()->permission('delete_gate_pass') == 'added' && $request->user_id == user()->id) ||
                                            (user()->permission('delete_gate_pass') == 'owned' && $request->user_id == user()->id) ||
                                            (user()->permission('delete_gate_pass') == 'both' && $request->user_id == user()->id)
                                        )
                                            <a href="javascript:;" class="text-darkest-grey delete-table-row" data-id="{{ $request->id }}" title="@lang('app.delete')">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="shadow-none">
                                    <x-cards.no-record icon="file-invoice" :message="__('messages.noRecordFound')" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end">
                {{ $requests->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('body').on('click', '.delete-table-row', function() {
            var id = $(this).data('id');
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
                    var url = "{{ route('gate-pass.destroy', ':id') }}";
                    url = url.replace(':id', id);
                    var token = "{{ csrf_token() }}";
                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {'_token': token, '_method': 'DELETE'},
                        success: function (response) {
                            if (response.status == "success") {
                                window.location.reload();
                            }
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
