@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')

    <x-filters.filter-box>
        <!-- DATE START -->
        <div class="select-box d-flex pr-2 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('modules.client.addedOn')</p>
            <div class="select-status d-flex">
                <input type="text" class="position-relative text-dark form-control border-0 p-2 text-left f-14 f-w-500 border-additional-grey"
                    id="datatableRange" placeholder="@lang('placeholders.dateRange')">
            </div>
        </div>
        <!-- DATE END -->

        <!-- CLIENT START -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.client')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="client" id="client" data-live-search="true" data-size="8">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($clients as $client)
                        <x-user-option :user="$client" />
                    @endforeach
                </select>
            </div>
        </div>

        <!-- CLIENT END -->

        <!-- SEARCH BY TASK START -->
        <div class="task-search d-flex  py-1 px-lg-3 px-0 border-right-grey align-items-center">
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
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('app.status')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" data-container="body" name="status" id="status">
                            <option value="all">@lang('app.all')</option>
                            <option value="active">@lang('app.active')</option>
                            <option value="deactive">@lang('app.inactive')</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('app.category')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" id="filter_category_id" data-live-search="true"
                            data-container="body" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ mb_ucwords($category->category_name) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize"
                    for="usr">@lang('modules.productCategory.subCategory')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" id="filter_sub_category_id" data-live-search="true"
                            data-container="body" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($subcategories as $subcategory)
                                <option value="{{ $subcategory->id }}">{{ $subcategory->category_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('app.project')</label>
                <div class="select-filter mb-4">
                    <div class="select-others select-filter-project">
                        <select class="form-control select-picker" id="project_id" data-live-search="true"
                            data-container="body" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->project_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize"
                    for="usr">@lang('modules.contracts.contractType')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" id="contract_type_id" data-live-search="true"
                            data-container="body" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($contracts as $contract)
                                <option value="{{ $contract->id }}">{{ $contract->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('app.country')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" id="country_id" data-live-search="true"
                            data-container="body" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($countries as $country)
                                <option value="{{ $country->id }}"
                                    data-content="<span class='flag-icon flag-icon-{{ strtolower($country->iso) }} flag-icon-squared'></span> {{ $country->nicename }}">{{ $country->nicename }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('app.verify') <i class="fa fa-question-circle" data-toggle="popover" data-placement="top" data-content="@lang('messages.clientFilterVerification')" data-html="true" data-trigger="hover"></i></label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" id="verification" data-container="body" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            <option value="yes">@lang('app.yes')</option>
                            <option value="no">@lang('app.no')</option>
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
        <div class="d-block d-lg-flex d-md-flex justify-content-between action-bar dd">

            <div id="table-actions" class="flex-grow-1 align-items-center">
                @if ($addClientPermission == 'all' || $addClientPermission == 'added' || $addClientPermission == 'both')
                    <x-forms.link-primary :link="route('clients.create')" class="mr-3 openRightModal float-left mb-2 mb-lg-0 mb-md-0" icon="plus">
                        @lang('app.add')
                        @lang('app.client')
                    </x-forms.link-primary>
                @endif

                @if ($addClientPermission == 'all' || $addClientPermission == 'added' || $addClientPermission == 'both')
                    <x-forms.link-secondary :link="route('clients.import')" class="mr-3 float-left mb-2 mb-lg-0 mb-md-0 d-sm-bloc" icon="file-upload">
                        @lang('app.importExcel')
                    </x-forms.link-secondary>
                @endif
            </div>

            <x-datatable.actions>
                <div class="select-status mr-3">
                    <select name="action_type" class="form-control select-picker" id="quick-action-type" disabled>
                        <option value="">@lang('app.selectAction')</option>
                        <option value="change-status">@lang('modules.tasks.changeStatus')</option>
                        <option value="delete">@lang('app.delete')</option>
                    </select>
                </div>
                <div class="select-status mr-3 d-none quick-action-field" id="change-status-action">
                    <select name="status" class="form-control select-picker">
                        <option value="deactive">@lang('app.inactive')</option>
                        <option value="active">@lang('app.active')</option>
                    </select>
                </div>
            </x-datatable.actions>


            <div class="btn-group ml-0 ml-lg-3 ml-md-3" role="group">
                <a href="{{ route('clients.index') }}" class="btn btn-secondary f-14 btn-active show-clients" data-toggle="tooltip"
                    data-original-title="@lang('app.menu.clients')"><i class="side-icon bi bi-list-ul"></i></a>

                <a href="javascript:;" class="btn btn-secondary f-14 show-unverified" data-toggle="tooltip"
                    data-original-title="@lang('modules.dashboard.verificationPending')"><i class="side-icon bi bi-person-x"></i></a>
            </div>

        </div>
        <!-- Add Task Export Buttons End -->

        <!-- Task Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive">

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}

        </div>
        <!-- Task Box End -->
    </div>
    <!-- CONTENT WRAPPER END -->

@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        (function() {
            var $body = $('body');
            var $table = $('#clients-table');
            var namespace = '.clientsIndex';

            $table.off('preXhr.dt').on('preXhr.dt', function(e, settings, data) {
                var dateRangePicker = $('#datatableRange').data('daterangepicker');
                var startDate = $('#datatableRange').val();
                var endDate = null;

                if (startDate == '') {
                    startDate = null;
                } else if (dateRangePicker) {
                    startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                    endDate = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
                }

                var status = $('#status').val() || 'all';
                var client = $('#client').val() || 'all';
                var category_id = $('#filter_category_id').val() || 'all';
                var sub_category_id = $('#filter_sub_category_id').val() || 'all';
                var project_id = $('#project_id').val() || 'all';
                var contract_type_id = $('#contract_type_id').val() || 'all';
                var country_id = $('#country_id').val() || 'all';
                var verification = $('#verification').val() || 'all';
                var searchText = $('#search-text-field').val();

                data['startDate'] = startDate;
                data['endDate'] = endDate;
                data['status'] = status;
                data['client'] = client;
                data['category_id'] = category_id;
                data['sub_category_id'] = sub_category_id;
                data['project_id'] = project_id;
                data['contract_type_id'] = contract_type_id;
                data['country_id'] = country_id;
                data['verification'] = verification;
                data['searchText'] = searchText;
            });

            var showTable = function() {
                var table = window.LaravelDataTables["clients-table"];
                if (table) {
                    table.draw(false);
                }
            }
            window.showTable = showTable;

            $body.off(namespace);

            $body.on('change' + namespace + ' keyup' + namespace, '#client, #status, #filter_category_id, #filter_sub_category_id, #project_id, #contract_type_id, #country_id, #verification', function() {
                var hasFilters = ($('#status').val() !== "all") ||
                                 ($('#client').val() !== "all") ||
                                 ($('#filter_category_id').val() !== "all") ||
                                 ($('#filter_sub_category_id').val() !== "all") ||
                                 ($('#project_id').val() !== "all") ||
                                 ($('#contract_type_id').val() !== "all") ||
                                 ($('#country_id').val() !== "all") ||
                                 ($('#verification').val() !== 'all');

                if (hasFilters) {
                    $('#reset-filters').removeClass('d-none');
                } else {
                    $('#reset-filters').addClass('d-none');
                }
                showTable();
            });

            $body.on('keyup' + namespace, '#search-text-field', function() {
                if ($(this).val() != "") {
                    $('#reset-filters').removeClass('d-none');
                }
                showTable();
            });

            $body.on('click' + namespace, '#reset-filters, #reset-filters-2', function() {
                $('#filter-form')[0].reset();
                $('.filter-box .select-picker').selectpicker("refresh");
                $('.show-unverified').removeClass("btn-active");
                $('.show-clients').addClass("btn-active");
                $('#reset-filters').addClass('d-none');
                showTable();
            });

            $body.on('change' + namespace, '#quick-action-type', function() {
                var actionValue = $(this).val();
                if (actionValue !== '') {
                    $('#quick-action-apply').removeAttr('disabled');
                    $('.quick-action-field').addClass('d-none');
                    if (actionValue === 'change-status') {
                        $('#change-status-action').removeClass('d-none');
                    }
                } else {
                    $('#quick-action-apply').attr('disabled', true);
                    $('.quick-action-field').addClass('d-none');
                }
            });

            $body.on('click' + namespace, '#quick-action-apply', function() {
                var actionValue = $('#quick-action-type').val();
                if (actionValue == 'delete') {
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

            $body.on('click' + namespace, '.verify-user', function() {
                var id = $(this).data('user-id');
                Swal.fire({
                    title: "@lang('messages.sweetAlertTitle')",
                    text: "@lang('messages.approvalWarning')",
                    icon: 'warning',
                    showCancelButton: true,
                    focusConfirm: false,
                    confirmButtonText: "@lang('app.approve')",
                    cancelButtonText: "@lang('app.cancel')",
                    customClass: { confirmButton: 'btn btn-primary mr-3', cancelButton: 'btn btn-secondary' },
                    showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        var url = "{{ route('clients.approve', ':id') }}".replace(':id', id);
                        var token = "{{ csrf_token() }}";
                        $.easyAjax({
                            type: 'POST',
                            url: url,
                            data: { '_token': token },
                            success: function(response) {
                                if (response.status == "success") {
                                    showTable();
                                    if (typeof syncGlobalStats === "function") {
                                        syncGlobalStats();
                                    }
                                }
                            }
                        });
                    }
                });
            });

            $body.on('click' + namespace, '.delete-table-row', function() {
                var id = $(this).data('user-id');
                var url = "{{ route('clients.finance_count', ':id') }}".replace(':id', id);
                var token = "{{ csrf_token() }}";
                $.easyAjax({
                    type: 'GET',
                    url: url,
                    data: { '_token': token },
                    success: function(response) {
                        if (response.status == "success") {
                            Swal.fire({
                                title: "@lang('messages.sweetAlertTitle')",
                                text: response.deleteClient + "@lang('messages.recoverRecord')",
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
                                    var destroyUrl = "{{ route('clients.destroy', ':id') }}".replace(':id', id);
                                    $.easyAjax({
                                        type: 'POST',
                                        url: destroyUrl,
                                        data: { '_token': token, '_method': 'DELETE' },
                                        success: function(response) {
                                            if (response.status == "success") {
                                                showTable();
                                                if (typeof syncGlobalStats === "function") {
                                                    syncGlobalStats();
                                                }
                                            }
                                        }
                                    });
                                }
                            });
                        }
                    }
                });
            });

            function applyQuickAction() {
                var rowdIds = $("#clients-table input:checkbox:checked").map(function() {
                    return $(this).val();
                }).get();

                var url = "{{ route('clients.apply_quick_action') }}?row_ids=" + rowdIds;

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
                            if (typeof resetActionButtons === 'function') resetActionButtons();
                            if (typeof deSelectAll === 'function') deSelectAll();
                            $('#quick-action-form').hide();
                            if (typeof syncGlobalStats === 'function') {
                                syncGlobalStats();
                            }
                        }
                    }
                });
            }
            window.applyQuickAction = applyQuickAction;

            $body.on('click' + namespace, '.show-unverified', function() {
                $('#verification').val('no');
                $('#verification').selectpicker('refresh');
                $(this).addClass('btn-active');
                $('#reset-filters').removeClass('d-none');
                showTable();
            });

            $body.on('click' + namespace, '.btn-group .btn-secondary', function() {
                $('.btn-secondary').removeClass('btn-active');
                $(this).addClass('btn-active');
            });

            @if (!is_null(request('start')) && !is_null(request('end')))
                $('#datatableRange').val('{{ request('start') }}' + ' @lang("app.to") ' + '{{ request('end') }}');
                var drp = $('#datatableRange').data('daterangepicker');
                if (drp) {
                    drp.setStartDate("{{ request('start') }}");
                    drp.setEndDate("{{ request('end') }}");
                }
                showTable();
            @endif

            document.addEventListener("turbo:before-cache", function cleanup() {
                $body.off(namespace);
                $table.off('preXhr.dt' + namespace);
                delete window.showTable;
                delete window.applyQuickAction;
                document.removeEventListener("turbo:before-cache", cleanup);
            }, { once: true });
        })();
    </script>
@endpush
