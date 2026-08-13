@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')

    <x-filters.filter-box>

        <!-- CATEGORY START -->
        <div class="select-box d-flex py-2 pr-2 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">
                @lang('modules.productCategory.productCategory')</p>
            <div class="select-status d-flex">
                <select class="form-control select-picker" name="category_id" id="category_id">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ mb_ucwords($category->category_name) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <!-- CATEGORY END -->

        <!-- SUBCATEGORY START -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">
                @lang('modules.productCategory.productSubCategory')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="sub_category" id="sub_category">
                    <option selected value="all">@lang('app.all')</option>
                </select>
            </div>
        </div>
        <!-- SUBCATEGORY END -->

        <!-- PRODUCT SOURCE / TYPE START -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">
                Type & Source</p>
            <div class="select-status d-flex">
                <select class="form-control select-picker" name="is_serialized" id="is_serialized_filter">
                    <option value="all">@lang('app.all')</option>
                    <option value="ready_made">Ready-Made</option>
                    <option value="assembly_part">Assembly Part</option>
                    <option value="badar_energy">Badar Energy</option>
                    <option value="oem">OEM</option>
                </select>
            </div>
        </div>
        <!-- PRODUCT SOURCE END -->

        <!-- PRODUCT ORIGIN START -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">
                Origin</p>
            <div class="select-status d-flex">
                <select class="form-control select-picker" name="origin_type" id="origin_type_filter">
                    <option value="all">@lang('app.all')</option>
                    <option value="local">Local</option>
                    <option value="imported">Imported</option>
                </select>
            </div>
        </div>
        <!-- PRODUCT ORIGIN END -->

        <!-- STOCK AVAILABILITY START -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">
                Stock Status</p>
            <div class="select-status d-flex">
                <select class="form-control select-picker" name="stock_status" id="stock_status_filter">
                    <option value="all">@lang('app.all')</option>
                    <option value="in_stock">In Stock (>=10)</option>
                    <option value="low_stock">Low Stock (&lt;10)</option>
                    <option value="out_of_stock">Out of Stock (0)</option>
                </select>
            </div>
        </div>
        <!-- STOCK AVAILABILITY END -->

        <!-- SEARCH BY TASK START -->
        <div class="task-search d-flex py-1 px-lg-3 px-0 border-right-grey align-items-center">
            <form class="w-100 mr-1 mr-lg-0 mr-md-1 ml-md-1 ml-0 ml-lg-0">
                <div class="input-group bg-grey rounded">
                    <div class="input-group-prepend">
                        <span class="input-group-text border-0 bg-additional-grey">
                            <i class="fa fa-search f-13 text-dark-grey"></i>
                        </span>
                    </div>
                    <input type="text" class="form-control f-14 p-1 border-additional-grey" id="search-text-field"
                        placeholder="Search Name, Code, Barcode, Voltage...">
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
    </x-filters.filter-box>

@endsection

@php
$addProductPermission = user()->permission('add_product');
$addOrderPermission = user()->permission('add_order');
@endphp

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <div class="d-flex justify-content-between action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center">
                @if ($addProductPermission == 'all' || $addProductPermission == 'added')
                    <x-forms.link-primary :link="route('products.create')" class="mr-3 openRightModal float-left"
                        icon="plus">
                        @lang('app.add')
                        @lang('app.product')
                    </x-forms.link-primary>
                @endif
            </div>

            @if (in_array('client', user_roles()) && $addOrderPermission == 'all')
                <div class="btn-group" role="group">
                    <x-forms.link-primary :link="route('products.cart')" icon="shopping-bag">
                        @lang('app.cart') <span
                            class="badge badge-light ml-2 productCounter">{{ $cartProductCount }}</span>
                    </x-forms.link-primary>
                </div>
            @endif

            @if (!in_array('client', user_roles()))
                <x-datatable.actions>
                    <div class="select-status mr-3 pl-3">
                        <select name="action_type" class="form-control select-picker" id="quick-action-type" disabled>
                            <option value="">@lang('app.selectAction')</option>
                            <option value="change-purchase">@lang('app.purchaseAllow')</option>
                            <option value="delete">@lang('app.delete')</option>
                        </select>
                    </div>
                    <div class="select-status mr-3 d-none quick-action-field" id="change-status-action">
                        <select name="status" class="form-control select-picker">
                            <option value="1">@lang('app.allowed')</option>
                            <option value="0">@lang('app.notAllowed')</option>
                        </select>
                    </div>
                </x-datatable.actions>
            @endif
        </div>

        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive">
            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
        </div>
    </div>
