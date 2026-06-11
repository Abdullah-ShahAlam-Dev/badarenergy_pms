<div class="modal-header">
    <h5 class="modal-title">
        <i class="fa fa-chart-bar mr-2"></i>
        Campaign Stats &mdash; {{ $campaign->name }}
    </h5>
    <button type="button" class="close" data-dismiss="modal">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="modal-body">

    {{-- Status Badge --}}
    <div class="mb-4 d-flex align-items-center">
        <span class="f-14 text-dark-grey mr-3">Status:</span>
        @php
            $statusMap = [
                'draft'     => ['secondary', 'Draft'],
                'scheduled' => ['info', 'Scheduled'],
                'sending'   => ['warning', 'Sending'],
                'completed' => ['success', 'Completed'],
                'paused'    => ['dark', 'Paused'],
                'canceled'  => ['danger', 'Canceled'],
            ];
            [$color, $label] = $statusMap[$campaign->status] ?? ['secondary', ucfirst($campaign->status)];
        @endphp
        <span class="badge badge-{{ $color }} f-13">{{ $label }}</span>

        @if ($campaignStats['launched_at'])
            <span class="ml-3 text-muted f-12">Launched: {{ $campaignStats['launched_at'] }}</span>
        @endif
    </div>

    {{-- Delivery Summary Cards --}}
    <div class="row text-center mb-4">
        <div class="col-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <h3 class="f-28 font-weight-bold text-dark">{{ number_format($campaignStats['total']) }}</h3>
                    <p class="text-muted f-12 mb-0">Total Recipients</p>
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <h3 class="f-28 font-weight-bold text-success">{{ number_format($campaignStats['sent']) }}</h3>
                    <p class="text-muted f-12 mb-0">Sent <span class="badge badge-success">{{ $campaignStats['sent_pct'] }}%</span></p>
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <h3 class="f-28 font-weight-bold text-warning">{{ number_format($campaignStats['pending']) }}</h3>
                    <p class="text-muted f-12 mb-0">Pending</p>
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <h3 class="f-28 font-weight-bold text-danger">{{ number_format($campaignStats['failed']) }}</h3>
                    <p class="text-muted f-12 mb-0">Failed <span class="badge badge-danger">{{ $campaignStats['failed_pct'] }}%</span></p>
                </div>
            </div>
        </div>
    </div>

    {{-- Delivery Progress Bar --}}
    @if ($campaignStats['total'] > 0)
        <div class="mb-4">
            <div class="d-flex justify-content-between mb-1">
                <small class="text-muted">Delivery Progress</small>
                <small class="text-muted">{{ $campaignStats['sent_pct'] }}% sent</small>
            </div>
            <div class="progress" style="height: 10px; border-radius: 5px;">
                <div class="progress-bar bg-success" role="progressbar"
                     style="width: {{ $campaignStats['sent_pct'] }}%"
                     aria-valuenow="{{ $campaignStats['sent_pct'] }}"
                     aria-valuemin="0" aria-valuemax="100">
                </div>
                <div class="progress-bar bg-danger" role="progressbar"
                     style="width: {{ $campaignStats['failed_pct'] }}%"
                     aria-valuenow="{{ $campaignStats['failed_pct'] }}"
                     aria-valuemin="0" aria-valuemax="100">
                </div>
            </div>
        </div>
    @endif

    {{-- Batch Progress (if dispatched) --}}
    @if (!empty($campaignStats['batch_progress']))
        @php $bp = $campaignStats['batch_progress']; @endphp
        <div class="alert alert-light border mb-4">
            <strong class="f-13">Queue Batch Progress</strong>
            <div class="row mt-2 text-center">
                <div class="col-4">
                    <div class="f-16 font-weight-bold">{{ $bp['total_jobs'] }}</div>
                    <div class="text-muted f-11">Total Jobs</div>
                </div>
                <div class="col-4">
                    <div class="f-16 font-weight-bold text-warning">{{ $bp['pending_jobs'] }}</div>
                    <div class="text-muted f-11">Pending Jobs</div>
                </div>
                <div class="col-4">
                    <div class="f-16 font-weight-bold text-danger">{{ $bp['failed_jobs'] }}</div>
                    <div class="text-muted f-11">Failed Jobs</div>
                </div>
            </div>
            <div class="progress mt-3" style="height: 6px; border-radius: 3px;">
                <div class="progress-bar bg-primary" style="width: {{ $bp['progress'] }}%"></div>
            </div>
            <div class="text-right mt-1">
                <small class="text-muted">{{ $bp['progress'] }}% processed</small>
                @if ($bp['finished'])
                    <span class="badge badge-success ml-2">Batch Finished</span>
                @endif
                @if ($bp['cancelled'])
                    <span class="badge badge-danger ml-2">Batch Cancelled</span>
                @endif
            </div>
        </div>
    @endif

    {{-- Recent Failed Emails --}}
    @php
        $failedEmails = $campaign->emails()
            ->where('status', 'failed')
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();
    @endphp

    @if ($failedEmails->isNotEmpty())
        <div class="mt-4">
            <h6 class="f-14 font-weight-bold text-danger mb-2">
                <i class="fa fa-exclamation-triangle mr-1"></i>
                Recent Delivery Failures (last 10)
            </h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Email</th>
                            <th>Attempts</th>
                            <th>Error</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($failedEmails as $fe)
                            <tr>
                                <td class="f-12">{{ $fe->email }}</td>
                                <td class="f-12 text-center">{{ $fe->attempts }}</td>
                                <td class="f-12 text-danger">{{ Str::limit($fe->error_message, 80) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
</div>
