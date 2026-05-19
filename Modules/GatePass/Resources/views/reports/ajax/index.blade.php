<div class="table-responsive">
    <table class="table table-hover border-0 w-100">
        <thead>
            <tr>
                <th>@lang('gatepass::modules.gatePass.requestNumber')</th>
                <th>@lang('app.employee')</th>
                <th>@lang('gatepass::modules.gatePass.requestDate')</th>
                <th>@lang('gatepass::modules.gatePass.type')</th>
                <th>@lang('gatepass::modules.gatePass.itemName')</th>
                <th>@lang('gatepass::modules.gatePass.status')</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $report)
                <tr>
                    <td>{{ $report->request_number }}</td>
                    <td>{{ $report->user->name }}</td>
                    <td>{{ $report->request_date->format(company()->date_format) }}</td>
                    <td>{{ strtoupper($report->type) }}</td>
                    <td>
                        @foreach($report->items as $item)
                            {{ $item->item_name }} ({{ $item->quantity }})@if(!$loop->last), @endif
                        @endforeach
                    </td>
                    <td>
                        @php
                            $statusClass = 'badge-warning';
                            $statusText = str_replace('_', ' ', strtoupper($report->status));
                            
                            if($report->status == 'completed') {
                                $statusClass = 'badge-success';
                            } elseif($report->status == 'open') {
                                $statusClass = 'badge-info';
                                $statusText = 'OPEN (RETURN PENDING)';
                            } elseif($report->status == 'sent_back') {
                                $statusClass = 'badge-danger';
                                $statusText = 'SENT BACK';
                            } elseif(str_contains($report->status, 'rejected')) {
                                $statusClass = 'badge-danger';
                            } elseif($report->status == 'pending_security') {
                                $statusClass = 'badge-info';
                            }
                        @endphp
                        <span class="badge {{ $statusClass }}">
                            {{ $statusText }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-5">No records found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