@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        var subCategories = @json($subCategories);

        $('#category_id').change(function(e) {
            var opts = '';
            var subCategory = subCategories.filter(function(item) {
                return item.category_id == e.target.value
            });

            subCategory.forEach(project => {
                opts += `<option value='${project.id}'>${project.category_name}</option>`
            })

            $('#sub_category').html('<option value="all">@lang("app.all")</option>' + opts);
            $("#sub_category").selectpicker("refresh");
        });

        $('#products-table').on('preXhr.dt', function(e, settings, data) {
            data['category_id'] = $('#category_id').val();
            data['sub_category_id'] = $('#sub_category').val();
            data['searchText'] = $('#search-text-field').val();
            data['unit_type_id'] = $('#unit_type_id').val();
            data['is_serialized'] = $('#is_serialized_filter').val();
            data['origin_type'] = $('#origin_type_filter').val();
            data['stock_status'] = $('#stock_status_filter').val();
        });

        const showTable = () => {
            window.LaravelDataTables["products-table"].draw(false);
        }

        $('#category_id, #sub_category, #unit_type_id, #is_serialized_filter, #origin_type_filter, #stock_status_filter').on('change keyup', function() {
            if ($('#category_id').val() != "all" || $('#sub_category').val() != "all" || $('#unit_type_id').val() != "all" || $('#is_serialized_filter').val() != "all" || $('#origin_type_filter').val() != "all" || $('#stock_status_filter').val() != "all") {
                $('#reset-filters').removeClass('d-none');
            } else {
                $('#reset-filters').addClass('d-none');
            }
            showTable();
        });

        $('#search-text-field').on('keyup', function() {
            if ($('#search-text-field').val() != "") {
                $('#reset-filters').removeClass('d-none');
            }
            showTable();
        });

        $('#reset-filters').click(function() {
            $('#category_id').val('all');
            $('#sub_category').html('<option value="all">@lang("app.all")</option>');
            $('#unit_type_id').val('all');
            $('#is_serialized_filter').val('all');
            $('#origin_type_filter').val('all');
            $('#stock_status_filter').val('all');
            $('#search-text-field').val('');
  
            $('.select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');

            showTable();
        });

        $('#quick-action-type').change(function() {
            const actionValue = $(this).val();
            if (actionValue != '') {
                $('#quick-action-apply').removeAttr('disabled');
                if (actionValue == 'change-purchase') {
                    $('.quick-action-field').addClass('d-none');
                    $('#change-status-action').removeClass('d-none');
                } else {
                    $('.quick-action-field').addClass('d-none');
                }
            } else {
                $('#quick-action-apply').attr('disabled', true);
                $('.quick-action-field').addClass('d-none');
            }
        });

        $('#quick-action-apply').click(function() {
            const actionValue = $('#quick-action-type').val();
            if (actionValue == 'delete') {
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
                        applyQuickAction();
                    }
                });
            } else {
                applyQuickAction();
            }
        });

        const applyQuickAction = () => {
            var rowdIds = $("#products-table input:checkbox:checked").map(function() {
                return $(this).val();
            }).get();

            var url = "{{ route('products.apply_quick_action') }}?row_ids=" + rowdIds;

            $.easyAjax({
                url: url,
                container: '#quick-action-form',
                type: "POST",
                disableButton: true,
                buttonSelector: "#quick-action-apply",
                data: $('#quick-action-form').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        showTable();
                        resetActionButtons();
                        deSelectAll();
                        $('#quick-action-form').hide();
                    }
                }
            })
        };

        $('body').on('click', '.productView', function() {
            let id = $(this).data('product-id');
            var url = "{{ route('products.show', ':id') }}";
            url = url.replace(':id', id);

            $(MODAL_DEFAULT + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_DEFAULT, url);
        });

        $('body').on('click', '.delete-table-row', function() {
            var id = $(this).data('product-id');
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
                    var url = "{{ route('products.destroy', ':id') }}";
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
                            if (response.status == "success") {
                                showTable();
                            }
                        }
                    });
                }
            });
        });
    </script>
@endpush
