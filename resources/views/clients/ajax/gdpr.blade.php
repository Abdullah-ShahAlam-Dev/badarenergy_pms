<!-- ROW START -->
<div class="row">

    <div class="col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4">
        <!-- Add Task Export Buttons Start -->
        <div class="d-flex" id="table-actions"></div>
        <!-- Add Task Export Buttons End -->
    </div>

    <div class="col-lg-8 col-md-8 mb-4 mb-xl-0 mb-lg-4">
        <!-- Task Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">
            {!! $dataTable->table(['class' => 'table table-hover border-0']) !!}
        </div>
        <!-- Task Box End -->
    </div>

    <div class="col-lg-4 col-md-4 mb-4 mb-xl-0 mb-lg-4">

        <h4 class="heading-h4">@lang('modules.gdpr.consent')</h4>

        <ul class="list-group">
            @forelse($consents as $consent)
                <li class="list-group-item border-grey">
                    <a class="d-block f-15 text-dark-grey text-capitalize consent-details"
                        href="javascript:;" data-consent-id="{{ $consent->id }}">{{ $consent->name }}</a>
                </li>
            @empty
                <li class="list-group-item border-grey">
                    <x-cards.no-record :message="__('messages.noRecordFound')" icon="list" />
                </li>
            @endforelse
        </ul>

    </div>


</div>
<!-- ROW END -->
@include('sections.datatable_js')

<script>
    (function() {
        var $body = $('body');
        var namespace = '.clientsGdpr';
        var $table = $('#client-gdpr-table');

        $table.off('preXhr.dt' + namespace).on('preXhr.dt' + namespace, function(e, settings, data) {
            var clientID = "{{ $client->id }}";
            data['clientID'] = clientID;
        });

        var showTable = function() {
            if (window.LaravelDataTables["client-gdpr-table"]) {
                window.LaravelDataTables["client-gdpr-table"].draw(false);
            }
        };

        $body.off('.clientsGdpr');

        $body.on('click.clientsGdpr', '.consent-details', function() {
            var consentId = $(this).data('consent-id');
            var clientId = "{{ $client->id }}";
            var url = "{{ route('clients.gdpr_consent') }}?consentId=" + consentId + "&clientId=" + clientId;

            $.ajaxModal(MODAL_LG, url);
        });

        document.addEventListener("turbo:before-cache", function cleanup() {
            $body.off(namespace);
            $table.off('preXhr.dt' + namespace);
            document.removeEventListener("turbo:before-cache", cleanup);
        }, { once: true });
    })();
</script>
