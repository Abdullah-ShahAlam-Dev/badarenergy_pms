<?php

namespace App\Http\Controllers;

use Client;
use Carbon\Carbon;
use App\Models\User;
use App\Helper\Reply;
use App\Models\Ticket;
use App\Models\Country;
use App\Models\TicketTag;
use App\Models\TicketType;
use App\Models\TicketGroup;
use App\Models\TicketReply;
use Illuminate\Http\Request;
use App\Models\TicketChannel;
use App\Models\TicketTagList;
use App\Models\TicketAgentGroups;
use Illuminate\Support\Facades\DB;
use App\DataTables\TicketDataTable;
use App\Models\TicketReplyTemplate;
use App\Http\Requests\Tickets\StoreTicket;
use App\Http\Requests\Tickets\UpdateTicket;

class TicketController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.tickets';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('tickets', $this->user->modules));

            return $next($request);
        });
    }

    public function index(TicketDataTable $dataTable)
    {
        $this->viewPermission = user()->permission('view_tickets');
        abort_403(!in_array($this->viewPermission, ['all', 'added', 'owned', 'both']));

        $managePermission = user()->permission('manage_ticket_agent');

        if (!request()->ajax()) {
            $this->channels = TicketChannel::all();
            $this->groups = $managePermission == 'none' ? null : TicketGroup::with(['enabledAgents' => function ($q) use ($managePermission) {

                if ($managePermission == 'added') {
                    $q->where('added_by', user()->id);
                }
                elseif ($managePermission == 'owned') {
                    $q->where('agent_id', user()->id);
                }
                elseif ($managePermission == 'both') {
                    $q->where('agent_id', user()->id)->orWhere('added_by', user()->id);
                }
                else {
                    $q->get();
                }

            }, 'enabledAgents.user'])->get();

            $this->types = TicketType::all();
            $this->tags = TicketTagList::all();
        }

        return $dataTable->render('tickets.index', $this->data);

    }

    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
        case 'delete':
            $this->deleteRecords($request);

            return Reply::success(__('messages.deleteSuccess'));
        case 'change-status':
            $this->changeBulkStatus($request);

            return Reply::success(__('messages.updateSuccess'));
        default:
            return Reply::error(__('messages.selectAction'));
        }
    }

    protected function deleteRecords($request)
    {
        abort_403(user()->permission('delete_tickets') != 'all');

        Ticket::whereIn('id', explode(',', $request->row_ids))->delete();
    }

    protected function changeBulkStatus($request)
    {
        abort_403(user()->permission('edit_tickets') != 'all');

        $tickets = Ticket::whereIn('id', explode(',', $request->row_ids));

        if (company()->ticket_closing_restriction == 'enabled' && $request->status == 'closed' && !in_array('admin', user_roles())) {
            $tickets->where('agent_assigned_by', user()->id);
        }

        $tickets->update(['status' => $request->status]);
    }

    public function create()
    {
        $this->addPermission = user()->permission('add_tickets');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->groups = TicketGroup::with('enabledAgents', 'enabledAgents.user')->get();
        $this->types = TicketType::all();
        $this->channels = TicketChannel::all();
        $this->templates = TicketReplyTemplate::all();
        $this->employees = User::allEmployees(null, true, 'all');
        $this->clients = User::allClients();
        $this->countries = countries();
        $this->lastTicket = Ticket::orderBy('id', 'desc')->first();
        $this->pageTitle = __('modules.tickets.addTicket');
        $ticket = new Ticket();

        if ($ticket->getCustomFieldGroupsWithFields()) {
            $this->fields = $ticket->getCustomFieldGroupsWithFields()->fields;
        }

        if (request()->default_client) {
            $this->client = User::find(request()->default_client);
        }

        if (request()->ajax()) {
            $html = view('tickets.ajax.create', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'tickets.ajax.create';

        return view('tickets.create', $this->data);

    }

    public function store(StoreTicket $request)
    {

        $ticket = new Ticket();
        $ticket->subject = $request->subject;
        $ticket->status = 'open';
        $ticket->user_id = ($request->requester_type == 'employee') ? $request->user_id : $request->client_id;


        $ticket->type_id = $request->type_id;
        $ticket->priority = $request->priority;
        $ticket->channel_id = $request->channel_id;
        $ticket->group_id = $request->group_id;
        $ticket->save();

        // Save first message
        $reply = new TicketReply();
        $reply->message = trim_editor($request->description);
        $reply->ticket_id = $ticket->id;
        $reply->user_id = $this->user->id; // Current logged in user
        $reply->save();

        // To add custom fields data
        if ($request->custom_fields_data) {
            $ticket->updateCustomFieldData($request->custom_fields_data);
        }

        // Save tags
        $tags = collect(json_decode($request->tags))->pluck('value');

        foreach ($tags as $tag) {
            $tag = TicketTagList::firstOrCreate([
                'tag_name' => $tag
            ]);
            $ticket->ticketTags()->attach($tag);
        }

        if ($request->cc_users) {
            $ticket->ccUsers()->sync($request->cc_users);
        }

        // Log search
        $this->logSearchEntry($ticket->id, $ticket->subject, 'tickets.show', 'ticket');

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('tickets.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['replyID' => $reply->id, 'redirectUrl' => $redirectUrl]);
    }

    public function show($ticketNumber)
    {
        $this->viewTicketPermission = user()->permission('view_tickets');
        $this->ticket = Ticket::where('ticket_number', $ticketNumber)
            ->first();

        abort_if(!$this->ticket, 404);

        $this->ticket = $this->ticket->withCustomFields();
        $this->pageTitle = __('app.menu.ticket') . '#' . $this->ticket->ticket_number;

        abort_403(!(
            $this->viewTicketPermission == 'all'
            || ($this->viewTicketPermission == 'added' && user()->id == $this->ticket->added_by)
            || ($this->viewTicketPermission == 'owned' && (user()->id == $this->ticket->user_id || $this->ticket->agent_id == user()->id))
            || ($this->viewTicketPermission == 'both' && (user()->id == $this->ticket->user_id || $this->ticket->agent_id == user()->id || $this->ticket->added_by == user()->id))
            || $this->ticket->ccUsers->contains(user()->id)
        ));

        $this->groups = TicketGroup::with('enabledAgents', 'enabledAgents.user')->get();
        $this->types = TicketType::all();
        $this->channels = TicketChannel::all();
        $this->templates = TicketReplyTemplate::all();
        $this->ticketChart = $this->ticketChartData($this->ticket->user_id);

        $this->employees = User::allEmployees(null, true, 'all');

        if ($this->ticket->getCustomFieldGroupsWithFields()) {
            $this->fields = $this->ticket->getCustomFieldGroupsWithFields()->fields;
        }

        return view('tickets.edit', $this->data);
    }

    public function ticketChartData($id)
    {
        $labels = ['open', 'pending', 'resolved', 'closed'];
        $data['labels'] = [__('app.open'), __('app.pending'), __('app.resolved'), __('app.closed')];
        $data['colors'] = ['#D30000', '#FCBD01', '#2CB100', '#1d82f5'];
        $data['values'] = [];

        foreach ($labels as $label) {
            $data['values'][] = Ticket::where('user_id', $id)->where('status', $label)->count();
        }

        return $data;
    }

    public function update(UpdateTicket $request, $id)
    {

        $ticket = Ticket::findOrFail($id);

        if (company()->ticket_closing_restriction == 'enabled' && $request->status == 'closed' && $ticket->agent_assigned_by != user()->id && !in_array('admin', user_roles())) {
            return Reply::error(__('messages.onlyAssignerCanCloseTicket'));
        }

        $ticket->status = $request->status;
        $ticket->save();

        $message = trim_editor($request->message);

        if ($message != '') {
            $reply = new TicketReply();
            $reply->message = $request->message;
            $reply->ticket_id = $ticket->id;
            $reply->user_id = $this->user->id; // Current logged in user
            $reply->save();

            return Reply::successWithData(__('messages.ticketReplySuccess'), ['reply_id' => $reply->id]);
        }

        return Reply::dataOnly(['status' => 'success']);
    }

    public function destroy($id)
    {
        $ticket = Ticket::findOrFail($id);

        $this->deleteTicketPermission = user()->permission('delete_tickets');
        abort_403(!(
            $this->deleteTicketPermission == 'all'
            || ($this->deleteTicketPermission == 'added' && user()->id == $ticket->added_by)
            || ($this->deleteTicketPermission == 'owned' && (user()->id == $ticket->agent_id || user()->id == $ticket->user_id))
            || ($this->deleteTicketPermission == 'both' && (user()->id == $ticket->agent_id || user()->id == $ticket->added_by || user()->id == $ticket->user_id))
        ));

        Ticket::destroy($id);

        return Reply::success(__('messages.deleteSuccess'));

    }

    public function updateOtherData(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        if (company()->ticket_closing_restriction == 'enabled' && $request->status == 'closed' && $ticket->agent_assigned_by != user()->id && !in_array('admin', user_roles())) {
            return Reply::error(__('messages.onlyAssignerCanCloseTicket'));
        }

        $ticket->group_id = $request->group_id;
        $ticket->type_id = $request->type_id;
        $ticket->priority = $request->priority;
        $ticket->channel_id = $request->channel_id;
        $ticket->status = $request->status;

        $agentGroupData = TicketAgentGroups::where('company_id', company()->id)
            ->where('status', 'enabled')
            ->where('group_id', request()->group_id)
            ->pluck('agent_id')
            ->toArray();
        $ticketData = $ticket->where('company_id', company()->id)
            ->where('group_id', request()->group_id)
            ->whereIn('agent_id', $agentGroupData)
            ->whereIn('status', ['open', 'pending'])
            ->whereNotNull('agent_id')
            ->pluck('agent_id')
            ->toArray();

        $diffAgent = array_diff($agentGroupData, $ticketData);

        if (is_null(request()->agent_id)) {

            if (!empty($diffAgent)) {
                $ticket->agent_id = current($diffAgent);

            } else {
                $agentDuplicateCount = 0;
                $agentDuplicateCount = array_count_values($ticketData);

                if($agentDuplicateCount > 0){  /** @phpstan-ignore-line */
                    $minVal = min($agentDuplicateCount);
                    $agentId = array_search($minVal, $agentDuplicateCount);
                    $ticket->agent_id = $agentId;
                }

            }
        } else {
            $ticket->agent_id = request()->agent_id;
        }

        $ticket->save();

        // Save tags
        $tags = collect(json_decode($request->tags))->pluck('value');
        TicketTag::where('ticket_id', $ticket->id)->delete();

        foreach ($tags as $tag) {
            $tag = TicketTagList::firstOrCreate([
                'tag_name' => $tag
            ]);
            $ticket->ticketTags()->attach($tag);
        }

        if ($request->cc_users) {
            $ticket->ccUsers()->sync($request->cc_users);
        } else {
            $ticket->ccUsers()->detach();
        }

        return Reply::success(__('messages.updateSuccess'));
    }

    public function refreshCount(Request $request)
    {
        $viewPermission = user()->permission('view_tickets');

        $tickets = Ticket::with('agent')
            ->leftJoin('ticket_cc_users as tcu', function($join) {
                $join->on('tcu.ticket_id', '=', 'tickets.id')
                     ->where('tcu.user_id', '=', user()->id);
            })
            ->select('tickets.*');

        if (!is_null($request->startDate) && $request->startDate != '') {
            try {
                $startDate = Carbon::createFromFormat($this->company->date_format, $request->startDate)->toDateString();
                $tickets->where(DB::raw('DATE(tickets.updated_at)'), '>=', $startDate);
            } catch (\Exception $e) {
                // Ignore invalid date format (e.g. full range string from Turbo cache)
            }
        }

        if (!is_null($request->endDate) && $request->endDate != '') {
            try {
                $endDate = Carbon::createFromFormat($this->company->date_format, $request->endDate)->toDateString();
                $tickets->where(DB::raw('DATE(tickets.updated_at)'), '<=', $endDate);
            } catch (\Exception $e) {
                // Ignore invalid date format
            }
        }

        if (!is_null($request->agentId) && $request->agentId != 'all') {
            $tickets->where('tickets.agent_id', '=', $request->agentId);
        }

        if (!is_null($request->priority) && $request->priority != 'all') {
            $tickets->where('tickets.priority', '=', $request->priority);
        }

        if (!is_null($request->channelId) && $request->channelId != 'all') {
            $tickets->where('tickets.channel_id', '=', $request->channelId);
        }

        if (!is_null($request->typeId) && $request->typeId != 'all') {
            $tickets->where('tickets.type_id', '=', $request->typeId);
        }

        if (!is_null($request->ticketStatus) && $request->ticketStatus != 'all' && $request->ticketStatus != '' && $request->ticketStatus != 0) {
            $tickets->where('tickets.status', '=', $request->ticketStatus);
        }

        if ($viewPermission == 'added') {
            $tickets->where(function ($query) {
                $query->where('tickets.added_by', '=', user()->id)
                    ->orWhereNotNull('tcu.ticket_id');
            });
        } elseif ($viewPermission == 'owned') {
            $tickets->where(function ($query) {
                $query->where('tickets.user_id', '=', user()->id)
                    ->orWhere('tickets.agent_id', '=', user()->id)
                    ->orWhereNotNull('tcu.ticket_id');
            });
        } elseif ($viewPermission == 'both') {
            $tickets->where(function ($query) {
                $query->where('tickets.user_id', '=', user()->id)
                    ->orWhere('tickets.added_by', '=', user()->id)
                    ->orWhere('tickets.agent_id', '=', user()->id)
                    ->orWhereNotNull('tcu.ticket_id');
            });
        }

        $tickets = $tickets->get();

        $openTickets = $tickets->filter(function ($value, $key) {
            return $value->status == 'open';
        })->count();

        $pendingTickets = $tickets->filter(function ($value, $key) {
            return $value->status == 'pending';
        })->count();

        $resolvedTickets = $tickets->filter(function ($value, $key) {
            return $value->status == 'resolved';
        })->count();

        $closedTickets = $tickets->filter(function ($value, $key) {
            return $value->status == 'closed';
        })->count();

        $totalTickets = $tickets->count();

        $ccTickets = Ticket::join('ticket_cc_users', function($join) {
            $join->on('ticket_cc_users.ticket_id', '=', 'tickets.id')
                 ->where('ticket_cc_users.user_id', '=', user()->id);
        })->count();

        $ticketData = [
            'totalTickets' => $totalTickets,
            'closedTickets' => $closedTickets,
            'openTickets' => $openTickets,
            'pendingTickets' => $pendingTickets,
            'resolvedTickets' => $resolvedTickets,
            'ccTickets' => $ccTickets,
            'unreadNotificationCount' => (isset($this->unreadNotificationCount) ? $this->unreadNotificationCount : (isset(user()->unreadNotifications) ? count(user()->unreadNotifications) : 0))
        ];

        return Reply::dataOnly($ticketData);
    }

    public function changeStatus(Request $request)
    {
        $ticket = Ticket::find($request->ticketId);
        $this->editTicketPermission = user()->permission('edit_tickets');

        abort_403(!(
            $this->editTicketPermission == 'all'
            || ($this->editTicketPermission == 'added' && user()->id == $ticket->added_by)
            || ($this->editTicketPermission == 'owned' && (user()->id == $ticket->user_id || $ticket->agent_id == user()->id))
            || ($this->editTicketPermission == 'both' && (user()->id == $ticket->user_id || $ticket->agent_id == user()->id || $ticket->added_by == user()->id))
        ));
        
        if (company()->ticket_closing_restriction == 'enabled' && $request->status == 'closed' && $ticket->agent_assigned_by != user()->id && !in_array('admin', user_roles())) {
            return Reply::error(__('messages.onlyAssignerCanCloseTicket'));
        }

        $ticket->update(['status' => $request->status]);

        return Reply::successWithData(__('messages.updateSuccess'), ['status' => 'success']);
    }

    public function agentGroup($id)
    {
        $groups = TicketGroup::with('enabledAgents', 'enabledAgents.user');
        $groups = $groups->where('id', $id)->first();
        $ticketNumber = request()->ticketNumber;
        $ticket = Ticket::where('ticket_number', $ticketNumber)->first();
        $groupData = [];
        $userData = [];

        if (isset($groups) && count($groups->enabledAgents) > 0)
        {
            $data = [];

            foreach ($groups->enabledAgents as $agent)
            {
                    $selected = (!is_null($ticket) && $agent->user->id == $ticket->agent_id) ? true : false;

                    $url = route('employees.show', [$agent->user->id]);
                    $userData[] = ['id' => $agent->user->id, 'value' => $agent->user->name, 'image' => $agent->user->image_url, 'link' => $url];

                $data[] = view('components.user-option', [
                    'user' => $agent->user,
                    'agent' => false,
                    'pill' => false,
                    'selected' => $selected,
                ])->render();
            }

            $groupData = $userData;
        }
        else
        {
            $data = '<option value="">--</option>';
        }

        return Reply::dataOnly(['data' => $data , 'groupData' => $groupData]);


    }

}
