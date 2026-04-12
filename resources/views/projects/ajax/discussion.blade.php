@php
$addDiscussionPermission = user()->permission('add_project_discussions');
$manageCategoryPermission = user()->permission('manage_discussion_category');
@endphp

<style>
    #discussion-table_wrapper .dt-buttons,
    #discussion-table thead {
        display: none !important;
    }

    #discussion-table tr:hover .message-action {
        visibility: visible;
    }

    .message-action {
        visibility: hidden;
    }

    .card:hover .message-action {
        visibility: visible;
    }

</style>

<!-- ROW START -->
<div class="row pb-5">
    <div class="col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4 mt-3 mt-lg-5 mt-md-5">
        <!-- Add Task Export Buttons Start -->
        <div class="d-flex" id="table-actions">
            @if (($addDiscussionPermission == 'all' || $addDiscussionPermission == 'added' || $project->project_admin == user()->id) && !$project->trashed())
                <x-forms.button-primary class="mr-3 float-left" id="add-discussion" icon="plus" data-redirect-url="{{ route('projects.show', $project->id) . '?tab=discussion' }}">
                    @lang('app.new') @lang('modules.projects.discussion')
                </x-forms.button-primary>
            @endif

            @if ($manageCategoryPermission == 'all')
                <x-forms.button-secondary class="mr-3 float-left" id="discussion-category" icon="cog">
                    @lang('modules.discussions.discussionCategory')
                </x-forms.button-secondary>
            @endif

        </div>
        <!-- Add Task Export Buttons End -->

        <form action="" id="filter-form">
            <div class="d-flex my-3">
                <!-- STATUS START -->
                <div class="select-box py-2 px-0 mr-3">
                    <x-forms.label :fieldLabel="__('app.category')" fieldId="status" />
                    <select class="form-control select-picker" name="discussion_category" id="discussion_category"
                        data-live-search="true" data-size="8">
                        <option value="">@lang('app.all')</option>
                        @foreach ($discussionCategories as $item)
                            <option
                                data-content="<i class='fa fa-circle mr-2' style='color: {{ $item->color }}'></i> {{ mb_ucwords($item->name) }}"
                                value="{{ $item->id }}">{{ mb_ucwords($item->name) }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- STATUS END -->
            </div>
        </form>

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
        var $table = $('#discussion-table');
        var namespace = '.projectDiscussion';

        $table.off('preXhr.dt').on('preXhr.dt' + namespace, function(e, settings, data) {
            var projectId = "{{ $project->id }}";
            var categoryId = $('#discussion_category').val();
            data['project_id'] = projectId;
            data['category_id'] = categoryId;
        });

        function showTable() {
            if (window.LaravelDataTables && window.LaravelDataTables["discussion-table"]) {
                window.LaravelDataTables["discussion-table"].draw(false);
            }
        }
        window.showTable = showTable;

        $body.off(namespace);

        $body.on('change' + namespace, '#discussion_category', function() {
            showTable();
        });

        $body.on('click' + namespace, '.delete-discussion', function() {
            var id = $(this).data('discussion-id');
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
                    var url = "{{ route('discussion.destroy', ':id') }}".replace(':id', id);
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

        $body.on('click' + namespace, '#discussion-category', function() {
            var url = "{{ route('discussion-category.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $body.on('click' + namespace, '#add-discussion', function() {
            let redirectUrl = encodeURIComponent($(this).data("redirect-url"));
            var url = "{{ route('discussion.create') }}?id="+"{{ $project->id }}&redirectUrl="+redirectUrl;
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_XL, url);
        });

        $body.on('click' + namespace, '.edit-category', function() {
            var categoryId = $(this).data('category-id');
            var url = "{{ route('discussion-category.edit', ':id') }}".replace(':id', categoryId);
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $body.on('click' + namespace, '.add-reply', function() {
            var discussionId = $(this).data('discussion-id');
            var url = "{{ route('discussion-reply.create') }}?id=" + discussionId;
            $(MODAL_XL + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_XL, url);
        });

        $body.on('click' + namespace, '.edit-reply', function() {
            var id = $(this).data('row-id');
            var url = "{{ route('discussion-reply.edit', ':id') }}".replace(':id', id);
            $(MODAL_XL + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_XL, url);
        });

        $body.on('click' + namespace, '.set-best-answer', function() {
            var replyId = $(this).data('row-id');
            var type = 'set';
            var url = "{{ route('discussion.set_best_answer') }}";
            var token = "{{ csrf_token() }}";
            $.easyAjax({
                type: 'POST',
                url: url,
                container: '#right-modal-content',
                blockUI: true,
                data: { '_token': token, '_method': 'POST', 'replyId': replyId, 'type': type },
                success: function(response) {
                    if (response.status == "success") { $('#right-modal-content').html(response.html); }
                }
            });
        });

        $body.on('click' + namespace, '.unset-best-answer', function() {
            var replyId = $(this).data('reply-id');
            var type = 'unset';
            var url = "{{ route('discussion.set_best_answer') }}";
            var token = "{{ csrf_token() }}";
            $.easyAjax({
                type: 'POST',
                url: url,
                container: '#right-modal-content',
                blockUI: true,
                data: { '_token': token, '_method': 'POST', 'replyId': replyId, 'type': type },
                success: function(response) {
                    if (response.status == "success") { $('#right-modal-content').html(response.html); }
                }
            });
        });

        $body.on('click' + namespace, '.delete-message', function() {
            var id = $(this).data('row-id');
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
                    var url = "{{ route('discussion-reply.destroy', ':id') }}".replace(':id', id);
                    var token = "{{ csrf_token() }}";
                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        container: '#right-modal-content',
                        blockUI: true,
                        data: { '_token': token, '_method': 'DELETE' },
                        success: function(response) {
                            if (response.status == "success") { $('#right-modal-content').html(response.html); }
                        }
                    });
                }
            });
        });

        $body.on('click' + namespace, '.go-best-reply', function() {
            var replyId = $(this).data('reply-id');
            $('html, body').animate({
                scrollTop: $("#replyMessageBox_" + replyId).offset().top
            }, 1000);
        });

        document.addEventListener('turbo:before-cache', function cleanup() {
            $body.off(namespace);
            $table.off('preXhr.dt' + namespace);
            delete window.showTable;
            document.removeEventListener('turbo:before-cache', cleanup);
        }, { once: true });
    })();
</script>
