@push('styles')
    <style>
        /* Prevent bootstrap-select from overflowing parent container */
        .bootstrap-select,
        .bootstrap-select .dropdown-toggle {
            max-width: 100% !important;
        }
        /* Wrap selected items/images inside dropdown button if there are a few */
        .bootstrap-select .dropdown-toggle .filter-option-inner-inner {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            white-space: normal !important;
        }
    </style>
@endpush

<div class="col-lg-12 col-md-12 n-p-20">
    <x-form id="editSettings" method="POST">
        <div class="row p-20">
            <div class="col-md-12">
                <x-forms.label fieldId="reporter_ids" :fieldLabel="__('dailyreports::modules.dailyReports.selectReporters')"
                    fieldRequired="true">
                </x-forms.label>
                <x-forms.input-group>
                    <select class="form-control select-picker" name="reporter_ids[]" id="reporter_ids"
                        multiple data-live-search="true" data-size="8" 
                        data-actions-box="true" 
                        data-select-all-text="@lang('placeholders.selectAllText')"
                        data-deselect-all-text="@lang('placeholders.deselectAllText')"
                        data-selected-text-format="count > 3">
                        @foreach ($employees as $employee)
                            <option 
                                @if(in_array($employee->id, $selectedReporters)) selected @endif
                                data-content="<div class='d-inline-block mr-1'><img class='taskEmployeeImg rounded-circle' src='{{ $employee->image_url }}' ></div> {{ $employee->name }}"
                                value="{{ $employee->id }}">{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </x-forms.input-group>
                <p class="text-lightest mt-2">@lang('dailyreports::modules.dailyReports.reporterHelpText')</p>
            </div>
        </div>
    </x-form>
</div>

<x-slot name="action">
    <!-- Buttons Start -->
    <div class="w-100 border-top-grey">
        <x-setting-form-actions>
            <x-forms.button-primary id="save-form" class="mr-3" icon="check">@lang('app.save')
            </x-forms.button-primary>
        </x-setting-form-actions>
    </div>
    <!-- Buttons End -->
</x-slot>

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#reporter_ids').selectpicker('destroy').selectpicker({
                actionsBox: true,
                selectAllText: "@lang('placeholders.selectAllText')",
                deselectAllText: "@lang('placeholders.deselectAllText')"
            });

            $('#save-form').click(function() {
                var url = "{{ route('daily-report-settings.store') }}";
                $.easyAjax({
                    url: url,
                    container: '#editSettings',
                    type: "POST",
                    disableButton: true,
                    blockUI: true,
                    buttonSelector: "#save-form",
                    data: $('#editSettings').serialize(),
                    success: function(response) {
                        if (response.status == 'success') {
                            // success
                        }
                    }
                })
            });

            init('#editSettings');
        });
    </script>
@endpush
