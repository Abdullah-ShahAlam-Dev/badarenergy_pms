@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/css/tagify.css') }}">

    <style>
        .message-action {
            visibility: hidden;
        }

        .ticket-left .card:hover .message-action {
            visibility: visible;
        }

        .file-action {
            visibility: hidden;
        }

        .file-card:hover .file-action {
            visibility: visible;
        }

        .frappe-chart .chart-legend {
            display: none;
        }

    </style>
    <script src="{{ asset('vendor/jquery/frappe-charts.min.iife.js') }}"></script>
    <script src="{{ asset('vendor/jquery/Chart.min.js') }}"></script>

@endpush

@php
$editTicketPermission = user()->permission('edit_tickets');
$deleteTicketPermission = user()->permission('delete_tickets');
$manageTypePermission = user()->permission('manage_ticket_type');
$manageAgentPermission = user()->permission('manage_ticket_agent');
$manageChannelPermission = user()->permission('manage_ticket_channel');
$manageGroupPermission = user()->permission('manage_ticket_groups');
$canClose = (company()->ticket_closing_restriction == 'disabled' || $ticket->agent_assigned_by == user()->id || in_array('admin', user_roles()));
@endphp

@section('filter-section')
    <!-- FILTER START -->
    <!-- TICKET HEADER START -->
    <div class="d-flex px-4 filter-box bg-white">

        <a href="javascript:;"
            class="d-flex align-items-center height-44 text-dark-grey text-capitalize border-right-grey pr-3 reply-button"><i
                class="fa fa-reply mr-0 mr-lg-2 mr-md-2"></i><span
                class="d-none d-lg-block d-md-block">@lang('app.reply')</span></a>

        <a href="javascript:;"
            class="d-flex align-items-center height-44 text-dark-grey text-capitalize border-right-grey px-3 btn-copy"
            data-clipboard-text="{{ route('front.ticket_detail', $ticket->hash) }}"><i
                class="fa fa-copy mr-0 mr-lg-2 mr-md-2"></i><span
                class="d-none d-lg-block d-md-block">@lang('modules.tickets.copyTicketLink')</span></a>

        {{-- <a href="javascript:;" class="d-flex align-items-center height-44 text-dark-grey text-capitalize border-right-grey px-3"><i
                class="fa fa-clipboard-list mr-0 mr-lg-2 mr-md-2"></i><span class="d-none d-lg-block d-md-block">add
                note</span></a> --}}

        {{-- <div id="ticket-closed" @if ($ticket->status == 'closed') style="display:none" @endif>
            <a href="javascript:;" data-status="closed"
                class="d-flex align-items-center height-44 text-dark-grey text-capitalize border-right-grey px-3 submit-ticket"><i
                    class="fa fa-times-circle mr-0 mr-lg-2 mr-md-2"></i><span
                    class="d-none d-lg-block d-md-block">@lang('app.close')</span></a>
        </div> --}}

        @if ($deleteTicketPermission == 'all' || ($deleteTicketPermission == 'owned' && $ticket->agent_id == user()->id))
            <a href="javascript:;"
                class="d-flex align-items-center height-44 text-dark-grey text-capitalize border-right-grey px-3 delete-ticket"><i
                    class="fa fa-trash mr-0 mr-lg-2 mr-md-2"></i><span
                    class="d-none d-lg-block d-md-block">@lang('app.delete')</span>
            </a>
        @endif

        <a onclick="openTicketsSidebar()"
            class="d-flex d-lg-none ml-auto align-items-center justify-content-center height-44 text-dark-grey text-capitalize border-left-grey pl-3"><i
                class="fa fa-ellipsis-v"></i></a>

    </div>
    <!-- FILTER END -->
    <!-- TICKET HEADER END -->
@endsection

