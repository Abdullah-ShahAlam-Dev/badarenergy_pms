@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('content')
    <div class="content-wrapper">
        <div class="d-block d-lg-flex d-md-flex justify-content-between action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center">
                <h4 class="mb-0 pr-3 f-18 font-weight-bold text-dark-grey float-left">Delivery Orders</h4>
            </div>
            
            <div class="d-flex align-items-center">
                <div class="input-group bg-grey rounded">
                    <div class="input-group-prepend">
                        <span class="input-group-text border-0 bg-additional-grey">
                            <i class="fa fa-search f-13 text-dark-grey"></i>
                        </span>
                    </div>
                    <input type="text" class="form-control f-14 p-1 border-additional-grey" id="search-text-field"
                        placeholder="@lang('app.startTyping')">
                </div>
            </div>
        </div>

        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive p-4">
            <table id="delivery-orders-table" class="table table-hover border-0 w-100">
                <thead>
                    <tr>
                        <th>DO ID</th>
                        <th>Invoice Ref</th>
                        <th>Customer</th>
                        <th>Issue Date</th>
                        <th>Dispatcher</th>
                        <th>Status</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    @include('sections.datatable_js')
    <script>
        $(function() {
            var table = $('#delivery-orders-table').DataTable({
                responsive: true,
                serverSide: true,
                processing: true,
                ajax: {
                    url: "{{ route('delivery-orders.index') }}",
                    data: function(d) {
                        d.searchText = $('#search-text-field').val();
                    }
                },
                language: {
                    "url": "{{ __($darkPlaystoreUrl ?? 'assets/plugins/datatables/language/' . ($settings->locale ?? 'en') . '.json') }}"
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'invoice_number', name: 'invoice_number' },
                    { data: 'client_name', name: 'client_name', orderable: false, searchable: false },
                    { data: 'issue_date', name: 'issue_date' },
                    { data: 'dispatcher', name: 'dispatcher' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, class: 'text-right' }
                ]
            });

            $('#search-text-field').on('keyup', function() {
                table.draw();
            });

            $('body').on('click', '.open-edit-modal', function() {
                var doId = $(this).data('do-id');
                var url = "{{ route('delivery-orders.edit', ':id') }}";
                url = url.replace(':id', doId);
                $(MODAL_LG + ' ' + MODAL_HEADING).html('Edit / Dispatch Delivery Order');
                $.ajaxModal(MODAL_LG, url);
            });
        });
    </script>
@endpush
