<div id="ticket-detail-section">
    <div class="row">
        <div class="col-sm-12">
            <h3 class="heading-h1 mb-3">{{ ucfirst($ticket->subject) }}</h3>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-9">
            @foreach ($ticket->reply as $message)
                @php
                    $user = $message->user;
                @endphp
                <div class="card ticket-message rounded-0 border-0 mb-3 bg-white b-shadow-4" id="message-{{ $message->id }}">
                    <div class="card-horizontal">
                        <div class="card-img">
                            <img class="rounded-circle" src="{{ $user->image_url }}" alt="{{ $user->name }}" width="40">
                        </div>
                        <div class="card-body border-0 pl-3">
                            <div class="d-flex mb-2">
                                <h4 class="card-title f-13 f-w-500 text-dark mr-3">
                                    {{ $user->name }}
                                </h4>
                                <p class="card-date f-11 text-lightest mb-0">
                                    {{ $message->created_at->timezone($company->timezone)->translatedFormat($company->date_format . ' ' . $company->time_format) }}
                                </p>
                            </div>
                            <div class="card-text text-dark-grey f-13">
                                {!! nl2br($message->message) !!}
                            </div>

                            <div class="d-flex flex-wrap mt-3">
                                @foreach ($message->files as $file)
                                    <x-file-card :fileName="$file->filename" :dateAdded="$file->created_at->diffForHumans()">
                                        @if ($file->icon == 'images')
                                            <img src="{{ $file->file_url }}">
                                        @else
                                            <i class="fa {{ $file->icon }} text-lightest"></i>
                                        @endif

                                        <x-slot name="action">
                                            <div class="dropdown ml-auto file-action">
                                                <button class="btn btn-lg f-14 p-0 text-lightest text-capitalize rounded dropdown-toggle"
                                                    type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-ellipsis-h"></i>
                                                </button>

                                                <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0">
                                                    <a class="dropdown-item" target="_blank" href="{{ $file->file_url }}">@lang('app.view')</a>
                                                    <a class="dropdown-item" href="{{ route('ticket-files.download', md5($file->id)) }}">@lang('app.download')</a>
                                                </div>
                                            </div>
                                        </x-slot>
                                    </x-file-card>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="col-sm-3">
            <x-cards.data>
                <div class="col-12 px-0 pb-3 d-flex">
                    <p class="mb-0 text-lightest w-50 f-14 d-inline-block text-capitalize">@lang('modules.tickets.ticketDetail')</p>
                </div>
                <div class="col-12 px-0 pb-3 d-flex">
                    <p class="mb-0 text-lightest w-50 f-14 d-inline-block text-capitalize">@lang('app.status')</p>
                    <p class="mb-0 text-dark-grey w-50 f-14 d-inline">
                        <i class="fa fa-circle mr-1" style="color: {{ $ticket->status == 'open' ? '#FC1838' : ($ticket->status == 'pending' ? '#F1C411' : ($ticket->status == 'resolved' ? '#2CB100' : '#1D82F5')) }}"></i>
                        @lang('app.' . $ticket->status)
                    </p>
                </div>
                <div class="col-12 px-0 pb-3 d-flex">
                    <p class="mb-0 text-lightest w-50 f-14 d-inline-block text-capitalize">@lang('modules.tasks.priority')</p>
                    <p class="mb-0 text-dark-grey w-50 f-14 d-inline">
                        <i class="fa fa-circle mr-1" style="color: {{ $ticket->priority == 'high' ? '#FC1838' : ($ticket->priority == 'medium' ? '#F1C411' : '#2CB100') }}"></i>
                        @lang('app.' . $ticket->priority)
                    </p>
                </div>
                <div class="col-12 px-0 pb-3 d-flex">
                    <p class="mb-0 text-lightest w-50 f-14 d-inline-block text-capitalize">@lang('modules.tickets.requester')</p>
                    <p class="mb-0 text-dark-grey w-50 f-14 d-inline">
                        {{ $ticket->requester->name }}
                    </p>
                </div>
                @if($ticket->agent)
                <div class="col-12 px-0 pb-3 d-flex">
                    <p class="mb-0 text-lightest w-50 f-14 d-inline-block text-capitalize">@lang('modules.tickets.agent')</p>
                    <p class="mb-0 text-dark-grey w-50 f-14 d-inline">
                        {{ $ticket->agent->name }}
                    </p>
                </div>
                @endif

                @if(count($ticket->ccUsers) > 0)
                <div class="col-12 px-0 pb-3">
                    <p class="mb-2 text-lightest f-14 d-inline-block text-capitalize">CC</p>
                    <div class="d-flex flex-wrap">
                        @foreach ($ticket->ccUsers as $ccUser)
                            <div class="taskEmployeeImg rounded-circle mr-1 mb-1" data-toggle="tooltip" data-original-title="{{ $ccUser->name }}">
                                <img src="{{ $ccUser->image_url }}" alt="{{ $ccUser->name }}" width="25" height="25" class="rounded-circle">
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </x-cards.data>
        </div>
    </div>
</div>
