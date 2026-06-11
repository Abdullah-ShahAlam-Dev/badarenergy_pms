<div class="row">
    <div class="col-sm-12">
        <x-form id="edit-campaign-form" method="PUT" :action="route('crm-email-campaigns.update', $campaign->id)">

            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-bold text-capitalize border-bottom-grey">
                    <i class="fa fa-edit mr-2 text-warning"></i> Edit Campaign
                </h4>

                <div class="row p-20">

                    {{-- Campaign Name --}}
                    <div class="col-md-12">
                        <x-forms.text
                            class="mr-0 mr-lg-2 mr-md-2"
                            :fieldLabel="'Campaign Name'"
                            fieldName="name"
                            fieldId="campaign-name-edit"
                            fieldRequired="true"
                            :fieldValue="$campaign->name"
                        />
                    </div>

                    {{-- Template --}}
                    <div class="col-md-12 mt-3">
                        <x-forms.label fieldId="campaign-template-id-edit" :fieldLabel="'Email Template'" fieldRequired="true"></x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="template_id" id="campaign-template-id-edit" data-live-search="true">
                                <option value="">-- Select a Template --</option>
                                @foreach ($templates as $tmpl)
                                    <option value="{{ $tmpl->id }}" data-content="{{ $tmpl->content }}" data-subject="{{ $tmpl->subject }}"
                                        {{ $campaign->template_id == $tmpl->id ? 'selected' : '' }}>
                                        {{ $tmpl->title }}
                                    </option>
                                @endforeach
                            </select>
                            <x-slot name="append">
                                <button type="button" id="preview-template-btn-edit" class="btn btn-outline-secondary border-grey {{ $campaign->template_id ? '' : 'd-none' }}" title="Preview Template">
                                    <i class="fa fa-eye"></i> Preview
                                </button>
                            </x-slot>
                        </x-forms.input-group>
                    </div>

                    {{-- Segment --}}
                    <div class="col-md-12 mt-3">
                        <x-forms.select
                            fieldId="campaign-segment-id-edit"
                            :fieldLabel="'Recipient Segment'"
                            fieldName="segment_id"
                            fieldRequired="true"
                        >
                            <option value="">-- Select a Segment --</option>
                            @foreach ($segments as $seg)
                                <option value="{{ $seg->id }}" {{ $campaign->segment_id == $seg->id ? 'selected' : '' }}>
                                    {{ $seg->name }}
                                </option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    {{-- Schedule Date --}}
                    <div class="col-md-12 mt-3">
                        <x-forms.datepicker
                            fieldId="campaign-scheduled-at-edit"
                            :fieldLabel="'Schedule Date (Optional)'"
                            fieldName="scheduled_at"
                            :fieldValue="$campaign->scheduled_at ? $campaign->scheduled_at->format('Y-m-d') : ''"
                            :fieldPlaceholder="'Leave empty to launch manually'"
                        />
                    </div>

                    {{-- Email Body --}}
                    <div class="col-md-12 mt-3">
                        <label class="f-14 text-dark-grey mb-12 w-100" for="email-body-container-edit">
                            Email Body
                        </label>
                        <div id="email-body-container-edit" style="height: 300px;"></div>
                        <textarea name="email_body" id="campaign-email-body-edit" class="d-none"></textarea>
                    </div>

                </div>

                <div class="set-time mt-4 p-20 border-top-grey">
                    <x-forms.button-primary id="update-campaign-btn" class="mr-3" icon="check">
                        Update Campaign
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
        var initialContent = {!! json_encode($campaign->email_body) !!};
        var quill = quillImageLoad('#email-body-container-edit');

        if (quill && initialContent) {
            quill.clipboard.dangerouslyPasteHTML(initialContent);
        }

        // Change template & prefill content/toggle preview button
        $('#campaign-template-id-edit').on('change', function () {
            var selected = $(this).find('option:selected');
            var templateId = selected.val();
            var content  = selected.data('content') || '';
            if (quill && content) {
                quill.clipboard.dangerouslyPasteHTML(content);
            }
            if (templateId) {
                $('#preview-template-btn-edit').removeClass('d-none');
            } else {
                $('#preview-template-btn-edit').addClass('d-none');
            }
        });

        // Preview Template
        $('#preview-template-btn-edit').on('click', function() {
            var templateId = $('#campaign-template-id-edit').val();
            if (templateId) {
                var url = "{{ route('crm-email-templates.preview', ':id') }}".replace(':id', templateId);
                $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
                $.ajaxModal(MODAL_LG, url);
            }
        });

        $('#update-campaign-btn').on('click', function () {
            $('#campaign-email-body-edit').val(quill ? quill.root.innerHTML : '');

            $.easyAjax({
                url: "{{ route('crm-email-campaigns.update', $campaign->id) }}",
                container: '#edit-campaign-form',
                type: 'PUT',
                redirect: true,
                disableButton: true,
                blockUI: true,
                buttonSelector: '#update-campaign-btn',
                data: $('#edit-campaign-form').serialize()
            });
        });

        init(RIGHT_MODAL);
    });
</script>
