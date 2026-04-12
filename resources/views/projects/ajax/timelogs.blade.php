@php
$addTimelogPermission = user()->permission('add_timelogs');
@endphp

<!-- ROW START -->
<div class="row py-3 py-lg-5 py-md-5">
    <div class="col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4">
        <!-- Add Task Export Buttons Start -->
        <div class="d-flex" id="table-actions">
            @if (($addTimelogPermission == 'all' || $addTimelogPermission == 'added') && !$project->trashed())
                <x-forms.link-primary :link="route('timelogs.create').'?default_project='.$project->id"
                    class="mr-3 openRightModal float-left" icon="plus">
                    @lang('modules.timeLogs.logTime')
                </x-forms.link-primary>
            @endif
        </div>

        <div class="d-flex justify-content-between">

            <form action="" class="flex-grow-1 " id="filter-form">
                <div class="d-block d-lg-flex d-md-flex my-3">
                    <!-- STATUS START -->
                    <div class="select-box py-2 px-0 mr-3">
                        <x-forms.label :fieldLabel="__('app.status')" fieldId="status" />
                        <select class="form-control select-picker" name="status" id="status" data-live-search="true"
                            data-size="8">
                            <option value="all">@lang('app.all')</option>
                            <option value="1">@lang('app.approved')</option>
                            <option value="0">@lang('app.pending')</option>
                            <option value="2">@lang('app.active')</option>
                        </select>
                    </div>
                    <!-- STATUS END -->
                    <!-- STATUS START -->
                    <div class="select-box py-2 px-0 mr-3">
                        <x-forms.label :fieldLabel="__('app.invoiceGenerate')" fieldId="leave_type" />
                        <select class="form-control select-picker" name="invoice_generate" id="invoice_generate"
                            data-live-search="true" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            <option value="1">@lang('app.yes')</option>
                            <option value="0">@lang('app.no')</option>
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
                        <x-forms.button-secondary class="btn-xs d-none height-35 mt-2" id="reset-filters" icon="times-circle">
                            @lang('app.clearFilters')
                        </x-forms.button-secondary>
                    </div>
                    <!-- RESET END -->
                </div>
            </form>

            <x-datatable.actions class="mt-5">
                <div class="select-status mr-3 pl-3">
                    <select name="action_type" class="form-control select-picker" id="quick-action-type" disabled>
                        <option value="">@lang('app.selectAction')</option>
                        <option value="change-status">@lang('modules.tasks.changeStatus')</option>
                        <option value="delete">@lang('app.delete')</option>
                    </select>
                </div>
                <div class="select-status mr-3 d-none quick-action-field" id="change-status-action">
                    <select name="status" class="form-control select-picker">
                        <option value="0">@lang('app.pending')</option>
                        <option value="1">@lang('app.approve')</option>
                    </select>
                </div>
            </x-datatable.actions>

        </div>

        <!-- Task Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}

        </div>
        <!-- Task Box End -->
    </div>
</div>

@include('sections.datatable_js')


<script>
    (function() {
        var $body = $('body');
        var $table = $('#timelogs-table');
        var namespace = '.projectTimelogs';

        $table.off('preXhr.dt' + namespace).on('preXhr.dt' + namespace, function(e, settings, data) {
            var projectID = "{{ $project->id }}";
            var approved = $('#status').val();
            var invoice = $('#invoice_generate').val();
            var searchText = $('#search-text-field').val();

            data['projectId'] = projectID;
            data['approved'] = approved;
            data['invoice'] = invoice;
            data['searchText'] = searchText;
            data['project_admin'] = "{{ ($project->project_admin == user()->id) ? 1 : 0 }}";
        });

        function showTable() {
            if (window.LaravelDataTables && window.LaravelDataTables["timelogs-table"]) {
                window.LaravelDataTables["timelogs-table"].draw(false);
            }
        }
        window.showTable = showTable;

        $body.off(namespace);

        $body.on('change' + namespace + ' keyup' + namespace, '#project_id, #employee, #status, #invoice_generate', function() {
            if ($('#status').val() != "all" || $('#invoice_generate').val() != "all") {
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
            $('#filter-form .select-picker').selectpicker("refresh");
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
            var id = $(this).data('time-id');
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
                    var url = "{{ route('timelogs.destroy', ':id') }}".replace(':id', id);
                    var token = "{{ csrf_token() }}";
                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        blockUI: true,
                        data: { '_token': token, '_method': 'DELETE' },
                        success: function(response) {
                            if (response.status == "success") { showTable(); }
                        }
                    });
                }
            });
        });

        $body.on('click' + namespace, '.stop-active-timer, .approve-timelog', function() {
            var id = $(this).data('time-id');
            var action = $(this).hasClass('stop-active-timer') ? 'stop_timer' : 'approve_timelog';
            var url = (action === 'stop_timer') ? "{{ route('timelogs.stop_timer', ':id') }}".replace(':id', id) : "{{ route('timelogs.approve_timelog', ':id') }}".replace(':id', id);
            var token = '{{ csrf_token() }}';
            $.easyAjax({
                url: url,
                type: "POST",
                data: { id: id, timeId: id, _token: token },
                success: function() { showTable(); }
            });
        });

        function applyQuickAction() {
            var rowdIds = $("#timelogs-table input:checkbox:checked").map(function() {
                return $(this).val();
            }).get();

            var url = "{{ route('timelogs.apply_quick_action') }}?row_ids=" + rowdIds;

            $.easyAjax({
                url: url,
                container: '#quick-action-form',
                type: "POST",
                disableButton: true,
                buttonSelector: "#quick-action-apply",
                data: $('#quick-action-form').serialize(),
                blockUI: true,
                success: function(response) {
                    if (response.status == 'success') {
                        showTable();
                        if (typeof resetActionButtons === 'function') resetActionButtons();
                        if (typeof deSelectAll === 'function') deSelectAll();
                    }
                }
            })
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
