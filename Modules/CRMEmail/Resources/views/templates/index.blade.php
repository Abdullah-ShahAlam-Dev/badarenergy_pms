@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@php
    $addPermission = user()->permission('add_crm_email');
@endphp

@section('filter-section')
    <x-filters.filter-box>
        <!-- SEARCH BY TEMPLATE START -->
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
        <!-- SEARCH BY TEMPLATE END -->

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
                    <x-forms.link-primary :link="route('crm-email-templates.create')" class="mr-3 openRightModal float-left"
                                          icon="plus">
                        Add Template
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
            var $table = $('#crm-email-templates-table');
            var namespace = '.crmEmailTemplatesIndex';

            $body.off(namespace);
            $table.off('preXhr.dt' + namespace);

            $table.on('preXhr.dt' + namespace, function (e, settings, data) {
                data['searchText'] = $('#search-text-field').val();
            });

            function showTable() {
                if (window.LaravelDataTables && window.LaravelDataTables["crm-email-templates-table"]) {
                    window.LaravelDataTables["crm-email-templates-table"].draw(false);
                }
            }
            window.showTable = showTable;

            $body.on('keyup' + namespace, '#search-text-field', function () {
                if ($(this).val() != "") {
                    $('#reset-filters').removeClass('d-none');
                } else {
                    $('#reset-filters').addClass('d-none');
                }
                showTable();
            });

            $body.on('click' + namespace, '#reset-filters', function () {
                $('#search-text-field').val('');
                $('#reset-filters').addClass('d-none');
                showTable();
            });

            // Action: Preview Template
            $body.on('click' + namespace, '.preview-template', function() {
                var id = $(this).data('template-id');
                var url = "{{ route('crm-email-templates.preview', ':id') }}".replace(':id', id);
                $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
                $.ajaxModal(MODAL_LG, url);
            });

            // Action: Duplicate Template
            $body.on('click' + namespace, '.duplicate-template', function() {
                var id = $(this).data('template-id');
                var url = "{{ route('crm-email-templates.duplicate', ':id') }}".replace(':id', id);
                var token = "{{ csrf_token() }}";

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: { '_token': token },
                    success: function (response) {
                        if (response.status == "success") {
                            showTable();
                        }
                    }
                });
            });

            // Action: Delete Template
            $body.on('click' + namespace, '.delete-table-row', function () {
                var id = $(this).data('template-id');
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
                        var url = "{{ route('crm-email-templates.destroy', ':id') }}".replace(':id', id);
                        var token = "{{ csrf_token() }}";
                        $.easyAjax({
                            type: 'POST',
                            url: url,
                            data: { '_token': token, '_method': 'DELETE' },
                            success: function (response) {
                                if (response.status == "success") {
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
