<div class="col-xl-12 col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4 ">
    <div class="row">
        <div class="col-sm-12">
            <x-alert type="info" icon="info-circle">
                Edit the templates for custom email notifications. Use shortcodes to dynamically insert data.
            </x-alert>
        </div>
    </div>

    @foreach($emailTemplates as $template)
        <div class="row mb-4 p-4 border rounded">
            <div class="col-md-12">
                <h4 class="mb-3">{{ $template->event_name }}</h4>
            </div>
            
            <div id="updateTemplateForm-{{ $template->id }}" class="col-md-12">
                @csrf
                <input type="hidden" name="template_id" value="{{ $template->id }}">
                
                <div class="row">
                    <!-- Editor Side -->
                    <div class="col-md-6 border-right">
                        <div class="mb-3">
                            <x-forms.text class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.subject')"
                                fieldRequired="true" :fieldPlaceholder="__('placeholders.name')" fieldName="subject"
                                :fieldId="'subject-'.$template->id" :fieldValue="$template->subject" />
                        </div>

                        <div class="mb-3">
                            <x-forms.label :fieldId="'content-'.$template->id" :fieldLabel="__('app.description')" />
                            <div id="content-{{ $template->id }}" style="height: 250px;">{!! $template->content !!}</div>
                            <textarea name="content" id="hidden-content-{{ $template->id }}" class="d-none"></textarea>
                        </div>

                        <div class="mb-3">
                            <div class="text-muted f-12 mb-2">
                                <strong>Available Shortcodes:</strong> {task_heading}, {task_short_code}, {due_date}, {notifiable_name}, {url}
                            </div>
                        </div>

                        <div>
                            <button type="button" class="btn btn-primary save-template" data-id="{{ $template->id }}"><i class="fa fa-check mr-1"></i> @lang('app.save')</button>
                        </div>
                    </div>

                    <!-- Preview Side -->
                    <div class="col-md-6 pl-4">
                        <h5 class="mb-3 text-secondary"><i class="fa fa-eye mr-2"></i>Live Email Preview</h5>
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 id="preview-subject-{{ $template->id }}" class="font-weight-bold mb-3 border-bottom pb-2"></h6>
                                <div id="preview-body-{{ $template->id }}"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    @if($emailTemplates->isEmpty())
        <div class="row border rounded p-4">
            <div class="col-md-12">
                <h4 class="mb-3">Create New Template</h4>
                <div id="createTemplateForm">
                    @csrf
                    <div class="row">
                        <!-- Editor Side -->
                        <div class="col-md-6 border-right">
                            <div class="mb-3">
                                <x-forms.text :fieldLabel="'Event Name'" fieldName="event_name" fieldId="event_name" fieldValue="NewCcTask" fieldRequired="true" />
                            </div>
                            <div class="mb-3">
                                <x-forms.text :fieldLabel="__('app.subject')" fieldName="subject" fieldId="subject" fieldValue="CC: {task_heading}" fieldRequired="true" />
                            </div>
                            <div class="mb-3">
                                <x-forms.label fieldId="new-content" :fieldLabel="__('app.description')" />
                                <div id="new-content" style="height: 250px;"></div>
                                <textarea name="content" id="hidden-new-content" class="d-none"></textarea>
                            </div>
                            <div>
                                <button type="button" class="btn btn-primary" id="save-new-template"><i class="fa fa-check mr-1"></i> Create Template</button>
                            </div>
                        </div>

                        <!-- Preview Side -->
                        <div class="col-md-6 pl-4">
                            <h5 class="mb-3 text-secondary"><i class="fa fa-eye mr-2"></i>Live Email Preview</h5>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 id="preview-subject-new" class="font-weight-bold mb-3 border-bottom pb-2"></h6>
                                    <div id="preview-body-new"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    $(document).ready(function() {
        const dummyData = {
            '{task_heading}': 'Website Redesign',
            '{task_short_code}': 'PRJ-101',
            '{due_date}': '31-12-2027',
            '{notifiable_name}': 'John Doe',
            '{url}': '#'
        };

        function parseShortcodes(text) {
            if(!text) return '';
            let parsed = text;
            for (const [key, value] of Object.entries(dummyData)) {
                parsed = parsed.replace(new RegExp(key, 'g'), value);
            }
            return parsed;
        }

        @foreach($emailTemplates as $template)
            var quill{{ $template->id }} = new Quill('#content-{{ $template->id }}', {
                theme: 'snow'
            });

            function updatePreview{{ $template->id }}() {
                var subjectRaw = $('#subject-{{ $template->id }}').val();
                var bodyRaw = quill{{ $template->id }}.root.innerHTML;
                
                $('#preview-subject-{{ $template->id }}').text(parseShortcodes(subjectRaw));
                $('#preview-body-{{ $template->id }}').html(parseShortcodes(bodyRaw));
            }

            quill{{ $template->id }}.on('text-change', updatePreview{{ $template->id }});
            $('#subject-{{ $template->id }}').on('input', updatePreview{{ $template->id }});
            
            // Initial render
            updatePreview{{ $template->id }}();
        @endforeach

        @if($emailTemplates->isEmpty())
            var newQuill = new Quill('#new-content', {
                theme: 'snow'
            });
            
            function updatePreviewNew() {
                var subjectRaw = $('#subject').val();
                var bodyRaw = newQuill.root.innerHTML;
                
                $('#preview-subject-new').text(parseShortcodes(subjectRaw));
                $('#preview-body-new').html(parseShortcodes(bodyRaw));
            }

            newQuill.on('text-change', updatePreviewNew);
            $('#subject').on('input', updatePreviewNew);
            updatePreviewNew();

            $('#save-new-template').click(function() {
                var content = newQuill.root.innerHTML;
                $('#hidden-new-content').val(content);

                $.easyAjax({
                    url: "{{ route('email-templates.store') }}",
                    container: '#createTemplateForm',
                    type: "POST",
                    data: $('#createTemplateForm :input').serialize(),
                    success: function(response) {
                        if (response.status == 'success') {
                            window.location.reload();
                        }
                    }
                });
            });
        @endif

        $('.save-template').click(function() {
            var id = $(this).data('id');
            var quill = Quill.find(document.getElementById('content-' + id));
            $('#hidden-content-' + id).val(quill.root.innerHTML);

            $.easyAjax({
                url: "{{ route('email-templates.update', ':id') }}".replace(':id', id),
                container: '#updateTemplateForm-' + id,
                type: "PUT",
                data: $('#updateTemplateForm-' + id + ' :input').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        // Success handled by easyAjax
                    }
                }
            });
        });
    });
</script>
