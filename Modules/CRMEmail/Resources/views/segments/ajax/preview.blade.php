<div class="card border-grey bg-additional-grey mt-3">
    <div class="card-body p-3">
        <h5 class="f-14 font-weight-bold text-darkest-grey mb-2">Segment Estimation</h5>
        
        <div class="d-flex align-items-center mb-3">
            <span class="f-24 font-weight-bold text-primary mr-2">{{ $count }}</span>
            <span class="f-13 text-muted">distinct matching recipients</span>
        </div>

        @if($count > 0)
            <div class="table-responsive bg-white rounded border border-grey" style="max-height: 250px; overflow-y: auto;">
                <table class="table table-sm table-hover mb-0 f-12 text-darkest-grey">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 px-2 py-1">Name</th>
                            <th class="border-0 px-2 py-1">Email</th>
                            <th class="border-0 px-2 py-1">Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sampleRecipients as $recipient)
                            <tr>
                                <td class="px-2 py-1 align-middle text-truncate" style="max-width: 120px;">
                                    {{ $recipient->name ?: '--' }}
                                </td>
                                <td class="px-2 py-1 align-middle text-truncate" style="max-width: 150px;">
                                    {{ $recipient->email }}
                                </td>
                                <td class="px-2 py-1 align-middle">
                                    @php
                                        $typeClass = 'badge-info';
                                        if ($recipient->recipient_type === 'lead') {
                                            $typeClass = 'badge-warning text-dark';
                                        } elseif ($recipient->recipient_type === 'contact') {
                                            $typeClass = 'badge-success';
                                        }
                                    @endphp
                                    <span class="badge {{ $typeClass }} f-10">{{ ucfirst($recipient->recipient_type) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <small class="text-muted d-block mt-2">Showing a sample of up to 10 matching contacts.</small>
        @else
            <div class="text-center py-3 text-muted">
                <i class="fa fa-info-circle mb-1 f-16"></i>
                <p class="mb-0 f-12">No matching recipients found. Change the filter criteria or check at least one target source.</p>
            </div>
        @endif
    </div>
</div>
