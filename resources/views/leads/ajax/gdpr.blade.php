<!-- ROW START -->
<div class="row">

    <div class="col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4">
        <!-- Add Task Export Buttons Start -->
        <div class="d-flex" id="table-actions">
            @if (isset($gdpr) && $gdpr->consent_customer == 1)
                <x-forms.link-primary :link="route('front.gdpr.consent', $lead->hash)"
                    class="mr-3" icon="eye" target="_blank">
                    @lang('modules.gdpr.viewConsent')
                </x-forms.link-primary>
            @endif
        </div>
        <!-- Add Task Export Buttons End -->
    </div>

    <div class="col-lg-9 col-md-12 mb-4 mb-xl-0 mb-lg-4">
        <!-- Task Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">
            {!! $dataTable->table(['class' => 'table table-hover border-0']) !!}
        </div>
        <!-- Task Box End -->
    </div>

    <div class="col-lg-3 col-md-12 mb-4 mb-xl-0 mb-lg-4">
        <div class="right-sidebar">
            <div class="d-flex flex-column rounded mt-3 bg-white">
                <ul>
                    @forelse($consents as $consent)
                    <li>
                        <a class="d-block f-15 text-dark-grey text-capitalize border-bottom-grey consent-details" href="javascript:;" data-consent-id="{{ $consent->id }}">{{ $consent->name }}</a>
                    </li>
                    @empty
                    <p class="text-center">No Consent available.</p>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

</div>
<!-- ROW END -->

@include('sections.datatable_js')

<script>
    (function() {
        var $body = $('body');
        var $table = $('#leads-gdpr-table');
        var namespace = '.leadsGdpr';

        $table.off('preXhr.dt').on('preXhr.dt' + namespace, function(e, settings, data) {
            var leadID = "{{ $lead->id }}";
            data['leadID'] = leadID;
        });

        function showTable() {
            if (window.LaravelDataTables && window.LaravelDataTables["leads-gdpr-table"]) {
                window.LaravelDataTables["leads-gdpr-table"].draw(false);
            }
        }
        window.showTable = showTable;

        $body.off(namespace);

        $body.on('click' + namespace, '.consent-details', function() {
            var consentId = $(this).data('consent-id');
            var leadId = "{{ $lead->id }}";
            var url = `{{ route('leads.gdpr_consent') }}?consentId=${consentId}&leadId=${leadId}`;

            $.ajaxModal(MODAL_LG, url);
        });

        document.addEventListener("turbo:before-cache", function cleanup() {
            $body.off(namespace);
            $table.off('preXhr.dt' + namespace);
            delete window.showTable;
            document.removeEventListener("turbo:before-cache", cleanup);
        }, { once: true });
    })();
</script>
