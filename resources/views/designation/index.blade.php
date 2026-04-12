@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
    <style>
        .filter-box {
            z-index: 2;
        }
    </style>
@endpush

@php
    $addDesignationPermission = user()->permission('add_designation');
@endphp

@section('filter-section')
    <x-filters.filter-box>
        <!-- SEARCH BY TASK START -->
        <div class="task-search d-flex pr-lg-2 py-1 px-0 border-right-grey align-items-center">
            <form class="w-100 mr-1 mr-lg-0 mr-md-1 ml-md-1 ml-0 ml-lg-0">
                <div class="input-group rounded">
                    <div class="input-group-prepend margin-9">
                        <span class="input-group-text border-0 bg-additional-grey">
                            <i class="fa fa-search f-13 text-dark-grey"></i>
                        </span>
                    </div>
                    <input type="text" class="form-control f-14 p-1 border-additional-grey" id="search-text-field"
                           placeholder="@lang('app.startTyping')">
                </div>
            </form>
        </div>
        <!-- SEARCH BY TASK END -->

        <!-- RESET START -->
        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
        <!-- RESET END -->

        <!-- MORE FILTERS START -->
        <x-filters.more-filter-box>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('app.menu.designation')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="parent_id" id="parent_id"
                                data-live-search="true" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($designations as $designation)
                                <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>


        </x-filters.more-filter-box>
        <!-- MORE FILTERS END -->

    </x-filters.filter-box>
@endsection


@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">

        <!-- Add Task Export Buttons Start -->
        <div class="d-block d-lg-flex d-md-flex justify-content-between action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center">
                @if ($addDesignationPermission == 'all' || $addDesignationPermission == 'added')
                    <x-forms.link-primary :link="route('designations.create')" class="mr-3 openRightModal float-left"
                                          icon="plus">
                        @lang('app.menu.addDesignation')
                    </x-forms.link-primary>
                @endif
            </div>

            <x-datatable.actions>
                <div class="select-status mr-3 pl-3">
                    <select name="action_type" class="form-control select-picker" id="quick-action-type" disabled>
                        <option value="">@lang('app.selectAction')</option>
                        <option value="delete">@lang('app.delete')</option>
                    </select>
                </div>
            </x-datatable.actions>

            <div class="btn-group mt-2 mt-lg-0 mt-md-0 ml-3" role="group" aria-label="Basic example">
                <a href="{{ route('designations.index') }}" class="btn btn-secondary f-14 btn-active"
                   data-toggle="tooltip"
                   data-original-title="@lang('modules.leaves.tableView')"><i class="side-icon bi bi-list-ul"></i></a>

                <a href="{{ route('designation.hierarchy') }}" class="btn btn-secondary f-14" data-toggle="tooltip"
                   data-original-title="@lang('app.hierarchy')"><i class="bi bi-diagram-3"></i></a>
            </div>
        </div>

        <!-- leave table Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}

        </div>
        <!-- leave table End -->

    </div>
    <!-- CONTENT WRAPPER END -->

@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        (function() {
            var $body = $('body');
            var $table = $('#Designation-table');
            var namespace = '.designationsIndex';

            $body.off(namespace);
            $table.off('preXhr.dt' + namespace);

            $table.on('preXhr.dt' + namespace, function (e, settings, data) {
                var parentId = $('#parent_id').val();
                var childId = $('#child').val();
                var searchText = $('#search-text-field').val();

                data['searchText'] = searchText;
                data['parentId'] = parentId;
                data['childId'] = childId;
            });

            function showTable() {
                if (window.LaravelDataTables && window.LaravelDataTables["Designation-table"]) {
                    window.LaravelDataTables["Designation-table"].draw(false);
                }
            }
            window.showTable = showTable;

            $body.on('change keyup' + namespace, '#parent_id, #child', function () {
                var hasFilters = ($('#parent_id').val() != "all") || ($('#child').val() != "all");
                $('#reset-filters').toggleClass('d-none', !hasFilters);
                showTable();
            });

            $body.on('keyup' + namespace, '#search-text-field', function () {
                if ($(this).val() != "") {
                    $('#reset-filters').removeClass('d-none');
                }
                showTable();
            });

            $body.on('click' + namespace, '#reset-filters', function () {
                $('#filter-form')[0].reset();
                $('.filter-box .select-picker').selectpicker("refresh");
                $('#reset-filters').addClass('d-none');
                showTable();
            });

            $body.on('click' + namespace, '#reset-filters-2', function () {
                $('#filter-form')[0].reset();
                $('.filter-box #parent_id').val('all');
                $('.filter-box #child').val('all');
                $('.filter-box .select-picker').selectpicker("refresh");
                $('#reset-filters').addClass('d-none');
                showTable();
            });

            $body.on('click' + namespace, '.delete-table-row', function () {
                var id = $(this).data('designation-id');
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
                        var url = "{{ route('designations.destroy', ':id') }}".replace(':id', id);
                        var token = "{{ csrf_token() }}";
                        $.easyAjax({
                            type: 'POST',
                            url: url,
                            blockUI: true,
                            data: { '_token': token, '_method': 'DELETE' },
                            success: function (response) {
                                if (response.status == "success") { showTable(); }
                            }
                        });
                    }
                });
            });

            $body.on('change' + namespace, '#quick-action-type', function () {
                const actionValue = $(this).val();
                if (actionValue != '') {
                    $('#quick-action-apply').removeAttr('disabled');
                } else {
                    $('#quick-action-apply').attr('disabled', true);
                    $('.quick-action-field').addClass('d-none');
                }
            });

            function applyQuickAction() {
                const rowdIds = $("#Designation-table input:checkbox:checked").map(function () {
                    return $(this).val();
                }).get();

                const url = "{{ route('designations.apply_quick_action') }}?row_ids=" + rowdIds;

                $.easyAjax({
                    url: url,
                    container: '#quick-action-form',
                    type: "POST",
                    disableButton: true,
                    buttonSelector: "#quick-action-apply",
                    data: $('#quick-action-form').serialize(),
                    success: function (response) {
                        if (response.status === 'success') {
                            showTable();
                            if (typeof resetActionButtons === 'function') resetActionButtons();
                            if (typeof deSelectAll === 'function') deSelectAll();
                            $('#quick-action-form').hide();
                        }
                    }
                })
            }
            window.applyQuickAction = applyQuickAction;

            $body.on('click' + namespace, '#quick-action-apply', function () {
                const actionValue = $('#quick-action-type').val();
                if (actionValue === 'delete') {
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
                            applyQuickAction();
                        }
                    });
                } else {
                    applyQuickAction();
                }
            });

            document.addEventListener('turbo:before-cache', function cleanup() {
                $body.off(namespace);
                $table.off('preXhr.dt' + namespace);
                delete window.showTable;
                delete window.applyQuickAction;
                document.removeEventListener('turbo:before-cache', cleanup);
            }, { once: true });
        })();
    </script>

@endpush
