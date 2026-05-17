{{-- this plugin is used only in leaves create form --}}
@php
    $addLeadAgentPermission = user()->permission('manage_leave_setting');
    $approveRejectPermission = user()->permission('approve_or_reject_leaves');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/bootstrap-datepicker3.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/css/daterangepicker.css') }}">

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-lead-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('modules.leaves.assignLeave')</h4>
                <div class="row p-20">

                    <div class="col-lg-4 col-md-6">
                        @if (isset($defaultAssign))
                            <x-forms.label class="mt-3" fieldId="" :fieldLabel="__('app.name')" fieldRequired="true">
                            </x-forms.label>
                            <input type="hidden" name="user_id" id="user_id" value="{{ $defaultAssign->id }}">
                            <input type="text" value="{{ $defaultAssign->name }}"
                                class="form-control height-35 f-15 readonly-background" readonly>
                        @else
                            <x-forms.select fieldId="user_id" :fieldLabel="__('modules.messages.chooseMember')" fieldName="user_id" search="true"
                                fieldRequired="true">
                                <option value="">--</option>
                                @foreach ($employees as $employee)
                                    <x-user-option :user="$employee" :selected="request()->has('default_assign') &&
                                        request('default_assign') == $employee->id" />
                                @endforeach
                            </x-forms.select>
                        @endif
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.label class="mt-3" fieldId="" :fieldLabel="__('modules.leaves.leaveType')" fieldRequired="true">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="leave_type_id" id="leave_type_id"
                                data-live-search="true">
                                <option value="">--</option>
                                @if (isset($leaveTypes))
                                    @foreach ($leaveTypes as $leaveType)
                                        <option value="{{ $leaveType->id }}">{{ mb_ucwords($leaveType->type_name) }}
                                        </option>
                                    @endforeach
                                @endif

                                @if (isset($leaveQuotas))
                                    @foreach ($leaveQuotas as $leave)
                                        @php
                                            $leaveType = new \App\Models\LeaveType();
                                        @endphp

                                        @if ($leave->employeeLeave >= 0)
                                            @if($leaveType->leaveTypeCodition($leave, $userRole))
                                                    <option value="{{ $leave->id }}">{{ mb_ucwords($leave->type_name) }}</option>
                                            @endif
                                        @endif
                                    @endforeach
                                @endif
                            </select>

                            @if ($addLeadAgentPermission == 'all' || $addLeadAgentPermission == 'added')
                                <x-slot name="append">
                                    <button type="button"
                                        class="btn btn-outline-secondary border-grey add-lead-type2"
                                        data-toggle="tooltip" data-original-title="{{ __('modules.leaves.addLeaveType') }}">@lang('app.add')</button>
                                </x-slot>
                            @endif
                        </x-forms.input-group>
                    </div>

                    @if ($approveRejectPermission == 'all')
                        <div class="col-lg-4 col-md-6">
                            <x-forms.select fieldId="status" :fieldLabel="__('app.status')" fieldName="status" search="true">
                                <option value="pending">@lang('app.pending')</option>
                                <option value="approved">@lang('app.approved')</option>
                            </x-forms.select>
                        </div>
                    @endif

                    <div class="col-12 d-none" id="leave-quota-container">
                        <!-- Dynamic Leave Quota Panel will be loaded here -->
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="form-group my-3">
                            <label class="f-14 text-dark-grey mb-12 w-100" for="usr">@lang('modules.leaves.selectDuration')</label>
                            <div class="d-block d-lg-flex d-md-flex">
                                <x-forms.radio fieldId="duration_single" :fieldLabel="__('modules.leaves.single')" fieldName="duration"
                                    fieldValue="single" checked="true">
                                </x-forms.radio>
                                <x-forms.radio fieldId="duration_multiple" :fieldLabel="__('modules.leaves.multiple')" fieldValue="multiple"
                                    fieldName="duration"></x-forms.radio>

                                <x-forms.radio fieldId="half_day_first" :fieldLabel="__('modules.leaves.firstHalf')" fieldName="duration"
                                    fieldValue="first_half">
                                </x-forms.radio>
                                <x-forms.radio fieldId="half_day_second" :fieldLabel="__('modules.leaves.secondHalf')" fieldValue="second_half"
                                    fieldName="duration"></x-forms.radio>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6 single_date_div">
                        <x-forms.text :fieldLabel="__('app.date')" fieldName="leave_date" fieldId="single_date" :fieldPlaceholder="__('app.date')"
                            :fieldValue="now(company()->timezone)->translatedFormat(company()->date_format)" />
                    </div>

                    <div class="col-lg-4 col-md-6 d-none multi_date_div">
                        <x-forms.text :fieldLabel="__('messages.selectMultipleDates')" fieldName="multi_date" fieldId="multi_date" :fieldPlaceholder="__('messages.selectMultipleDates')"
                            :fieldValue="now(company()->timezone)->translatedFormat(company()->date_format)" />
                    </div>
                    <div class="col-lg-4 col-md-6 date-range-days mt-5">
                        <p id="users" class="mt-2 badge badge-secondary"></p>

                    </div>
                    <div class="col-md-12">
                        <div class="form-group my-3">
                            <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.leaves.reason')" fieldName="reason"
                                fieldId="reason" fieldRequired="true" :fieldPlaceholder="__('placeholders.leave.reason')">
                            </x-forms.textarea>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <x-forms.file-multiple class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.menu.addFile')" fieldName="file" :popover="__('messages.leaveFileMessage')" fieldId="leave-file-upload-dropzone" />
                        <input type="hidden" name="leaveID" id="leaveID">
                    </div>

                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-leave-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('leaves.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>

    </div>
</div>

{{-- this plugin is used only in leaves create form --}}
<script src="{{ asset('vendor/jquery/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('vendor/jquery/dropzone.min.js') }}"></script>
<script src="{{ asset('vendor/jquery/daterangepicker.min.js')}}" defer=""></script>


<script>
    function initLeaveCreate() {
        if (typeof window.jQuery !== 'undefined' && typeof $.easyAjax === 'function') {
            (function() {
                const $leaveBody = $('body');
                const namespace = '.leaveCreate';

                Dropzone.autoDiscover = false;
                //Dropzone class
                if ($('#leave-file-upload-dropzone').length > 0 && typeof Dropzone !== 'undefined') {
                    if (typeof myDropzone !== 'undefined') {
                        myDropzone.destroy();
                    }
                    myDropzone = new Dropzone("div#leave-file-upload-dropzone", {
                        dictDefaultMessage: "{{ __('app.dragDrop') }}",
                        url: "{{ route('leave-files.store') }}",
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        paramName: "file",
                        maxFilesize: DROPZONE_MAX_FILESIZE,
                        maxFiles: DROPZONE_MAX_FILES,
                        autoProcessQueue: false,
                        uploadMultiple: true,
                        addRemoveLinks: true,
                        parallelUploads: DROPZONE_MAX_FILES,
                        acceptedFiles: DROPZONE_FILE_ALLOW,
                        init: function() {
                            myDropzone = this;
                        }
                    });

                    myDropzone.on('sending', function(file, xhr, formData) {
                        var ids = $('#leaveID').val();
                        formData.append('leave_id', ids);
                    });
                    myDropzone.on('uploadprogress', function() {
                        $.easyBlockUI();
                    });
                    myDropzone.on('queuecomplete', function() {
                        var redirect_url = $('#redirect_url').val();
                        if (redirect_url != '') {
                            window.location.href = decodeURIComponent(redirect_url);
                        }
                        window.location.href = "{{ route('leaves.index') }}"
                    });
                    myDropzone.on('removedfile', function () {
                        var grp = $('div#file-upload-dropzone').closest(".form-group");
                        var label = $('div#file-upload-box').siblings("label");
                        $(grp).removeClass("has-error");
                        $(label).removeClass("is-invalid");
                    });
                    myDropzone.on('error', function (file, message) {
                        myDropzone.removeFile(file);
                        var grp = $('div#file-upload-dropzone').closest(".form-group");
                        var label = $('div#file-upload-box').siblings("label");
                        $(grp).find(".help-block").remove();
                        var helpBlockContainer = $(grp);

                        if (helpBlockContainer.length == 0) {
                            helpBlockContainer = $(grp);
                        }

                        helpBlockContainer.append('<div class="help-block invalid-feedback">' + message + '</div>');
                        $(grp).addClass("has-error");
                        $(label).addClass("is-invalid");

                    });
                }

                $leaveBody.off(namespace);

                getDate();
                const dp1 = datepicker('#single_date', {
                    onSelect: function () {
                        getDate();
                    },
                    position: 'bl',
                    ...datepickerConfig
                });

                if (typeof $().daterangepicker === 'function') {
                    $('#multi_date').daterangepicker({
                        linkedCalendars: false,
                        multidate: true,
                        todayHighlight: true,
                        format: 'yyyy-mm-d'
                    });
                }

                $leaveBody.on('change' + namespace, '#multi_date', function() {
                    var dates = $(this).val();
                    if (dates && dates.indexOf(' - ') !== -1) {
                        var startDate = moment(new Date(dates.split(' - ')[0]));
                        var endDate = moment(new Date(dates.split(' - ')[1]));
                        var totalDays = endDate.diff(startDate, 'days')+1;
                        $('.date-range-days').html(totalDays +' Days Selected');
                    }
                });

                $leaveBody.on('change' + namespace, 'input[type=radio][name=duration]', function() {
                    if (this.value == 'multiple') {
                        if (typeof $().daterangepicker === 'function') {
                            $('#multi_date').daterangepicker('clearDates').daterangepicker({
                                linkedCalendars: false,
                                multidate: true,
                                todayHighlight: true,
                                format: 'yyyy-mm-d'
                            });
                        }
                    }
                    
                    ($('input[name=duration]:checked').val() == "multiple") ? getDate('multiple') : getDate();

                    $(this).val() == 'multiple' ? $('.multi_date_div').removeClass('d-none') : $(
                        '.multi_date_div').addClass('d-none');
                    $(this).val() == 'multiple' ? $('.single_date_div').addClass('d-none') : $(
                        '.single_date_div').removeClass('d-none');
                });

                if ($('#user_id').val() != '') {
                    setMinDate($('#user_id').val());
                }

                $leaveBody.on('change' + namespace, '#user_id', function(e) {
                    const employeeID = $(this).val();
                    setMinDate(employeeID);
                    getEmployeeLeaveTypes(employeeID);
                });

                function setMinDate(employeeID) {
                    var employees = @json($employees);
                    var employee = employees.filter(function(item) {
                        return item.id == employeeID;
                    });

                    if(employees.length > 0 && employee[0] !== undefined && employee[0].employee_detail)
                    {
                        var minDate = new Date(employee[0].employee_detail.joining_date);
                        if (typeof dp1.setMin === 'function') {
                            dp1.setMin(minDate);
                        }
                        if (typeof $().daterangepicker === 'function') {
                            $('#multi_date').daterangepicker('setStartDate', minDate);
                        }
                    }
                }

                $leaveBody.on('click' + namespace, '#save-leave-form', function() {
                    var startDate, endDate;
                    if ($('input[name=duration]:checked').val() == 'multiple') {
                        var dateRange = $('#multi_date').data('daterangepicker');
                        startDate = dateRange.startDate.format('{{ company()->moment_date_format }}');
                        endDate = dateRange.endDate.format('{{ company()->moment_date_format }}');
                    } else {
                        startDate = $('#single_date').val();
                        endDate = $('#single_date').val();
                    }

                    const url = "{{ route('leaves.store') }}";

                    $.easyAjax({
                        url: url,
                        container: '#save-lead-data-form',
                        type: "POST",
                        disableButton: true,
                        blockUI: true,
                        buttonSelector: "#save-leave-form",
                        data: $('#save-lead-data-form').serialize()+'&multiStartDate='+startDate + '&multiEndDate='+endDate,
                        success: function(response) {
                            if (response.status == 'success') {
                                if (typeof myDropzone !== 'undefined' && myDropzone.getQueuedFiles().length > 0) {
                                    $('#leaveID').val(response.leaveID);
                                    myDropzone.processQueue();
                                } else {
                                    window.location.href = response.redirectUrl;
                                }
                            }
                        }
                    });
                });

                $leaveBody.on('click' + namespace, '.add-lead-type2', function() {
                    var url = "{{ route('leaveType.create') }}";
                    $(MODAL_XL + ' ' + MODAL_HEADING).html('...');
                    $.ajaxModal(MODAL_XL, url);
                });

                function getDate(value) {
                    let url = "{{ route('leaves.date') }}";
                    let date = $('#single_date').val();

                    if (value == 'multiple') {
                        date = '';
                    }

                    $.easyAjax({
                        type: 'GET',
                        url: url,
                        container: '#save-lead-data-form',
                        data: {
                            'date': date
                        },
                        success: function(response) {
                            if(response.status == 'success'){
                                if(response.users > 0 && response.users < 2){
                                    $('#users').text(response.users+' @lang('modules.leaves.employeeOnLeave')');
                                }else if(response.users > 0){
                                    $('#users').text(response.users+' @lang('modules.leaves.employeesOnLeave')');
                                }else{
                                    $('#users').text('');
                                }
                            }
                        }
                    });
                }

                init(RIGHT_MODAL);

    $(document).ready(function() {
        if ($('#user_id').val() != '') {
            getEmployeeLeaveTypes();
        }
    });

                window.addEventListener('turbo:before-cache', function cleanup() {
                    $leaveBody.off(namespace);
                    window.removeEventListener('turbo:before-cache', cleanup);
                }, { once: true });
            })();
        } else {
            setTimeout(initLeaveCreate, 50);
        }
    }

    function getEmployeeLeaveTypes(employeeID) {
        let id = employeeID || $('#user_id').val() || 0;
        var url = "{{ route('employee-leaves.employee_leave_types', ':id') }}";
        url = url.replace(':id', id);

        $.easyAjax({
            url: url,
            type: "GET",
            container: '#save-lead-data-form',
            blockUI: true,
            success: function(response) {
                if (response.data) {
                    $('#leave_type_id').html(response.data);
                    $('#leave_type_id').selectpicker('refresh');
                }
                if (response.quotaHtml) {
                    $('#leave-quota-container').html(response.quotaHtml).removeClass('d-none');
                } else {
                    $('#leave-quota-container').addClass('d-none').html('');
                }
            }
        });
    }

    initLeaveCreate();

    if ($('#user_id').val() != '') {
        getEmployeeLeaveTypes($('#user_id').val());
    }
</script>
