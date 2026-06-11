@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@php
    $addPermission = user()->permission('add_crm_email');
@endphp

@section('filter-section')
    <x-filters.filter-box>
        <!-- SEARCH START -->
        <div class="task-search d-flex py-1 pr-lg-2 px-0 border-right-grey align-items-center">
            <form class="w-100 mr-1 mr-lg-0 mr-md-1 ml-md-1 ml-0 ml-lg-0">
                <div class="input-group bg-grey rounded">
                    <div class="input-group-prepend">
                        <span class="input-group-text border-0 bg-additional-grey">
                            <i class="fa fa-search f-13 text-dark-grey"></i>
                        </span>
                    </div>
                    <input type="text" class="form-control f-14 p-1 border-additional-grey" id="search-text-field"
                           placeholder="@lang('app.startTyping')">
                </div>
            </form>
        </div>
        <!-- SEARCH END -->

        <!-- STATUS FILTER START -->
        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0 border-right-grey">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">Status</p>
            <select class="form-control select-picker" id="status-filter" data-style="form-select border-0 f-14">
                <option value="all">All</option>
                <option value="draft">Draft</option>
                <option value="scheduled">Scheduled</option>
                <option value="sending">Sending</option>
                <option value="completed">Completed</option>
                <option value="paused">Paused</option>
                <option value="canceled">Canceled</option>
            </select>
        </div>
        <!-- STATUS FILTER END -->

        <!-- RESET START -->
        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
        <!-- RESET END -->
    </x-filters.filter-box>
@endsection

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <!-- Action Buttons Start -->
        <div class="d-block d-lg-flex d-md-flex justify-content-between action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center">
                @if ($addPermission == 'all' || $addPermission == 'added')
                    <x-forms.link-primary :link="route('crm-email-campaigns.create')" class="mr-3 openRightModal float-left"
                                          icon="plus">
                        Add Campaign
                    </x-forms.link-primary>
                @endif
            </div>
        </div>

        <!-- DataTable Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">
            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
        </div>
        <!-- DataTable End -->
    </div>
    <!-- CONTENT WRAPPER END -->
@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        (function() {
            var $body = $('body');
            var $table = $('#crm-email-campaigns-table');
            var namespace = '.crmCampaignsIndex';
            var csrfToken = "{{ csrf_token() }}";

            $body.off(namespace);
            $table.off('preXhr.dt' + namespace);

            $table.on('preXhr.dt' + namespace, function (e, settings, data) {
                data['searchText'] = $('#search-text-field').val();
                data['status']     = $('#status-filter').val();
            });

            function showTable() {
                if (window.LaravelDataTables && window.LaravelDataTables["crm-email-campaigns-table"]) {
                    window.LaravelDataTables["crm-email-campaigns-table"].draw(false);
                }
            }
            window.showTable = showTable;

            $body.on('keyup' + namespace, '#search-text-field', function () {
                $('#reset-filters').toggleClass('d-none', $(this).val() === '');
                showTable();
            });

            $body.on('change' + namespace, '#status-filter', function () {
                $('#reset-filters').removeClass('d-none');
                showTable();
            });

            $body.on('click' + namespace, '#reset-filters', function () {
                $('#search-text-field').val('');
                $('#status-filter').val('all').trigger('change');
                $('#reset-filters').addClass('d-none');
                showTable();
            });

            // Action: View Stats (Modal)
            $body.on('click' + namespace, '.view-campaign-stats', function () {
                var id = $(this).data('campaign-id');
                var url = "{{ route('crm-email-campaigns.stats', ':id') }}".replace(':id', id);
                $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
                $.ajaxModal(MODAL_LG, url);
            });

            // Action: Launch Campaign
            $body.on('click' + namespace, '.launch-campaign', function () {
                var id = $(this).data('campaign-id');
                var url = "{{ route('crm-email-campaigns.launch', ':id') }}".replace(':id', id);

                Swal.fire({
                    title: 'Launch Campaign?',
                    text: 'This will start sending emails immediately to all recipients in the segment.',
                    icon: 'question',
                    showCancelButton: true,
                    focusConfirm: false,
                    confirmButtonText: 'Yes, Launch!',
                    cancelButtonText: "@lang('app.cancel')",
                    customClass: { confirmButton: 'btn btn-primary mr-3', cancelButton: 'btn btn-secondary' },
                    showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.easyAjax({
                            type: 'POST',
                            url: url,
                            data: { '_token': csrfToken },
                            success: function (response) {
                                if (response.status === 'success') {
                                    showTable();
                                }
                            }
                        });
                    }
                });
            });

            // Action: Pause Campaign
            $body.on('click' + namespace, '.pause-campaign', function () {
                var id = $(this).data('campaign-id');
                var url = "{{ route('crm-email-campaigns.pause', ':id') }}".replace(':id', id);

                Swal.fire({
                    title: 'Pause Campaign?',
                    text: 'Pending emails will not be sent. Already-queued jobs may still complete.',
                    icon: 'warning',
                    showCancelButton: true,
                    focusConfirm: false,
                    confirmButtonText: 'Yes, Pause',
                    cancelButtonText: "@lang('app.cancel')",
                    customClass: { confirmButton: 'btn btn-warning mr-3', cancelButton: 'btn btn-secondary' },
                    showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.easyAjax({
                            type: 'POST',
                            url: url,
                            data: { '_token': csrfToken },
                            success: function (response) {
                                if (response.status === 'success') {
                                    showTable();
                                }
                            }
                        });
                    }
                });
            });

            // Action: Delete Campaign
            $body.on('click' + namespace, '.delete-campaign-row', function () {
                var id = $(this).data('campaign-id');
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
                        var url = "{{ route('crm-email-campaigns.destroy', ':id') }}".replace(':id', id);
                        $.easyAjax({
                            type: 'POST',
                            url: url,
                            data: { '_token': csrfToken, '_method': 'DELETE' },
                            success: function (response) {
                                if (response.status === 'success') {
                                    showTable();
                                }
                            }
                        });
                    }
                });
            });

            document.addEventListener('turbo:before-cache', function cleanup() {
                $body.off(namespace);
                $table.off('preXhr.dt' + namespace);
                delete window.showTable;
                document.removeEventListener('turbo:before-cache', cleanup);
            }, { once: true });
        })();
    </script>
@endpush
