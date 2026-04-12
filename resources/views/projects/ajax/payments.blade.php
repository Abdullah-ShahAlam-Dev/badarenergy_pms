@php
$addPaymentPermission = user()->permission('add_payments');
@endphp

<!-- ROW START -->
<div class="row py-3 py-lg-5 py-md-5">
    <div class="col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4">
        <!-- Add Task Export Buttons Start -->
        <div class="d-flex justify-content-between">
            <div id="table-actions" class="align-items-center">
                @if (($addPaymentPermission == 'all' || $addPaymentPermission == 'added') && !$project->trashed())
                    <x-forms.link-primary :link="route('payments.create').'?project='.$project->id" class="mr-3 float-left openRightModal"
                        icon="plus" data-redirect-url="{{ url()->full() }}">
                        @lang('modules.payments.addPayment')
                    </x-forms.link-primary>
                @endif
            </div>

        </div>


        <form action="" id="filter-form">
            <div class="d-block d-lg-flex d-md-flex my-3">
                <!-- STATUS START -->
                <div class="select-box py-2 px-0 mr-3">
                    <x-forms.label :fieldLabel="__('app.status')" fieldId="status" />
                    <select class="form-control select-picker" name="status" id="status" data-live-search="true"
                        data-size="8">
                        <option value="all">@lang('app.all')</option>
                        <option value="complete">@lang('app.complete')</option>
                        <option value="pending">@lang('app.pending')</option>
                    </select>
                </div>
                <!-- STATUS END -->

                <!-- SEARCH BY TASK START -->
                <div class="select-box py-2 px-lg-2 px-md-2 px-0 mr-3">
                    <x-forms.label fieldId="status" class="d-none d-lg-block d-md-block" />
                    <div class="input-group bg-grey rounded">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-additional-grey">
                                <i class="fa fa-search f-13 text-dark-grey"></i>
                            </span>
                        </div>
                        <input type="text" class="form-control f-14 p-1 height-35 border" id="search-text-field"
                            placeholder="@lang('app.startTyping')">
                    </div>
                </div>
                <!-- SEARCH BY TASK END -->

                <!-- RESET START -->
                <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 mt-4">
                    <x-forms.button-secondary class="btn-xs d-none height-35" id="reset-filters" icon="times-circle">
                        @lang('app.clearFilters')
                    </x-forms.button-secondary>
                </div>
                <!-- RESET END -->
            </div>
        </form>


        <!-- Task Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}

            <x-datatable.actions>
                <div class="select-status mr-3 pl-3">
                    <select name="action_type" class="form-control select-picker" id="quick-action-type" disabled>
                        <option value="">@lang('app.selectAction')</option>
                        <option value="change-status">@lang('modules.tasks.changeStatus')</option>
                        <option value="delete">@lang('app.delete')</option>
                    </select>
                </div>
                <div class="select-status mr-3 d-none quick-action-field" id="change-status-action">
                    <select name="status" class="form-control select-picker">
                        <option value="complete">@lang('app.complete')</option>
                        <option value="pending">@lang('app.pending')</option>
                    </select>
                </div>
            </x-datatable.actions>
        </div>
        <!-- Task Box End -->
    </div>
</div>

@include('sections.datatable_js')

<script>
    (function() {
        var $body = $('body');
        var $table = $('#payments-table');
        var namespace = '.projectPayments';

        $table.off('preXhr.dt' + namespace).on('preXhr.dt' + namespace, function(e, settings, data) {
            var startDate = $('#start-date').val() || null;
            var endDate = $('#end-date').val() || null;
            var projectID = "{{ $project->id }}";
            var clientID = $('#clientID').val();
            var status = $('#status').val();
            var searchText = $('#search-text-field').val();

            data['clientID'] = clientID;
            data['projectID'] = projectID;
            data['status'] = status;
            data['startDate'] = startDate;
            data['endDate'] = endDate;
            data['searchText'] = searchText;
        });

        function showTable() {
            if (window.LaravelDataTables && window.LaravelDataTables["payments-table"]) {
                window.LaravelDataTables["payments-table"].draw(false);
            }
        }
        window.showTable = showTable;

        $body.off(namespace);

        $body.on('change' + namespace + ' keyup' + namespace, '#status', function() {
            if ($(this).val() != "all") {
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

        $body.on('click' + namespace, '#reset-filters,#reset-filters-2', function() {
            $('#filter-form')[0].reset();
            $('.filter-box .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');
            showTable();
        });

        $body.on('change' + namespace, '#quick-action-type', function() {
            const actionValue = $(this).val();
            if (actionValue != '') {
                $('#quick-action-apply').removeAttr('disabled');
                $('.quick-action-field').addClass('d-none');
                if (actionValue == 'change-status') {
                    $('#change-status-action').removeClass('d-none');
                }
            } else {
                $('#quick-action-apply').attr('disabled', true);
                $('.quick-action-field').addClass('d-none');
            }
        });

        $body.on('click' + namespace, '#quick-action-apply', function() {
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
                    customClass: { confirmButton: 'btn btn-primary mr-3', cancelButton: 'btn btn-secondary' },
                    showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) { applyQuickAction(); }
                });
            } else {
                applyQuickAction();
            }
        });

        $body.on('click' + namespace, '.delete-table-row', function() {
            var id = $(this).data('payment-id');
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
                    var url = "{{ route('payments.destroy', ':id') }}".replace(':id', id);
                    var token = "{{ csrf_token() }}";
                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: { '_token': token, '_method': 'DELETE' },
                        success: function(response) {
                            if (response.status == "success") { showTable(); }
                        }
                    });
                }
            });
        });

        function applyQuickAction() {
            var rowdIds = $("#payments-table input:checkbox:checked").map(function() {
                return $(this).val();
            }).get();

            var url = "{{ route('payments.apply_quick_action') }}?row_ids=" + rowdIds;

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
                    }
                }
            });
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
