<div class="row">
    <div class="col-sm-12">
        <x-form id="save-daily-report-data-form">
            <div class="add-client bg-white rounded">

                {{-- Header --}}
                <div class="p-20 border-bottom-grey">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 f-21 font-weight-normal text-capitalize">
                                <i class="fa fa-file-alt mr-2 text-primary"></i>
                                Submit Daily Report
                            </h4>
                            <p class="mb-0 text-lightest f-13 mt-1">
                                {{ \Carbon\Carbon::parse($reportDate)->format('l, ') }}
                                {{ \Carbon\Carbon::parse($reportDate)->format(company()->date_format) }}
                            </p>
                        </div>
                        <div class="text-right">
                            @if($totalMinutes > 0)
                                <div class="badge badge-light-blue f-15 p-2">
                                    <i class="fa fa-clock mr-1"></i>
                                    {{ floor($totalMinutes/60) }}h {{ $totalMinutes%60 }}m Logged Today
                                </div>
                            @else
                                <div class="badge badge-warning f-13 p-2">
                                    <i class="fa fa-exclamation-triangle mr-1"></i>
                                    No hours logged today
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="row p-20">
                    <div class="col-lg-12">
                        <input type="hidden" name="report_date" value="{{ $reportDate }}">

                        {{-- Auto-pulled Timelogs --}}
                        @if($timelogs->count() > 0)
                            <div class="mb-4">
                                <h6 class="font-weight-bold text-dark mb-2">
                                    <i class="fa fa-clock text-primary mr-1"></i>
                                    Today's Logged Activities (auto-loaded from Timesheet)
                                </h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Project</th>
                                                <th>Task</th>
                                                <th>Memo / Notes</th>
                                                <th class="text-right">Duration</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($timelogs as $log)
                                                <tr>
                                                    <td>
                                                        <span class="badge badge-soft-primary">
                                                            {{ $log->project->project_name ?? '—' }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $log->task->heading ?? '—' }}</td>
                                                    <td class="text-lightest">{{ $log->memo ?: '—' }}</td>
                                                    <td class="text-right font-weight-bold">{{ $log->hours }}</td>
                                                </tr>
                                            @endforeach
                                            <tr class="bg-light">
                                                <td colspan="3" class="text-right font-weight-bold">Total</td>
                                                <td class="text-right font-weight-bold text-primary">
                                                    {{ floor($totalMinutes/60) }}h {{ $totalMinutes%60 }}m
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-light border mb-4">
                                <i class="fa fa-info-circle text-primary mr-2"></i>
                                No timelog entries found for today. You can still describe your work in the summary below.
                            </div>
                        @endif

                        {{-- Work Summary --}}
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <div class="form-group my-3">
                                    <x-forms.label fieldId="summary" fieldRequired="true">
                                        Work Summary
                                    </x-forms.label>
                                    <p class="text-lightest f-12 mb-2">Describe the work you completed today.</p>
                                    <div id="summary"></div>
                                    <textarea name="summary" id="summary-text" class="d-none"></textarea>
                                </div>
                            </div>

                            <div class="col-md-12 mb-3">
                                <div class="form-group my-3">
                                    <x-forms.label fieldId="blockers">
                                        <i class="fa fa-exclamation-triangle text-warning mr-1"></i> Blockers / Challenges
                                    </x-forms.label>
                                    <p class="text-lightest f-12 mb-2">Any issues that blocked or slowed your progress today?</p>
                                    <div id="blockers"></div>
                                    <textarea name="blockers" id="blockers-text" class="d-none"></textarea>
                                </div>
                            </div>

                            <div class="col-md-12 mb-3">
                                <div class="form-group my-3">
                                    <x-forms.label fieldId="next_plan">
                                        <i class="fa fa-arrow-right text-success mr-1"></i> Plan for Tomorrow
                                    </x-forms.label>
                                    <p class="text-lightest f-12 mb-2">What are you planning to work on next?</p>
                                    <div id="next_plan"></div>
                                    <textarea name="next_plan" id="next_plan-text" class="d-none"></textarea>
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <x-forms.file-multiple fieldLabel="Attachments" fieldName="file" fieldId="daily-report-file-upload-dropzone" />
                                <input type="hidden" name="reportID" id="reportID">
                            </div>
                        </div>

                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-report-btn" class="mr-3" icon="check">
                        Submit Report
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('daily-reports.index')" class="border-0">
                        Cancel
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>
    </div>
