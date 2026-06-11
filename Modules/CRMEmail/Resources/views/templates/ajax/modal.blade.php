<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">Email Template Preview</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">×</span>
    </button>
</div>

<div class="modal-body bg-light">
    <!-- Meta Info Box -->
    <div class="p-3 mb-4 bg-white border rounded">
        <div class="row">
            <div class="col-sm-3 text-muted">Template Name:</div>
            <div class="col-sm-9"><strong>{{ $template->title }}</strong></div>
        </div>
        <div class="row mt-2">
            <div class="col-sm-3 text-muted">Sender Override:</div>
            <div class="col-sm-9">
                @if($template->from_name || $template->from_email)
                    <code>"{{ $template->from_name ?? 'System' }}" &lt;{{ $template->from_email ?? 'default@company.com' }}&gt;</code>
                @else
                    <span class="text-muted f-12">(Uses Default Company SMTP Details)</span>
                @endif
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-sm-3 text-muted">Parsed Subject:</div>
            <div class="col-sm-9 text-dark font-weight-bold">{{ $renderedSubject }}</div>
        </div>
    </div>

    <!-- Email Render Box -->
    <div class="bg-white border rounded p-4 shadow-sm" style="min-height: 250px;">
        <div class="ql-editor" style="padding: 0;">
            {!! $renderedBody !!}
        </div>
    </div>
</div>

<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0">@lang('app.close')</x-forms.button-cancel>
</div>
