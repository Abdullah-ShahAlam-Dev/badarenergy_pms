<div class="row">
    <div class="col-sm-12">
        <x-form id="save-template-data-form">
            @method('PUT')
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    Edit Template Details
                </h4>

                <div class="row p-20">
                    <!-- Template Name -->
                    <div class="col-md-6">
                        <x-forms.text fieldId="title" fieldLabel="Template Name" fieldName="title"
                            fieldRequired="true" :fieldValue="$template->title" fieldPlaceholder="e.g. Welcome Email"></x-forms.text>
                    </div>

                    <!-- Subject -->
                    <div class="col-md-6">
                        <x-forms.text fieldId="subject" fieldLabel="Email Subject" fieldName="subject"
                            fieldRequired="true" :fieldValue="$template->subject" fieldPlaceholder="e.g. Welcome to Our Company!"></x-forms.text>
                    </div>

                    <!-- From Name -->
                    <div class="col-md-4 mt-3">
                        <x-forms.text fieldId="from_name" fieldLabel="From Name (Optional Override)" fieldName="from_name"
                            :fieldValue="$template->from_name" fieldPlaceholder="e.g. Sales Team"></x-forms.text>
                    </div>

                    <!-- From Email -->
                    <div class="col-md-4 mt-3">
                        <x-forms.text fieldId="from_email" fieldLabel="From Email (Optional Override)" fieldName="from_email"
                            :fieldValue="$template->from_email" fieldPlaceholder="e.g. sales@company.com"></x-forms.text>
                    </div>

                    <!-- Status -->
                    <div class="col-md-4 mt-3">
                        <div class="form-group c-inv-select">
                            <x-forms.label fieldId="status" fieldLabel="Status" fieldRequired="true"></x-forms.label>
                            <select class="form-control select-picker" name="status" id="status">
                                <option value="active" @if($template->status == 'active') selected @endif>Active</option>
                                <option value="inactive" @if($template->status == 'inactive') selected @endif>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <!-- HTML Body Quill Editor -->
                    <div class="col-md-9 mt-4">
                        <div class="form-group mb-0">
                            <x-forms.label fieldId="content" fieldLabel="HTML Body" fieldRequired="true"></x-forms.label>
                            <div id="content" style="height: 300px;">{!! $template->content !!}</div>
                            <textarea name="content" id="content-text" class="d-none"></textarea>
                        </div>
                    </div>

                    <!-- Variable Guide Panel -->
                    <div class="col-md-3 mt-4">
                        <x-forms.label fieldId="variables-panel" fieldLabel="Available Variables"></x-forms.label>
                        <div class="p-3 bg-light border rounded" style="max-height: 330px; overflow-y: auto;">
                            <small class="text-muted d-block mb-3">Click on any variable below to insert it at your cursor position in the editor:</small>
                            @foreach($variables as $key => $label)
                                <button type="button" class="btn btn-sm btn-outline-primary btn-block text-left mb-2 insert-variable-btn" data-variable="{{ $key }}">
                                    <strong>{{ $key }}</strong>
                                    <span class="d-block f-11 text-muted">{{ $label }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-template-btn" class="mr-3" icon="check">
                        @lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('crm-email-templates.index')" class="border-0">
                        @lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Initialize Quill image load
        quillImageLoad('#content');

        // Insert placeholder variable on button click
        $('.insert-variable-btn').click(function() {
            var variable = $(this).data('variable');
            var quill = quillArray['#content'];
            if (quill) {
                var range = quill.getSelection(true);
                if (range) {
                    quill.insertText(range.index, variable);
                    quill.setSelection(range.index + variable.length);
                }
            }
        });

        // Save Template Update
        $('#save-template-btn').click(function() {
            // Retrieve content from editor
            var editorHtml = document.getElementById('content').children[0].innerHTML;
            
            // Empty check for Quill editor default tag (<p><br></p>)
            if (editorHtml === '<p><br></p>') {
                editorHtml = '';
            }
            document.getElementById('content-text').value = editorHtml;

            const url = "{{ route('crm-email-templates.update', $template->id) }}";

            $.easyAjax({
                url: url,
                container: '#save-template-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-template-btn",
                data: $('#save-template-data-form').serialize(),
                redirect: true
            });
        });

        init(RIGHT_MODAL);
    });
</script>