@section('content')

    <!-- TICKET START -->
    <div class="ticket-wrapper bg-white border-top-0 d-lg-flex">

        <!-- TICKET LEFT START -->
        <div class="ticket-left w-100">
            <x-form id="updateTicket2" method="PUT">
                <input type="hidden" name="status" id="status" value="{{ $ticket->status }}">
                <input type="hidden" id="ticket_reply_id" value="">

                <!-- START -->
                <div class="d-flex justify-content-between align-items-center p-3 border-right-grey border-bottom-grey">
                    <span>
                        <p class="f-15 f-w-500 mb-0">{{ $ticket->subject }}</p>
                        <p class="f-11 text-lightest mb-0">@lang('modules.tickets.requestedOn')
                            {{ $ticket->created_at->timezone(company()->timezone)->translatedFormat(company()->date_format . ' ' . company()->time_format) }}
                        </p>
                    </span>
                    <span id="ticketStatusBadge">
                        @if ($ticket->status == 'open')
                            @php
                                $statusColor = 'red';
                            @endphp
                        @elseif($ticket->status == 'pending')
                            @php
                                $statusColor = 'yellow';
                            @endphp
                        @elseif($ticket->status == 'resolved')
                            @php
                                $statusColor = 'dark-green';
                            @endphp
                        @elseif($ticket->status == 'closed')
                            @php
                                $statusColor = 'blue';
                            @endphp
                        @endif
                        <p class="mb-0 text-capitalize ticket-status">
                           {!! $ticket->badge('span') !!}
                            <x-status :color="$statusColor" :value="__('app.'. $ticket->status)" />
                        </p>
                    </span>
                </div>
                <!-- END -->
                <!-- TICKET MESSAGE START -->
                <div class="ticket-msg border-right-grey" data-menu-vertical="1" data-menu-scroll="1"
                    data-menu-dropdown-timeout="500" id="ticketMsg">
                    @php $ccUserIds = $ticket->ccUsers->pluck('id')->toArray(); @endphp

                    @foreach ($ticket->reply as $reply)
                        <x-cards.ticket :message="$reply" :user="$reply->user" :ccUserIds="$ccUserIds" />
                    @endforeach

                </div>
                <!-- TICKET MESSAGE END -->

                <div class="col-md-12 border-top border-right d-none mb-5" id="reply-section">
                    <div class="form-group my-3">
                        @if ($ticket->requester->id != user()->id || (!is_null($ticket->agent_id) && $ticket->agent_id != user()->id))
                        <p class="f-w-500">
                            @lang('app.to'): {{ ($ticket->requester->id != user()->id) ? $ticket->requester->name : $ticket->agent->name }}
                        </p>
                        @endif
                        <div id="description"></div>
                        <textarea name="message" id="description-text" class="d-none"></textarea>
                    </div>
                    <div class="my-3">
                        <a class="f-15 f-w-500" href="javascript:;" id="add-file"><i
                                class="fa fa-paperclip font-weight-bold mr-1"></i>@lang('modules.projects.uploadFile')</a>
                    </div>
                    <x-forms.file-multiple class="mr-0 mr-lg-2 mr-md-2 upload-section d-none"
                        fieldLabel=""
                        fieldName="file[]" fieldId="ticket-file-upload-dropzone" />
                </div>

                <div class="ticket-reply-back justify-content-start px-lg-4 px-md-4 px-3 py-3  d-flex bg-white border-top-grey border-right-grey"
                    id="reply-section-action">

                    <x-forms.button-primary class="reply-button mr-3" icon="reply">@lang('app.reply')
                    </x-forms.button-primary>

                    <x-forms.link-secondary :link="route('tickets.index')" icon="arrow-left">@lang('app.back')
                    </x-forms.link-secondary>

                </div>
                <div class="ticket-reply-back flex-row justify-content-start px-lg-4 px-md-4 px-3 py-3 c-inv-btns bg-white border-top-grey border-right-grey d-none"
                    id="reply-section-action-2">
                    @if ($editTicketPermission == 'all'
                    || ($editTicketPermission == 'added' && user()->id == $ticket->added_by)
                    || ($editTicketPermission == 'owned' && (user()->id == $ticket->user_id || $ticket->agent_id == user()->id))
                    || ($editTicketPermission == 'both' && (user()->id == $ticket->user_id || $ticket->agent_id == user()->id || $ticket->added_by == user()->id)))
                        <div class="inv-action dropup mr-3">
                            <button class="btn-primary dropdown-toggle" type="button" data-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                @lang('app.submit')
                                <span><i class="fa fa-chevron-up f-15 text-white"></i></span>
                            </button>
                            <!-- DROPDOWN - INFORMATION -->
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuBtn" tabindex="0">
                                <li>
                                    <a class="dropdown-item f-14 text-dark submit-ticket" href="javascript:;"
                                        data-status="open">
                                        <x-status color="red" :value="__('modules.tickets.submitOpen')" />
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item f-14 text-dark submit-ticket" href="javascript:;"
                                        data-status="pending">
                                        <x-status color="yellow" :value="__('modules.tickets.submitPending')" />
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item f-14 text-dark submit-ticket" href="javascript:;"
                                        data-status="resolved">
                                        <x-status color="dark-green" :value="__('modules.tickets.submitResolved')" />
                                    </a>
                                </li>
                                @if ($canClose)
                                    <li>
                                        <a class="dropdown-item f-14 text-dark submit-ticket" href="javascript:;"
                                            data-status="closed">
                                            <x-status color="blue" :value="__('modules.tickets.submitClosed')" />
                                        </a>
                                    </li>
                                @endif

                            </ul>
                        </div>
                    @else
                        @php
                            $submitStatus = 'open';
                            // If user is a CC user and not the requester, preserve the current status instead of opening
                            if (isset($ccUserIds) && in_array(user()->id, $ccUserIds) && user()->id != $ticket->user_id) {
                                $submitStatus = $ticket->status;
                            }
                        @endphp
                        <x-forms.button-primary icon="check" data-status="{{ $submitStatus }}" class="submit-ticket mr-3">
                            @lang('app.submit')
                        </x-forms.button-primary>
                    @endif

                    @if (!in_array('client', user_roles()))
                        <div class="inv-action dropup mr-3">
                            <button class="btn-secondary dropdown-toggle" type="button" data-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-bolt f-15 mr-1"></i>
                                @lang('modules.tickets.applyTemplate')
                                <span><i class="fa fa-chevron-up f-15"></i></span>
                            </button>
                            <!-- DROPDOWN - INFORMATION -->
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuBtn" tabindex="0">
                                @forelse($templates as $template)
                                    <li><a href="javascript:;" data-template-id="{{ $template->id }}"
                                            class="dropdown-item f-14 text-dark apply-template">{{ ucfirst($template->reply_heading) }}</a>
                                    </li>
                                @empty
                                    <li><a class="dropdown-item f-14 text-dark">@lang('messages.noTemplateFound')</a></li>
                                @endforelse
                            </ul>
                        </div>
                    @endif

                    <x-forms.link-secondary id="cancel-reply" class="border-0" link="javascript:;">@lang('app.cancel')
                    </x-forms.link-secondary>

                </div>
            </x-form>
        </div>
        <!-- TICKET LEFT END -->

        @php
            $hasTicketEditPermission = ($editTicketPermission == 'all' 
                || ($editTicketPermission == 'added' && user()->id == $ticket->added_by)
                || ($editTicketPermission == 'owned' && ($ticket->agent_id == user()->id || $ticket->user_id == user()->id))
                || ($editTicketPermission == 'both' && ($ticket->agent_id == user()->id || $ticket->added_by == user()->id || $ticket->user_id == user()->id))
            );
            $isTicketCcUser = (isset($ccUserIds) && in_array(user()->id, $ccUserIds));
            $isCreatorOrRequester = (user()->id == $ticket->added_by || user()->id == $ticket->user_id);
        @endphp

        @if ($hasTicketEditPermission || $isTicketCcUser || $isCreatorOrRequester)
            <!-- TICKET RIGHT START -->
            <div class="mobile-close-overlay w-100 h-100" id="close-tickets-overlay"></div>
            <div class="ticket-right bg-white" id="ticket-detail-contact">
                <a class="d-block d-lg-none close-it" id="close-tickets"><i class="fa fa-times"></i></a>
                @if ($hasTicketEditPermission)
                <div id="tabs">
                    <nav class="tabs px-2 border-bottom-grey">
                        <div class="nav" id="nav-tab" role="tablist">
                            <a class="nav-item nav-link f-14 active" id="nav-detail-tab" data-toggle="tab"
                                href="#nav-details" role="tab" aria-controls="nav-email"
                                aria-selected="false">@lang('app.details')</a>
                            <a class="nav-item nav-link f-14" id="nav-contact-tab" data-toggle="tab" href="#nav-contact"
                                role="tab" aria-controls="nav-slack" aria-selected="true">@lang('app.contact')</a>
                            <a class="nav-item nav-link f-14" id="nav-other-tab" data-toggle="tab" href="#nav-other"
                                role="tab">@lang('app.other')</a>
                        </div>
                    </nav>
                </div>
                <div class="tab-content" id="nav-tabContent">
                    <!-- DETAILS START -->
                    <div class="tab-pane fade show active" id="nav-details" role="tabpanel"
                        aria-labelledby="nav-detail-tab">
                        <x-form id="updateTicket1">
                            <!-- TICKET FILTERS START -->
                            <div class="ticket-filters p-4 w-100 position-relative border-bottom">
                                <div class="more-filter-items mb-4">
                                    @foreach ($groups as $group)

                                            @endforeach
                                    <x-forms.label class="my-3" fieldId="group_id"
                                        :fieldLabel="__('modules.tickets.assignGroup')">
                                    </x-forms.label>
                                    <x-forms.input-group>
                                        <select class="form-control select-picker " name="group_id" id="group_id"
                                            data-live-search="true" data-container="body" data-size="8">
                                            @foreach ($groups as $group)
                                                <option @if($group->id == $ticket->group_id) selected @endif value="{{ $group->id }}">{{ mb_ucwords($group->group_name) }}</option>
                                            @endforeach
                                        </select>
                                        @if($manageGroupPermission == 'all')
                                            <x-slot name="append">
                                                <button id="manage-groups" type="button"
                                                    class="btn btn-outline-secondary border-grey">@lang('app.add')</button>
                                            </x-slot>
                                        @endif
                                    </x-forms.input-group>
                                </div>
                                <div class="more-filter-items mb-4">
                                    <x-forms.label class="my-3" fieldId="agent_id"
                                        :fieldLabel="__('modules.tickets.agent')">
                                    </x-forms.label>
                                    <x-forms.input-group>
                                        <select class="form-control select-picker " name="agent_id" id="agent_id"
                                            data-live-search="true" data-container="body" data-size="8">
                                            <option value="">--</option>
                                        </select>
                                        @if ($manageAgentPermission == 'all')
                                            <x-slot name="append">
                                                <button id="addAgent" type="button"
                                                    class="btn btn-outline-secondary border-grey">@lang('app.add')</button>
                                            </x-slot>
                                        @endif
                                    </x-forms.input-group>
                                </div>
                                <div class="more-filter-items mb-4">
                                    <x-forms.label class="my-3" fieldId="cc_users" :fieldLabel="'CC Users'">
                                    </x-forms.label>
                                    <x-forms.input-group>
                                        <select class="form-control select-picker" name="cc_users[]" id="cc_users"
                                                data-live-search="true" data-size="8" multiple data-container="body">
                                            @foreach ($employees as $employee)
                                                <x-user-option :user="$employee" :selected="in_array($employee->id, $ticket->ccUsers->pluck('id')->toArray())" />
                                            @endforeach
                                        </select>
                                    </x-forms.input-group>
                                </div>
                                <div class="more-filter-items">
                                    <x-forms.select fieldId="priority" :fieldLabel="__('modules.tasks.priority')"
                                        fieldName="priority" data-container="body">
                                        <option @if ($ticket->priority == 'low') selected @endif value="low"
                                            data-content="<i class='fa fa-circle mr-2 text-dark-green'></i> {{ __('app.low')}}"
                                            >@lang('app.low')</option>
                                        <option @if ($ticket->priority == 'medium') selected @endif value="medium"
                                            data-content="<i class='fa fa-circle mr-2 text-blue'></i> {{ __('app.medium')}}"
                                            >@lang('app.medium')</option>
                                        <option @if ($ticket->priority == 'high') selected @endif value="high"
                                            data-content="<i class='fa fa-circle mr-2 text-warning'></i> {{ __('app.high')}}"
                                            >@lang('app.high')</option>
                                        <option @if ($ticket->priority == 'urgent') selected @endif value="urgent"
                                            data-content="<i class='fa fa-circle mr-2 text-red'></i> {{ __('app.urgent')}}"
                                            >@lang('app.urgent')</option>
                                    </x-forms.select>
                                </div>
                                <div class="more-filter-items mb-4">
                                    <x-forms.label class="my-3" fieldId="ticket_type_id"
                                        :fieldLabel="__('modules.invoices.type')">
                                    </x-forms.label>
                                    <x-forms.input-group>
                                        <select class="form-control select-picker" name="type_id" id="ticket_type_id"
                                            data-container="body" data-live-search="true" data-size="8">
                                            <option value="">--</option>
                                            @foreach ($types as $type)
                                                <option @if ($type->id == $ticket->type_id) selected @endif value="{{ $type->id }}">
                                                    {{ mb_ucwords($type->type) }}</option>
                                            @endforeach
                                        </select>
                                        @if ($manageTypePermission == 'all')
                                            <x-slot name="append">
                                                <button id="addTicketType" type="button"
                                                    class="btn btn-outline-secondary border-grey">@lang('app.add')</button>
                                            </x-slot>
                                        @endif
                                    </x-forms.input-group>
                                </div>
                                <div class="more-filter-items mb-4">
                                    <x-forms.label class="my-3" fieldId="ticket_channel_id"
                                        :fieldLabel="__('modules.tickets.channelName')">
                                    </x-forms.label>
                                    <x-forms.input-group>
                                        <select class="form-control select-picker" name="channel_id" id="ticket_channel_id"
                                            data-container="body" data-live-search="true" data-size="8">
                                            <option value="">--</option>
                                            @foreach ($channels as $channel)
                                                <option @if ($channel->id == $ticket->channel_id) selected @endif value="{{ $channel->id }}">
                                                    {{ mb_ucwords($channel->channel_name) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if ($manageChannelPermission == 'all')
                                            <x-slot name="append">
                                                <button id="addChannel" type="button"
                                                    class="btn btn-outline-secondary border-grey">@lang('app.add')</button>
                                            </x-slot>
                                        @endif
                                    </x-forms.input-group>
                                </div>
                                <div class="more-filter-items">
                                    <x-forms.select fieldId="ticket-status" :fieldLabel="__('app.status')"
                                        fieldName="status" data-container="body">
                                        <option @if ($ticket->status == 'open') selected @endif value="open"
                                            data-content="<i class='fa fa-circle mr-2 text-red'></i>{{ __('app.open') }}">
                                            @lang('app.open')
                                        </option>
                                        <option @if ($ticket->status == 'pending') selected @endif value="pending"
                                            data-content="<i class='fa fa-circle mr-2 text-yellow'></i>{{ __('app.pending') }}">
                                            @lang('app.pending')</option>
                                        <option @if ($ticket->status == 'resolved') selected @endif value="resolved"
                                            data-content="<i class='fa fa-circle mr-2 text-dark-green'></i>{{ __('app.resolved') }}">
                                            @lang("app.resolved")</option>
                                        @if ($canClose)
                                            <option @if ($ticket->status == 'closed') selected @endif value="closed"
                                                data-content="<i class='fa fa-circle mr-2 text-blue'></i>{{ __('app.closed') }}">
                                                @lang('app.closed')</option>
                                        @endif
                                    </x-forms.select>
                                </div>
                                <div class="more-filter-items">
                                    <x-forms.label class="my-3" fieldId="tags"
                                        :fieldLabel="__('modules.tickets.tags')">
                                    </x-forms.label>
                                    <input type="text" name="tags" id="tags" class="rounded f-14"
                                        value="{{ implode(',', $ticket->ticketTags->pluck('tag_name')->toArray()) }}">
                                </div>
                            </div>
                            <!-- TICKET FILTERS END -->
                            <!-- TICKET UPDATE START -->
                            <div class="ticket-update bg-white px-4 py-3">
                                <x-forms.button-primary class="ml-none d-flex submit-ticket-2 fixed-bottom">
                                    @lang('app.update')
                                </x-forms.button-primary>
                            </div>
                            <!-- TICKET UPDATE END -->
                        </x-form>
                    </div>
                    <!-- DETAILS END -->
                    <!-- CONTACT START -->
                    <div class="tab-pane fade" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab">
                        <!-- CONTACT OWNER START  -->
                        <div class="card-horizontal bg-white-shade ticket-contact-owner p-4 rounded-0">
                            <div class="card-img mr-3">
                                <img class="___class_+?88___" src="{{ $ticket->requester->image_url }}"
                                    alt="{{ mb_ucwords($ticket->requester->name) }}">
                            </div>
                            <div class="card-body border-0 p-0 w-100">
                                <h4 class="card-title f-14 font-weight-normal mb-0">
                                    <a class="text-dark-grey" @if ($ticket->requester->hasRole('employee'))
                                        href="{{ route('employees.show', $ticket->requester->id) }}"
                                    @else
                                        href="{{ route('clients.show', $ticket->requester->id) }}"
                                    @endif>
                                    {{ mb_ucwords($ticket->requester->name) }}
                                    </a>
                                </h4>
                                @if ($ticket->requester->country_id)
                                    <span class="card-text f-12 text-dark-grey text-capitalize d-flex align-items-center">
                                        <span class='flag-icon flag-icon-{{ strtolower($ticket->requester->country->iso) }} mr-2'></span>
                                        {{ $ticket->requester->country->nicename }}
                                    </span>
                                @else
                                    --
                                @endif

                            </div>
                        </div>
                        <!-- CONTACT OWNER END  -->
                        <!-- TICKET CHART START  -->
                        <x-cards.data :title="__('app.menu.tickets')" padding="false">
                            <x-pie-chart id="ticket-chart" :labels="$ticketChart['labels']" :values="$ticketChart['values']"
                                :colors="$ticketChart['colors']" height="200" width="220" />
                        </x-cards.data>
                        <!-- TICKET CHART END  -->
                        <!-- RECENT TICKETS START -->
                        <div class="card pt-4 px-4 border-grey border-left-0 border-right-0 rounded-0">
                            <div class="card-title">
                                <h4 class="f-18 f-w-500 text-capitalize mb-3">@lang('modules.tickets.recentTickets')</h4>
                            </div>
                            <!-- CHART START -->
                            <div class="card-body p-0">
                                <div class="recent-ticket position-relative" data-menu-vertical="1" data-menu-scroll="1"
                                    data-menu-dropdown-timeout="500" id="recentTickets">
                                    <div class="recent-ticket-inner position-relative">
                                        @foreach ($ticket->requester->tickets as $item)
                                            <div class="r-t-items d-flex">
                                                <div class="r-t-items-left text-lightest f-21">
                                                    <i class="fa fa-ticket-alt"></i>
                                                </div>
                                                <div class="r-t-items-right ">
                                                    <h3 class="f-14 font-weight-bold">
                                                        <a class="text-dark"
                                                            href="{{ route('tickets.show', $item->id) }}">{{ $item->subject }}</a>
                                                    </h3>
                                                    <span class="d-flex mb-1">
                                                        <span class="mr-3 f-w-500 text-dark-grey">#{{ $item->id }}</span>
                                                        @if ($item->status == 'open')
                                                            @php
                                                                $statusColor = 'red';
                                                            @endphp
                                                        @elseif($item->status == 'pending')
                                                            @php
                                                                $statusColor = 'yellow';
                                                            @endphp
                                                        @elseif($item->status == 'resolved')
                                                            @php
                                                                $statusColor = 'dark-green';
                                                            @endphp
                                                        @elseif($item->status == 'closed')
                                                            @php
                                                                $statusColor = 'blue';
                                                            @endphp
                                                        @endif
                                                        <span class="f-13 text-darkest-grey text-capitalize">
                                                            <x-status :color="$statusColor" :value="$item->status" />
                                                        </span>

                                                    </span>
                                                    <p class="f-12 text-dark-grey">
                                                        {{ $item->created_at->timezone(company()->timezone)->translatedFormat(company()->date_format . ' ' . company()->time_format) }}
                                                    </p>
                                                </div>
                                            </div><!-- item end -->
                                        @endforeach

                                    </div>
                                </div>
                            </div>
                            <!-- CHART END -->
                        </div>
                        <!-- RECENT TICKETS END -->
                    </div>
                <!-- CONTACT END -->

                    <div class="tab-pane fade" id="nav-other" role="tabpanel" aria-labelledby="nav-other-tab">
                        <x-form id="updateOther">
                            <!-- TICKET FILTERS START -->
                            <div class="ticket-filters p-4 w-100 position-relative border-bottom">
                                <x-forms.custom-field-show :fields="$fields" :model="$ticket"></x-forms.custom-field-show>
                            </div>
                            <!-- TICKET FILTERS END -->
                        </x-form>
                    </div>
                </div>
                @else
                    <div class="card pt-4 px-4 border-grey border-left-0 border-right-0 border-top-0 rounded-0">
                        <div class="card-title">
                            <h4 class="f-18 f-w-500 text-capitalize mb-3">CC Users</h4>
                        </div>
                        <div class="card-body p-0 pb-4">
                            @foreach ($ticket->ccUsers as $item)
                                <div class="mb-3">
                                    <x-employee :user="$item" />
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            <!-- TICKET RIGHT END -->
        @endif
    </div>
<!-- TICKET END -->

@endsection

@push('scripts')
    <script src="{{ asset('vendor/jquery/dropzone.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery/tagify.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery/clipboard.min.js') }}"></script>

<script>
    (function() {
        var $body = $('body');
        var namespace = '.ticketsEdit';
        var ticketDropzone;
        var tagify;

        var clipboard = new ClipboardJS('.btn-copy');

        clipboard.on('success', function(e) {
            Swal.fire({
                icon: 'success',
                text: '@lang("app.copied")',
                toast: true,
                position: 'top-end',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false,
                customClass: { confirmButton: 'btn btn-primary' },
                showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' },
            });
        });

        // Cleanup before re-binding
        $body.off(namespace);

        // Quill must be initialized lazily when reply section is shown
        // (it cannot initialize on display:none elements)
        var quillInitialized = false;
        function initQuillIfNeeded() {
            if (!quillInitialized && typeof quillImageLoad === 'function') {
                quillImageLoad('#description');
                quillInitialized = true;
            }
        }

        $body.on('click' + namespace, '.reply-button', function() {
            $('#reply-section-action').toggleClass('d-none d-flex');
            $('#reply-section-action-2').toggleClass('d-none flex-row');
            $('#reply-section').removeClass('d-none');
            initQuillIfNeeded();
            window.scrollTo(0, document.body.scrollHeight);
        });

        $body.on('click' + namespace, '#cancel-reply', function() {
            $('#reply-section-action').toggleClass('d-none d-flex');
            $('#reply-section-action-2').toggleClass('d-none flex-row');
            $('#reply-section').addClass('d-none');
            window.scrollTo(0, document.body.scrollHeight);
        });

        $body.on('click' + namespace, '#add-file', function() {
            $('.upload-section').removeClass('d-none');
            $(this).addClass('d-none');
            window.scrollTo(0, document.body.scrollHeight);
        });

        var tagInput = document.querySelector('input[name=tags]');
        if (tagInput) tagify = new Tagify(tagInput);

        if (Dropzone.instances.length > 0) {
            Dropzone.instances.forEach(function(dz) {
                if (dz.element.id === 'ticket-file-upload-dropzone') dz.destroy();
            });
        }

        ticketDropzone = new Dropzone("div#ticket-file-upload-dropzone", {
            dictDefaultMessage: "{{ __('app.dragDrop') }}",
            url: "{{ route('ticket-files.store') }}",
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            paramName: "file",
            maxFilesize: DROPZONE_MAX_FILESIZE,
            maxFiles: DROPZONE_MAX_FILES,
            autoProcessQueue: false,
            uploadMultiple: true,
            addRemoveLinks: true,
            parallelUploads: DROPZONE_MAX_FILES,
            acceptedFiles: DROPZONE_FILE_ALLOW,
            init: function() {
                ticketDropzone = this;
            }
        });

        ticketDropzone.on('sending', function(file, xhr, formData) {
            var ids = $('#ticket_reply_id').val();
            formData.append('ticket_reply_id', ids);
            formData.append('ticket_id', '{{ $ticket->id }}');
            $.easyBlockUI();
        });

        ticketDropzone.on('queuecomplete', function() {
            window.location.href = "{{ route('tickets.show', $ticket->ticket_number) }}";
        });

        ticketDropzone.on('error', function (file, message) {
            this.removeFile(file);
            var grp = $(this.element).closest(".form-group");
            grp.find(".help-block").remove();
            grp.append('<div class="help-block invalid-feedback">' + message + '</div>');
            grp.addClass("has-error");
            grp.find("label").addClass("is-invalid");
        });

        $body.on('click' + namespace, '.submit-ticket', function() {
            var $description = $('#description');
            if ($description.length > 0 && $description[0].children[0]) {
                var note = $description[0].children[0].innerHTML;
                $('#description-text').val(note);
            }

            var status = $(this).data('status');
            $('#status').val(status);

            $.easyAjax({
                url: "{{ route('tickets.update', $ticket->id) }}",
                container: '#ticketMsg',
                type: "POST",
                blockUI: true,
                data: $('#updateTicket2').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        if (ticketDropzone && ticketDropzone.getQueuedFiles().length > 0) {
                            $('#ticket_reply_id').val(response.reply_id);
                            ticketDropzone.processQueue();
                        } else {
                            window.location.href = "{{ route('tickets.show', $ticket->ticket_number) }}";
                        }
                    }
                }
            });
        });

        $body.on('click' + namespace, '.submit-ticket-2', function(e) {
            e.preventDefault();
            $.easyAjax({
                url: "{{ route('tickets.update_other_data', $ticket->id) }}",
                container: '#updateTicket1',
                type: "POST",
                blockUI: true,
                disableButton: true,
                buttonSelector: ".submit-ticket-2",
                data: $('#updateTicket1').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        var status = $('#ticket-status').val();
                        $('#ticket-closed').toggle(status != 'closed');
                        
                        var colors = { 'open': 'red', 'pending': 'yellow', 'resolved': 'dark-green', 'closed': 'blue' };
                        var labels = { 
                            'open': "@lang('app.open')", 
                            'pending': "@lang('app.pending')", 
                            'resolved': "@lang('app.resolved')", 
                            'closed': "@lang('app.closed')" 
                        };

                        var color = colors[status] || 'red';
                        var label = labels[status] || "@lang('app.open')";
                        $('#ticketStatusBadge').html('<i class="fa fa-circle mr-2 text-' + color + '"></i>' + label);
                    }
                }
            });
        });

        $body.on('click' + namespace, '.apply-template', function() {
            var templateId = $(this).data('template-id');
            $.easyAjax({
                url: "{{ route('replyTemplates.fetchTemplate') }}",
                data: { templateId: templateId },
                success: function(response) {
                    if (response.status == "success") {
                        var container = $('#description').get(0);
                        if (container) {
                            var quill = Quill.find(container) || new Quill(container);
                            quill.clipboard.dangerouslyPasteHTML(0, response.replyText);
                        }
                    }
                }
            });
        });

        $body.on('click' + namespace, '.delete-file', function() {
            var id = $(this).data('row-id');
            var $replyFile = $(this);
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
                    var url = "{{ route('ticket-files.destroy', ':id') }}".replace(':id', id);
                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: { '_token': "{{ csrf_token() }}", '_method': 'DELETE' },
                        success: function(response) {
                            if (response.status == "success") $replyFile.closest('.card').remove();
                        }
                    });
                }
            });
        });

        $body.on('click' + namespace, '.delete-ticket', function() {
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
                    $.easyAjax({
                        type: 'POST',
                        url: "{{ route('tickets.destroy', $ticket->id) }}",
                        data: { '_token': "{{ csrf_token() }}", '_method': 'DELETE' },
                        success: function(response) {
                            if (response.status == "success") window.location.href = "{{ route('tickets.index') }}";
                        }
                    });
                }
            });
        });

        $body.on('click' + namespace, '.delete-message', function() {
            var id = $(this).data('row-id');
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
                    var url = "{{ route('ticket-replies.destroy', ':id') }}".replace(':id', id);
                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: { '_token': "{{ csrf_token() }}", '_method': 'DELETE' },
                        success: function(response) {
                            if (response.status == "success") $('#message-' + id).remove();
                        }
                    });
                }
            });
        });

        $body.on('click' + namespace, '#addAgent', function() {
            $.ajaxModal(MODAL_XL, "{{ route('ticket-agents.create') }}");
        });

        $body.on('click' + namespace, '#addChannel', function() {
            $.ajaxModal(MODAL_LG, "{{ route('ticketChannels.create') }}");
        });

        $body.on('click' + namespace, '#addTicketType', function() {
            $.ajaxModal(MODAL_LG, "{{ route('ticketTypes.create') }}");
        });

        $body.on('click' + namespace, '#manage-groups', function() {
            $.ajaxModal(MODAL_LG, "{{ route('ticket-groups.create') }}");
        });

        function scrollToBottom(divId) {
            var myDiv = document.getElementById(divId);
            if (myDiv) myDiv.scrollTop = myDiv.scrollHeight;
        }
        scrollToBottom('ticketMsg');

        // ── CC ↔ Agent bidirectional badge helpers ──────────────────────────────
        function cacheOriginalContent(selector) {
            $(selector + ' option').each(function () {
                if (!$(this).data('original-content')) {
                    var existing = $(this).attr('data-content');
                    $(this).data('original-content', existing || null);
                }
            });
        }

        function updateCcFromAgent() {
            var agentId = $('#agent_id').val();
            $('#cc_users option').each(function () {
                var orig = $(this).data('original-content');
                if (agentId && $(this).val() == agentId) {
                    $(this).prop('disabled', true).prop('selected', false);
                    var badge = orig
                        ? orig + ' <span style="background:#17a2b8;color:#fff;border-radius:3px;padding:1px 5px;font-size:10px;font-weight:600;">Assigned</span>'
                        : $(this).text() + ' <span style="background:#17a2b8;color:#fff;border-radius:3px;padding:1px 5px;font-size:10px;font-weight:600;">Assigned</span>';
                    $(this).attr('data-content', badge);
                } else {
                    $(this).prop('disabled', false);
                    if (orig) $(this).attr('data-content', orig);
                    else $(this).removeAttr('data-content');
                }
            });
            $('#cc_users').selectpicker('refresh');
        }

        function updateAgentFromCc() {
            var ccUsers = $('#cc_users').val() || [];
            $('#agent_id option').each(function () {
                var orig = $(this).data('original-content');
                if (ccUsers.includes($(this).val())) {
                    $(this).prop('disabled', true).prop('selected', false);
                    var badge = orig
                        ? orig + ' <span style="background:#6c757d;color:#fff;border-radius:3px;padding:1px 5px;font-size:10px;font-weight:600;">CC</span>'
                        : $(this).text() + ' <span style="background:#6c757d;color:#fff;border-radius:3px;padding:1px 5px;font-size:10px;font-weight:600;">CC</span>';
                    $(this).attr('data-content', badge);
                } else {
                    $(this).prop('disabled', false);
                    if (orig) $(this).attr('data-content', orig);
                    else $(this).removeAttr('data-content');
                }
            });
            $('#agent_id').selectpicker('refresh');
        }
        // ────────────────────────────────────────────────────────────────────────

        function getAgents(groupId) {
            var url = "{{ route('tickets.agent_group', ':id').'?ticketNumber='.$ticket->ticket_number}}".replace(':id', groupId);
            if (!groupId) return;
            $.easyAjax({
                url: url,
                type: "GET",
                success: function(response) {
                    var options = response.data;
                    $('#agent_id').html('<option value="">--</option>' + options).selectpicker('refresh');

                    // Re-cache and sync badges now that agent list has changed
                    cacheOriginalContent('#agent_id');
                    updateCcFromAgent();
                    updateAgentFromCc();
                }
            });
        }

        $body.on('change' + namespace, '#group_id', function() {
            getAgents($(this).val());
        });

        $body.on('change' + namespace, '#agent_id', function() {
            updateCcFromAgent();
        });

        $body.on('change' + namespace, '#cc_users', function() {
            updateAgentFromCc();
        });

        // Rich selectpicker for CC Users
        $("#cc_users").selectpicker({
            actionsBox: true,
            selectAllText: "{{ __('modules.permission.selectAll') }}",
            deselectAllText: "{{ __('modules.permission.deselectAll') }}",
            multipleSeparator: " ",
            selectedTextFormat: "count > 8",
            countSelectedText: function(selected) { return selected + " {{ __('app.membersSelected') }} "; }
        });

        // Initial cache + sync (runs after CC selectpicker is initialised above)
        cacheOriginalContent('#cc_users');
        getAgents($('#group_id').val()); // will cache agent options and sync after AJAX


        window.addEventListener('turbo:before-cache', function cleanup() {
            $body.off(namespace);
            if (ticketDropzone) ticketDropzone.destroy();
            if (tagify) tagify.destroy();
            
            // Cleanup Chart.js
            var chartInstance = Chart.getChart("ticket-chart");
            if (chartInstance) chartInstance.destroy();

            if (typeof destroy_editor === 'function') destroy_editor('#description');
            else if (typeof destory_editor === 'function') destory_editor('#description');

            window.removeEventListener('turbo:before-cache', cleanup);
        }, { once: true });
    })();
</script>
@endpush
