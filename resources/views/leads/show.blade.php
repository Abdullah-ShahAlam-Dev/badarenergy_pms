@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@php
$viewClientNote = user()->permission('view_lead_note');
@endphp


@section('filter-section')
    <!-- FILTER START -->
    <!-- PROJECT HEADER START -->
    <div class="d-flex filter-box project-header bg-white">

        <div class="mobile-close-overlay w-100 h-100" id="close-client-overlay"></div>
        <div class="project-menu d-lg-flex" id="mob-client-detail">
            <a class="d-none close-it" href="javascript:;" id="close-client-detail" >
                <i class="fa fa-times"></i>
            </a>
            <x-tab :href="route('leads.show', $lead->id)" :text="__('modules.lead.profile')" class="profile" />

            <x-tab :href="route('leads.show', $lead->id).'?tab=files'" :text="__('modules.lead.file')" class="files" ajax="false"/>

            <x-tab :href="route('leads.show', $lead->id).'?tab=follow-up'" :text="__('modules.lead.followUp')" class="follow-up" ajax="false" />

            <x-tab :href="route('leads.show', $lead->id).'?tab=proposals'" :text="__('modules.lead.proposal')" class="proposals" ajax="false" />

        @if ($viewClientNote == 'all' || $viewClientNote == 'both' || $viewClientNote == 'added' || $viewClientNote == 'owned')
            <x-tab :href="route('leads.show', $lead->id).'?tab=notes'" ajax="false" :text="__('app.notes')" class="notes" />
        @endif

            @if ($gdpr->enable_gdpr)
                <x-tab :href="route('leads.show', $lead->id).'?tab=gdpr'" :text="__('app.menu.gdpr')" class="gdpr" ajax="false" />
            @endif
        </div>
        <a class="mb-0 d-block d-lg-none text-dark-grey ml-auto mr-2 border-left-grey"
            onclick="openClientDetailSidebar()"><i class="fa fa-ellipsis-v "></i></a>
    </div>
    <!-- FILTER END -->
    <!-- PROJECT HEADER END -->

@endsection

@section('content')

    <div class="content-wrapper border-top-0 client-detail-wrapper">
        @include($view)
    </div>

@endsection

@push('scripts')
<script>
    (function() {
        var $body = $('body');
        var namespace = '.leadsShow';
        var activeTab = "{{ $activeTab }}";

        $('.project-menu .' + activeTab).addClass('active');

        $body.off(namespace);

        $body.on('click' + namespace, '.ajax-tab', function(event) {
            event.preventDefault();

            $('.project-menu .p-sub-menu').removeClass('active');
            $(this).addClass('active');

            var requestUrl = this.href;

            $.easyAjax({
                url: requestUrl,
                blockUI: true,
                container: ".content-wrapper",
                historyPush: true,
                success: function(response) {
                    if (response.status == "success") {
                        $('.content-wrapper').html(response.html);
                        init('.content-wrapper');
                    }
                }
            });
        });

        $body.on('click' + namespace, '#add-files', function() {
            var url = "{{ route('lead-files.create') }}";
            $.ajaxModal(MODAL_LG, url);
        });

        $body.on('click' + namespace, '.delete-table-row', function() {
            var id = $(this).data('id');
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
                    var url = "{{ route('leads.destroy', ':id') }}".replace(':id', id);
                    var token = "{{ csrf_token() }}";
                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: { '_token': token, '_method': 'DELETE' },
                        success: function(response) {
                            if (response.status == "success") {
                                window.location.href = "{{ route('leads.index')}}";
                            }
                        }
                    });
                }
            });
        });

        document.addEventListener("turbo:before-cache", function cleanup() {
            $body.off(namespace);
            document.removeEventListener("turbo:before-cache", cleanup);
        }, { once: true });
    })();
</script>
@endpush
