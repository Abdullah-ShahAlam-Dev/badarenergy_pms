@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')
    <x-filters.filter-box>
        <!-- Status Filter -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-zero">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.status')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="status" id="status" data-live-search="true" data-size="8">
                    <option value="all">@lang('app.all')</option>
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
        </div>

        <!-- Search Box -->
        <div class="task-search d-flex py-1 px-lg-3 px-0 border-right-grey align-items-center">
            <form action="" class="w-100">
                <div class="input-group bg-grey rounded">
                    <div class="input-group-prepend">
                        <span class="input-group-text border-0 bg-additional-grey">
                            <i class="fa fa-search f-13 text-dark-grey"></i>
                        </span>
                    </div>
                    <input type="text" class="form-control f-14 border-0 w-100 height-35" id="search-text-field"
                           placeholder="@lang('app.startTyping')">
                </div>
            </form>
        </div>

        <!-- Reset Button -->
        <div class="align-self-center d-flex pl-3">
            <x-forms.button-secondary id="reset-filters" class="btn-xs d-none" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
    </x-filters.filter-box>
@endsection

@section('content')
    <!-- Content Area Start -->
    <div class="content-wrapper">
        <!-- Add Button and Action Toolbar -->
        <div class="d-flex justify-content-between action-bar" id="table-actions">
            @if ($addPermission == 'all' || in_array('admin', user_roles()))
                <x-forms.link-primary :link="route('stock-intakes.create')" class="mr-3" icon="plus">
                    Add Stock Intake
                </x-forms.link-primary>
            @endif
        </div>

        <!-- Yajra DataTable Container -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">
            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
        </div>
    </div>
    <!-- Content Area End -->
@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        $(document).ready(function() {
            // Re-draw datatable on filter changes
            $('#status, #search-text-field').on('change keyup', function() {
                if ($('#status').val() !== 'all' || $('#search-text-field').val() !== '') {
                    $('#reset-filters').removeClass('d-none');
                } else {
                    $('#reset-filters').addClass('d-none');
                }
                showTable();
            });

            // Reset filters logic
            $('#reset-filters').click(function() {
                $('#status').val('all').trigger('change');
                $('#search-text-field').val('');
                showTable();
            });
        });

        function showTable() {
            window.LaravelDataTables["stock-intakes-table"].draw();
        }

        // Custom Delete Action handler
        $('body').on('click', '.delete-table-row', function() {
            var id = $(this).data('voucher-id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "You will not be able to recover this stock intake voucher!",
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
                    var url = "{{ route('stock-intakes.destroy', ':id') }}";
                    url = url.replace(':id', id);

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {
                            '_token': token,
                            '_method': 'DELETE'
                        },
                        success: function(response) {
                            if (response.status === "success") {
                                showTable();
                            }
                        }
                    });
                }
            });
        });

        // Approve action handler
        $('body').on('click', '.approve-voucher', function() {
            var id = $(this).data('voucher-id');
            Swal.fire({
                title: "Approve Voucher?",
                text: "Approving will instantly generate serial numbers (if serialized) and increase warehouse inventory.",
                icon: 'info',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "Approve and Post",
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
                    var url = "{{ route('stock-intakes.approve', ':id') }}";
                    url = url.replace(':id', id);

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {
                            '_token': "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.status === "success") {
                                showTable();
                            }
                        }
                    });
                }
            });
        });
    </script>
@endpush
