@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')

    <x-filters.filter-box>
        <!-- SEARCH START -->
        <div class="task-search d-flex py-1 px-lg-3 px-0 border-right-grey align-items-center">
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
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.status')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="status" id="filter_status">
                    <option value="all">@lang('app.all')</option>
                    <option value="active">@lang('app.active')</option>
                    <option value="inactive">@lang('app.inactive')</option>
                </select>
            </div>
        </div>
        <!-- STATUS FILTER END -->

        <!-- TYPE FILTER START -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('modules.warehouse.type')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="type" id="filter_type">
                    <option value="all">@lang('app.all')</option>
                    <option value="warehouse">@lang('modules.warehouse.typeWarehouse')</option>
                    <option value="outlet">@lang('modules.warehouse.typeOutlet')</option>
                    <option value="assembly">@lang('modules.warehouse.typeAssembly')</option>
                    <option value="transit">@lang('modules.warehouse.typeTransit')</option>
                </select>
            </div>
        </div>
        <!-- TYPE FILTER END -->

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
        <div class="d-block d-lg-flex d-md-flex justify-content-between action-bar">

            <div id="table-actions" class="flex-grow-1 align-items-center">
                @if ($addPermission == 'all' || in_array('admin', user_roles()))
                    <x-forms.link-primary :link="route('warehouses.create')" class="mr-3 openRightModal float-left mb-2 mb-lg-0 mb-md-0" icon="plus">
                        @lang('app.add') @lang('modules.warehouse.warehouse')
                    </x-forms.link-primary>
                @endif
            </div>

            <x-datatable.actions>
                <div class="select-status mr-3">
                    <select name="action_type" class="form-control select-picker" id="quick-action-type" disabled>
                        <option value="">@lang('app.selectAction')</option>
                        <option value="delete">@lang('app.delete')</option>
                    </select>
                </div>
            </x-datatable.actions>

        </div>

        <!-- Table Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive">
            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
        </div>
        <!-- Table Box End -->
    </div>
    <!-- CONTENT WRAPPER END -->

@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        (function() {
            var $body  = $('body');
            var $table = $('#warehouses-table');
            var namespace = '.warehousesIndex';

            $table.off('preXhr.dt').on('preXhr.dt', function(e, settings, data) {
                data['searchText'] = $('#search-text-field').val();
                data['status']     = $('#filter_status').val() || 'all';
                data['type']       = $('#filter_type').val()   || 'all';
            });

            var showTable = function() {
                var table = window.LaravelDataTables['warehouses-table'];
                if (table) { table.draw(false); }
            };
            window.showTable = showTable;

            $body.off(namespace);

            $body.on('change' + namespace + ' keyup' + namespace, '#filter_status, #filter_type', function() {
                var hasFilters = ($('#filter_status').val() !== 'all') || ($('#filter_type').val() !== 'all');
                hasFilters ? $('#reset-filters').removeClass('d-none') : $('#reset-filters').addClass('d-none');
                showTable();
            });

            $body.on('keyup' + namespace, '#search-text-field', function() {
                if ($(this).val() !== '') { $('#reset-filters').removeClass('d-none'); }
                showTable();
            });

            $body.on('click' + namespace, '#reset-filters', function() {
                $('#filter-form')[0].reset();
                $('.filter-box .select-picker').selectpicker('refresh');
                $('#reset-filters').addClass('d-none');
                showTable();
            });

            /* Delete single row */
            $body.on('click' + namespace, '.delete-table-row', function() {
                var id  = $(this).data('warehouse-id');
                var url = "{{ route('warehouses.destroy', ':id') }}".replace(':id', id);

                Swal.fire({
                    title: "@lang('messages.sweetAlertTitle')",
                    text:  "@lang('messages.recoverRecord')",
                    icon:  'warning',
                    showCancelButton: true,
                    focusConfirm: false,
                    confirmButtonText: "@lang('messages.confirmDelete')",
                    cancelButtonText:  "@lang('app.cancel')",
                    customClass: { confirmButton: 'btn btn-primary mr-3', cancelButton: 'btn btn-secondary' },
                    showClass:   { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.easyAjax({
                            type: 'POST',
                            url:  url,
                            data: { '_token': '{{ csrf_token() }}', '_method': 'DELETE' },
                            success: function(response) {
                                if (response.status === 'success') { showTable(); }
                            }
                        });
                    }
                });
            });

            /* Toggle is_active status */
            $body.on('change' + namespace, '.toggle-warehouse-status', function() {
                var warehouseId = $(this).data('warehouse-id');
                $.easyAjax({
                    type: 'POST',
                    url:  "{{ route('warehouses.toggle-status') }}",
                    data: { '_token': '{{ csrf_token() }}', 'warehouseId': warehouseId },
                    success: function(response) {
                        if (response.status === 'success') { showTable(); }
                    }
                });
            });

            /* Quick-action bulk delete */
            $body.on('change' + namespace, '#quick-action-type', function() {
                var actionValue = $(this).val();
                if (actionValue !== '') {
                    $('#quick-action-apply').removeAttr('disabled');
                } else {
                    $('#quick-action-apply').attr('disabled', true);
                }
            });

            $body.on('click' + namespace, '#quick-action-apply', function() {
                var actionValue = $('#quick-action-type').val();
                if (actionValue === 'delete') {
                    Swal.fire({
                        title: "@lang('messages.sweetAlertTitle')",
                        text:  "@lang('messages.recoverRecord')",
                        icon:  'warning',
                        showCancelButton: true,
                        focusConfirm: false,
                        confirmButtonText: "@lang('messages.confirmDelete')",
                        cancelButtonText:  "@lang('app.cancel')",
                        customClass: { confirmButton: 'btn btn-primary mr-3', cancelButton: 'btn btn-secondary' },
                        showClass:   { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) { applyQuickAction(); }
                    });
                } else {
                    applyQuickAction();
                }
            });

            function applyQuickAction() {
                var rowIds = $('#warehouses-table input:checkbox:checked').map(function() {
                    return $(this).val();
                }).get();

                // Bulk delete via individual destroy calls
                if (rowIds.length === 0) { return; }
                var calls = rowIds.map(function(id) {
                    var url = "{{ route('warehouses.destroy', ':id') }}".replace(':id', id);
                    return $.easyAjax({ type: 'POST', url: url, data: { '_token': '{{ csrf_token() }}', '_method': 'DELETE' } });
                });
                $.when.apply($, calls).done(function() { showTable(); });
            }
            window.applyQuickAction = applyQuickAction;

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
