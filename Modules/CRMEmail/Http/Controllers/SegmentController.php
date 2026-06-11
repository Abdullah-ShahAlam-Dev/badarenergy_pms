<?php

namespace Modules\CRMEmail\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\ClientCategory;
use App\Models\ClientSubCategory;
use App\Models\Country;
use App\Models\LeadCategory;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use Illuminate\Http\Request;
use Modules\CRMEmail\DataTables\EmailSegmentDataTable;
use Modules\CRMEmail\Entities\EmailSegment;
use Modules\CRMEmail\Http\Requests\StoreEmailSegment;
use Modules\CRMEmail\Http\Requests\UpdateEmailSegment;
use Modules\CRMEmail\Services\SegmentResolverService;

class SegmentController extends AccountBaseController
{
    private $resolverService;

    public function __construct(SegmentResolverService $resolverService)
    {
        parent::__construct();
        $this->pageTitle = 'Email Segments';
        $this->resolverService = $resolverService;

        $this->middleware(function ($request, $next) {
            $viewPermission = user()->permission('view_crm_email');
            abort_403(!in_array('admin', user_roles()) && $viewPermission == 'none');
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(EmailSegmentDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_crm_email');
        abort_403(!in_array('admin', user_roles()) && $viewPermission == 'none');

        return $dataTable->render('crmemail::segments.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $addPermission = user()->permission('add_crm_email');
        abort_403(!in_array('admin', user_roles()) && $addPermission == 'none');

        $this->clientCategories = ClientCategory::all();
        $this->clientSubCategories = ClientSubCategory::all();
        $this->leadStatuses = LeadStatus::all();
        $this->leadSources = LeadSource::all();
        $this->leadCategories = LeadCategory::all();
        $this->countries = Country::all();
        $this->designations = \Illuminate\Support\Facades\DB::table('client_contacts')
            ->where('company_id', company()->id)
            ->whereNotNull('title')
            ->where('title', '<>', '')
            ->distinct()
            ->pluck('title');

        if (request()->ajax()) {
            $html = view('crmemail::segments.ajax.create', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => 'Create Segment']);
        }

        return view('crmemail::segments.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmailSegment $request)
    {
        $addPermission = user()->permission('add_crm_email');
        abort_403(!in_array('admin', user_roles()) && $addPermission == 'none');

        $segment = new EmailSegment();
        $segment->company_id = company()->id;
        $segment->name = $request->name;
        $segment->sources = $request->sources;
        $segment->criteria = $request->criteria;
        $segment->added_by = user()->id;
        $segment->save();

        return Reply::successWithData('Email segment created successfully!', [
            'redirectUrl' => route('crm-email-segments.index')
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $this->segment = EmailSegment::findOrFail($id);

        $editPermission = user()->permission('edit_crm_email');
        abort_403(!in_array('admin', user_roles()) && !($editPermission == 'all' || ($editPermission == 'added' && $this->segment->added_by == user()->id)));

        $this->clientCategories = ClientCategory::all();
        $this->clientSubCategories = ClientSubCategory::all();
        $this->leadStatuses = LeadStatus::all();
        $this->leadSources = LeadSource::all();
        $this->leadCategories = LeadCategory::all();
        $this->countries = Country::all();
        $this->designations = \Illuminate\Support\Facades\DB::table('client_contacts')
            ->where('company_id', company()->id)
            ->whereNotNull('title')
            ->where('title', '<>', '')
            ->distinct()
            ->pluck('title');

        if (request()->ajax()) {
            $html = view('crmemail::segments.ajax.edit', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => 'Edit Segment']);
        }

        return view('crmemail::segments.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmailSegment $request, $id)
    {
        $segment = EmailSegment::findOrFail($id);

        $editPermission = user()->permission('edit_crm_email');
        abort_403(!in_array('admin', user_roles()) && !($editPermission == 'all' || ($editPermission == 'added' && $segment->added_by == user()->id)));

        $segment->name = $request->name;
        $segment->sources = $request->sources;
        $segment->criteria = $request->criteria;
        $segment->save();

        return Reply::successWithData('Email segment updated successfully!', [
            'redirectUrl' => route('crm-email-segments.index')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $segment = EmailSegment::findOrFail($id);

        $deletePermission = user()->permission('delete_crm_email');
        abort_403(!in_array('admin', user_roles()) && !($deletePermission == 'all' || ($deletePermission == 'added' && $segment->added_by == user()->id)));

        $activeCampaigns = $segment->campaigns()->whereIn('status', ['scheduled', 'sending', 'running', 'active'])->count();
        if ($activeCampaigns > 0) {
            return Reply::error('This segment is currently linked to one or more active or scheduled campaigns and cannot be deleted.');
        }

        $segment->delete();

        return Reply::success('Email segment deleted successfully!');
    }

    /**
     * Duplicate the specified resource.
     */
    public function duplicate($id)
    {
        $segment = EmailSegment::findOrFail($id);

        $addPermission = user()->permission('add_crm_email');
        abort_403(!in_array('admin', user_roles()) && $addPermission == 'none');

        $newSegment = new EmailSegment();
        $newSegment->company_id = company()->id;
        $newSegment->name = $segment->name . ' - Copy';
        $newSegment->sources = $segment->sources;
        $newSegment->criteria = $segment->criteria;
        $newSegment->added_by = user()->id;
        $newSegment->save();

        return Reply::success('Email segment duplicated successfully!');
    }

    /**
     * Estimate recipient count and list sample matches.
     */
    public function estimate(Request $request)
    {
        $sources = $request->sources ?: [];
        $criteria = $request->criteria ?: [];

        $count = $this->resolverService->estimateCount($sources, $criteria);
        $sampleRecipients = $this->resolverService->getSampleRecipients($sources, $criteria, 10);

        $this->count = $count;
        $this->sampleRecipients = $sampleRecipients;

        $html = view('crmemail::segments.ajax.preview', $this->data)->render();

        return Reply::dataOnly([
            'status' => 'success',
            'html' => $html,
            'count' => $count
        ]);
    }
}