</div>

<script>
    $(document).ready(function() {
        var $body = $('body');
        var drNamespace = '.dailyReportCreate';
        var dailyReportDropzone;

        // Simplified Quill configuration: Text and Links only
        var quillConfig = {
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline', 'strike'],
                    ['link'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['clean']
                ]
            },
            theme: 'snow'
        };

        ['#summary', '#blockers', '#next_plan'].forEach(function(selector) {
            if ($(selector).length > 0 && !$(selector).hasClass('ql-container')) {
                quillArray[selector] = new Quill(selector, quillConfig);
            }
        });

        // Dropzone initialization
        Dropzone.autoDiscover = false;
        dailyReportDropzone = new Dropzone("div#daily-report-file-upload-dropzone", {
            dictDefaultMessage: "{{ __('app.dragDrop') }}",
            url: "{{ route('daily-reports.store_file') }}",
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            paramName: "file",
            maxFilesize: 10,
            maxFiles: 10,
            autoProcessQueue: false,
            uploadMultiple: true,
            addRemoveLinks: true,
            parallelUploads: 10,
            init: function () {
                window.dailyReportDropzone = this;
            }
        });

        dailyReportDropzone.on('sending', function (file, xhr, formData) {
            var reportID = $('#reportID').val();
            formData.append('daily_report_id', reportID);
            $.easyBlockUI();
        });

        dailyReportDropzone.on('queuecomplete', function () {
            window.location.href = "{{ route('daily-reports.index') }}";
        });

        $body.off(drNamespace);

        $body.on('click' + drNamespace, '#save-report-btn', function () {
            // Read content from Quill editors
            var summaryHtml  = quillArray['#summary']  ? quillArray['#summary'].root.innerHTML  : '';
            var blockersHtml = quillArray['#blockers'] ? quillArray['#blockers'].root.innerHTML : '';
            var nextPlanHtml = quillArray['#next_plan'] ? quillArray['#next_plan'].root.innerHTML : '';

            $('#summary-text').val(summaryHtml);
            $('#blockers-text').val(blockersHtml);
            $('#next_plan-text').val(nextPlanHtml);

            $.easyAjax({
                url: "{{ route('daily-reports.store') }}",
                container: '#save-daily-report-data-form',
                type: 'POST',
                disableButton: true,
                blockUI: true,
                buttonSelector: '#save-report-btn',
                data: $('#save-daily-report-data-form').serialize(),
                success: function (response) {
                    if (response.status === 'success') {
                        if (dailyReportDropzone.getQueuedFiles().length > 0) {
                            $('#reportID').val(response.reportID);
                            dailyReportDropzone.processQueue();
                        } else {
                            if (typeof window.showTable === 'function') {
                                window.showTable();
                            }
                            if (response.redirectUrl) {
                                window.location.href = response.redirectUrl;
                            }
                        }
                    }
                }
            });
        });

        // Cleanup on Turbo cache
        window.addEventListener('turbo:before-cache', function drCleanup() {
            $body.off(drNamespace);
            ['#summary', '#blockers', '#next_plan'].forEach(function(selector) {
                if (typeof destory_editor === 'function') {
                    destory_editor(selector);
                }
            });
            window.removeEventListener('turbo:before-cache', drCleanup);
        }, { once: true });

        init(RIGHT_MODAL);
    });
</script>
