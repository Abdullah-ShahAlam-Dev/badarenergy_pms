<div id="shipment-detail-section">
    <div class="row">
        <div class="col-sm-12">
            <div class="card bg-white border-0 b-shadow-4">
                <div class="card-header bg-white border-0 text-capitalize d-flex justify-content-between p-20">
                    <h4 class="f-21 font-weight-normal mb-0">Shipment Details - {{ $shipment->shipment_number }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-7 col-lg-6 col-md-12">
                            <div class="d-flex flex-wrap">
                                <div class="col-md-6 mb-3">
                                    <h5 class="f-14 text-lightest font-weight-normal">Shipment Number</h5>
                                    <p class="f-15 text-darkest-grey">{{ $shipment->shipment_number }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <h5 class="f-14 text-lightest font-weight-normal">Container Number</h5>
                                    <p class="f-15 text-darkest-grey">{{ $shipment->container_number ?: '-' }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <h5 class="f-14 text-lightest font-weight-normal">Bill of Lading</h5>
                                    <p class="f-15 text-darkest-grey">{{ $shipment->bill_of_lading ?: '-' }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <h5 class="f-14 text-lightest font-weight-normal">Manufacturing Reference</h5>
                                    <p class="f-15 text-darkest-grey">{{ $shipment->manufacturing_ref ?: '-' }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <h5 class="f-14 text-lightest font-weight-normal">Port of Origin</h5>
                                    <p class="f-15 text-darkest-grey">{{ $shipment->port_of_origin }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <h5 class="f-14 text-lightest font-weight-normal">Port of Discharge</h5>
                                    <p class="f-15 text-darkest-grey">{{ $shipment->port_of_discharge }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <h5 class="f-14 text-lightest font-weight-normal">ETA</h5>
                                    <p class="f-15 text-darkest-grey">{{ $shipment->eta ? $shipment->eta->translatedFormat(company()->date_format) : '-' }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <h5 class="f-14 text-lightest font-weight-normal">Arrival Date</h5>
                                    <p class="f-15 text-darkest-grey">{{ $shipment->arrival_date ? $shipment->arrival_date->translatedFormat(company()->date_format) : '-' }}</p>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <h5 class="f-14 text-lightest font-weight-normal">Status</h5>
                                    @php
                                        $statusColors = [
                                            'in_transit' => 'text-blue',
                                            'port_customs' => 'text-warning',
                                            'warehouse_receiving' => 'text-info',
                                            'completed' => 'text-success',
                                            'cancelled' => 'text-danger',
                                        ];
                                        $color = $statusColors[$shipment->status] ?? 'text-dark-grey';
                                    @endphp
                                    <p class="f-15"><i class="fa fa-circle mr-1 f-10 {{ $color }}"></i>{{ ucwords(str_replace('_', ' ', $shipment->status)) }}</p>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <h5 class="f-14 text-lightest font-weight-normal">Remarks</h5>
                                    <p class="f-15 text-darkest-grey">{!! nl2br(e($shipment->remarks)) ?: '-' !!}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Linked Intake Vouchers Section -->
                        <div class="col-xl-5 col-lg-6 col-md-12 border-left">
                            <h4 class="f-16 text-darkest-grey font-weight-bold mb-3">Linked Stock Intake Vouchers</h4>
                            @if($shipment->stockIntakeVouchers->isEmpty())
                                <p class="text-lightest">No stock intake vouchers are linked to this shipment yet.</p>
                            @else
                                <div class="list-group list-group-flush">
                                    @foreach($shipment->stockIntakeVouchers as $voucher)
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <div>
                                                <h6 class="mb-0 text-darkest-grey font-weight-bold">{{ $voucher->voucher_number }}</h6>
                                                <small class="text-lightest">Date: {{ $voucher->intake_date->translatedFormat(company()->date_format) }}</small>
                                            </div>
                                            <span class="badge badge-light p-2">{{ strtoupper($voucher->status) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
