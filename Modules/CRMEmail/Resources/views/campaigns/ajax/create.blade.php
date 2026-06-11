<div class="row">
    <div class="col-sm-12">
        <x-form id="create-campaign-form" method="POST" :action="route('crm-email-campaigns.store')">

            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-bold text-capitalize border-bottom-grey">
                    <i class="fa fa-paper-plane mr-2 text-primary"></i> Create Campaign
                </h4>

                <div class="row p-20">

                    {{-- Campaign Name --}}
                    <div class="col-md-12">
                        <x-forms.text
                            class="mr-0 mr-lg-2 mr-md-2"
                            :fieldLabel="'Campaign Name'"
                            fieldName="name"
                            fieldId="campaign-name"
                            fieldRequired="true"
                            :fieldValue="old('name')"
                            :fieldPlaceholder="'e.g. Summer Promo 2026'"
                        />
                    </div>

                    {{-- Template --}}
                    <div class="col-md-12 mt-3">
                        <x-forms.label fieldId="campaign-template-id" :fieldLabel="'Email Template'" fieldRequired="true"></x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="template_id" id="campaign-template-id" data-live-search="true">
                                <option value="">-- Select a Template --</option>
                                @foreach ($templates as $tmpl)
                                    <option value="{{ $tmpl->id }}" data-content="{{ $tmpl->content }}" data-subject="{{ $tmpl->subject }}">
                                        {{ $tmpl->title }}
                                    </option>
                                @endforeach
                            </select>
                            <x-slot name="append">
                                <button type="button" id="preview-template-btn" class="btn btn-outline-secondary border-grey d-none" title="Preview Template">
                                    <i class="fa fa-eye"></i> Preview
                                </button>
                            </x-slot>
                        </x-forms.input-group>
                    </div>

                    {{-- Segment --}}
                    <div class="col-md-12 mt-3">
                        <x-forms.select
                            fieldId="campaign-segment-id"
                            :fieldLabel="'Recipient Segment'"
                            fieldName="segment_id"
                            fieldRequired="true"
                        >
                            <option value="">-- Select a Segment --</option>
                            @foreach ($segments as $seg)
                                <option value="{{ $seg->id }}">{{ $seg->name }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    {{-- Schedule Date (Optional) --}}
                    <div class="col-md-12 mt-3">
                        <x-forms.datepicker
                            fieldId="campaign-scheduled-at"
                            :fieldLabel="'Schedule Date (Optional)'"
                            fieldName="scheduled_at"
                            :fieldValue="old('scheduled_at')"
                            :fieldPlaceholder="'Leave empty to launch manually'"
                        />
                    </div>

                    {{-- Email Body (Quill Editor) --}}
                    <div class="col-md-12 mt-3">
                        <label class="f-14 text-dark-grey mb-12 w-100" for="email-body-container">
                            Email Body
                            <span class="text-muted f-12">(Pre-filled from template, can be customised)</span>
                        </label>

                        <div id="email-body-container" style="height: 300px;"></div>
                        <textarea name="email_body" id="campaign-email-body" class="d-none"></textarea>
                    </div>

                </div>

                {{-- Form Actions --}}
                <div class="set-time mt-4 p-20 border-top-grey">
                    <x-forms.button-primary id="save-campaign-btn" class="mr-3" icon="check">
                        Save Campaign
                    </x-forms.button-primary>
                    <a class="btn btn-cancel f-14 font-weight-normal text-dark-grey" href="{{ route('crm-email-campaigns.index') }}">
                        @lang('app.cancel')
                    </a>
                </div>
            </div>

        </x-form>
    </div>
</div>

<script>
    $(document).ready(function () {
        var quill = quillImageLoad('#email-body-container');

        // Pre-fill editor content when a template is selected
        $('#campaign-template-id').on('change', function () {
            var selected = $(this).find('option:selected');
            var templateId = selected.val();
            var content  = selected.data('content') || '';
            if (quill && content) {
                quill.clipboard.dangerouslyPasteHTML(content);
            }
            if (templateId) {
                $('#preview-template-btn').removeClass('d-none');
            } else {
                $('#preview-template-btn').addClass('d-none');
            }
        });

        // Preview Template
        $('#preview-template-btn').on('click', function() {
            var templateId = $('#campaign-template-id').val();
            if (templateId) {
                var url = "{{ route('crm-email-templates.preview', ':id') }}".replace(':id', templateId);
                $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
                $.ajaxModal(MODAL_LG, url);
            }
        });

        // Sync Quill content to hidden textarea before form submit
        $('#create-campaign-form').on('submit', function () {
            $('#campaign-email-body').val(quill ? quill.root.innerHTML : '');
        });

        $('#save-campaign-btn').on('click', function () {
            $('#campaign-email-body').val(quill ? quill.root.innerHTML : '');

            $.easyAjax({
                url: "{{ route('crm-email-campaigns.store') }}",
                container: '#create-campaign-form',
                type: 'POST',
                redirect: true,
                disableButton: true,
                blockUI: true,
                buttonSelector: '#save-campaign-btn',
                data: $('#create-campaign-form').serialize()
            });
        });

        init(RIGHT_MODAL);
    });
</script>
