@extends('layouts.app')

@section('filter-section')
    <x-filters.filter-box>
        <!-- DATE START -->
        <div class="select-box d-flex pr-2 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.date')</p>
            <div class="select-status d-flex">
                <input type="text" class="position-relative text-dark form-control border-0 p-2 text-left f-14 f-w-500 border-additional-grey"
                    id="date-filter" placeholder="@lang('app.date')" value="{{ \Carbon\Carbon::parse($date)->format(company()->date_format) }}">
            </div>
        </div>
        <!-- DATE END -->
        
        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
    </x-filters.filter-box>
@endsection

@section('content')
<div class="content-wrapper">

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0 f-20 font-weight-bold">
                <i class="fa fa-exclamation-triangle text-warning mr-2"></i>
                Missing Reports Tracker
            </h4>
            <p class="mb-0 text-lightest f-13 mt-1">
                Date: <strong>{{ \Carbon\Carbon::parse($date)->format('l, ') }}{{ \Carbon\Carbon::parse($date)->format(company()->date_format) }}</strong>
            </p>
        </div>
        <div class="d-flex align-items-center">
            <a href="{{ route('daily-reports.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa fa-arrow-left mr-1"></i> Back to Report Analysis
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 b-shadow-4 rounded p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded p-3 mr-3" style="background:#4361ee20">
                        <i class="fa fa-users fa-lg" style="color:#4361ee"></i>
                    </div>
                    <div>
                        <p class="mb-0 text-lightest f-12">Total Employees</p>
                        <h3 class="mb-0 font-weight-bold">{{ $totalEmployees }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 b-shadow-4 rounded p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded p-3 mr-3" style="background:#2ec99520">
                        <i class="fa fa-check-circle fa-lg" style="color:#2ec995"></i>
                    </div>
                    <div>
                        <p class="mb-0 text-lightest f-12">Submitted</p>
                        <h3 class="mb-0 font-weight-bold text-success">{{ $submittedCount }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 b-shadow-4 rounded p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded p-3 mr-3" style="background:#f4433620">
                        <i class="fa fa-times-circle fa-lg" style="color:#f44336"></i>
                    </div>
                    <div>
                        <p class="mb-0 text-lightest f-12">Missing</p>
                        <h3 class="mb-0 font-weight-bold text-danger">{{ $missingEmployees->count() }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">

        {{-- Employees Who Did NOT Submit --}}
        <div class="col-md-5">
            <div class="card border-0 b-shadow-4 rounded">
                <div class="card-header border-bottom bg-white p-3">
                    <h6 class="mb-0 font-weight-bold text-danger">
                        <i class="fa fa-times-circle mr-2"></i>
                        Did NOT Submit ({{ $missingEmployees->count() }})
                    </h6>
                </div>
                <div class="card-body p-0" style="max-height:500px; overflow-y:auto">
                    @forelse($missingEmployees as $emp)
                        <div class="d-flex align-items-center p-3 border-bottom">
                            <img src="{{ $emp->image_url }}"
                                class="rounded-circle mr-3"
                                width="36" height="36"
                                alt="{{ $emp->name }}">
                            <div class="flex-grow-1">
                                <p class="mb-0 f-14 font-weight-bold">{{ $emp->name }}</p>
                                <p class="mb-0 text-lightest f-12">
                                    {{ $emp->employeeDetail->designation->name ?? '—' }}
                                </p>
                            </div>
                            <span class="badge badge-danger">Missing</span>
                        </div>
                    @empty
                        <div class="text-center p-4">
                            <i class="fa fa-check-circle fa-3x text-success mb-2 d-block"></i>
                            <p class="text-success font-weight-bold mb-0">All employees submitted!</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Submitted Reports --}}
        <div class="col-md-7">
            <div class="card border-0 b-shadow-4 rounded">
                <div class="card-header border-bottom bg-white p-3">
                    <h6 class="mb-0 font-weight-bold text-success">
                        <i class="fa fa-check-circle mr-2"></i>
                        Submitted Reports ({{ $submittedReports->count() }})
                    </h6>
                </div>
                <div class="card-body p-0" style="max-height:500px; overflow-y:auto">
                    @forelse($submittedReports as $report)
                        <div class="d-flex align-items-start p-3 border-bottom">
                            <img src="{{ $report->user->image_url }}"
                                class="rounded-circle mr-3 mt-1"
                                width="36" height="36"
                                alt="{{ $report->user->name }}">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <p class="mb-0 f-14 font-weight-bold">{{ $report->user->name }}</p>
                                    <span class="text-lightest f-11">
                                        <i class="fa fa-clock mr-1"></i>
                                        {{ $report->created_at->timezone(company()->timezone)
                                            ->format(company()->time_format) }}
                                    </span>
                                </div>
                                <p class="mb-1 f-13 text-dark-grey">
                                    {{ Str::limit(strip_tags($report->summary), 100) }}
                                </p>
                                <div class="d-flex align-items-center">
                                    <span class="badge badge-light-blue mr-2">
                                        <i class="fa fa-clock mr-1"></i>{{ $report->total_hours }}
                                    </span>
                                    @if($report->blockers)
                                        <span class="badge badge-warning mr-2">Has Blockers</span>
                                    @endif
                                    <a href="{{ route('daily-reports.show', $report->id) }}" 
                                       class="btn btn-primary btn-sm openRightModal">
                                         View
                                     </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center p-4 text-lightest">
                            No reports submitted yet for this date.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
    <script>
        (function() {
            var namespace = '.missingReports';
            var $body = $('body');
            var dpInstance = null;

            function initDatePicker() {
                if (dpInstance) {
                    dpInstance.remove();
                    dpInstance = null;
                }

                if ($('#date-filter').length > 0) {
                    dpInstance = datepicker('#date-filter', {
                        position: 'bl',
                        maxDate: new Date(),
                        onSelect: (instance, date) => {
                            var formattedDate = moment(date).format('YYYY-MM-DD');
                            window.location.href = "{{ route('daily-reports.missing') }}?date=" + formattedDate;
                        }
                    });
                }
            }

            function setupMissingReports() {
                initDatePicker();

                $body.off(namespace);
                
                $body.on('click' + namespace, '#reset-filters', function() {
                    window.location.href = "{{ route('daily-reports.missing') }}";
                });

                if ("{{ request('date') }}" != "" && "{{ request('date') }}" != "{{ now()->toDateString() }}") {
                    $('#reset-filters').removeClass('d-none');
                }
            }

            // Run immediately on evaluation
            setupMissingReports();

            // Run on subsequent Turbo restorations
            var onTurboLoad = function() {
                setupMissingReports();
            };
            document.addEventListener('turbo:load', onTurboLoad);

            // Cleanup before Turbo caches the DOM to prevent orphaned instances
            document.addEventListener('turbo:before-cache', function cleanup() {
                if (dpInstance) {
                    dpInstance.remove();
                    dpInstance = null;
                }
                $body.off(namespace);
                document.removeEventListener('turbo:load', onTurboLoad);
            }, { once: true });
        })();
    </script>
@endpush
