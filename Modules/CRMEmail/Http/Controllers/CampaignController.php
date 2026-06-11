<?php

namespace Modules\CRMEmail\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Modules\CRMEmail\DataTables\CampaignDataTable;
use Modules\CRMEmail\Entities\Campaign;
use Modules\CRMEmail\Entities\CrmEmailSetting;
use Modules\CRMEmail\Entities\EmailMarketingTemplate;
use Modules\CRMEmail\Entities\EmailSegment;
use Modules\CRMEmail\Http\Requests\StoreCampaign;
use Modules\CRMEmail\Http\Requests\UpdateCampaign;
use Modules\CRMEmail\Jobs\LaunchCampaignJob;

class CampaignController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Email Campaigns';

        $this->middleware(function ($request, $next) {
            $viewPermission = user()->permission('view_crm_email');
            abort_403(!in_array('admin', user_roles()) && $viewPermission == 'none');
            return $next($request);
        });
    }

    /**
     * Display a listing of all campaigns in a DataTable.
     *
     * @param CampaignDataTable $dataTable
     * @return mixed
     */
    public function index(CampaignDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_crm_email');
        abort_403(!in_array('admin', user_roles()) && $viewPermission == 'none');

        return $dataTable->render('crmemail::campaigns.index', $this->data);
    }

    /**
     * Show the form for creating a new campaign.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $addPermission = user()->permission('add_crm_email');
        abort_403(!in_array('admin', user_roles()) && $addPermission == 'none');

        $this->templates = EmailMarketingTemplate::where('company_id', company()->id)
            ->where('status', 'active')
            ->orderBy('title')
            ->get();

        $this->segments = EmailSegment::where('company_id', company()->id)
            ->orderBy('name')
            ->get();

        if (request()->ajax()) {
            $html = view('crmemail::campaigns.ajax.create', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => 'Create Campaign']);
        }

        return view('crmemail::campaigns.create', $this->data);
    }

    /**
     * Store a newly created campaign in storage.
     *
     * @param StoreCampaign $request
     * @return array
     */
    public function store(StoreCampaign $request)
    {
        $addPermission = user()->permission('add_crm_email');
        abort_403(!in_array('admin', user_roles()) && $addPermission == 'none');

        $template = EmailMarketingTemplate::where('id', $request->template_id)
            ->where('company_id', company()->id)
            ->firstOrFail();

        $campaign = new Campaign();
        $campaign->company_id   = company()->id;
        $campaign->name         = $request->name;
        $campaign->template_id  = $template->id;
        $campaign->email_body   = $request->email_body ?: $template->content;
        $campaign->segment_id   = $request->segment_id;
        $campaign->status       = 'draft';
        $campaign->scheduled_at = $request->scheduled_at ? \Carbon\Carbon::createFromFormat(company()->date_format, $request->scheduled_at)->format('Y-m-d') . ' 00:00:00' : null;
        $campaign->added_by     = user()->id;
        $campaign->save();

        return Reply::successWithData('Campaign created successfully!', [
            'redirectUrl' => route('crm-email-campaigns.index'),
        ]);
    }

    /**
     * Show the form for editing the specified campaign.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->campaign = Campaign::where('company_id', company()->id)->findOrFail($id);

        $editPermission = user()->permission('edit_crm_email');
        abort_403(
            !in_array('admin', user_roles())
            && !($editPermission == 'all' || ($editPermission == 'added' && $this->campaign->added_by == user()->id))
        );

        // Only draft campaigns can be edited.
        abort_403($this->campaign->status !== 'draft');

        $this->templates = EmailMarketingTemplate::where('company_id', company()->id)
            ->where('status', 'active')
            ->orderBy('title')
            ->get();

        $this->segments = EmailSegment::where('company_id', company()->id)
            ->orderBy('name')
            ->get();

        if (request()->ajax()) {
            $html = view('crmemail::campaigns.ajax.edit', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => 'Edit Campaign']);
        }

        return view('crmemail::campaigns.edit', $this->data);
    }

    /**
     * Update the specified campaign in storage.
     *
     * @param UpdateCampaign $request
     * @param int $id
     * @return array
     */
    public function update(UpdateCampaign $request, $id)
    {
        $campaign = Campaign::where('company_id', company()->id)->findOrFail($id);

        $editPermission = user()->permission('edit_crm_email');
        abort_403(
            !in_array('admin', user_roles())
            && !($editPermission == 'all' || ($editPermission == 'added' && $campaign->added_by == user()->id))
        );

        abort_403($campaign->status !== 'draft');

        $campaign->name         = $request->name;
        $campaign->template_id  = $request->template_id;
        $campaign->email_body   = $request->email_body;
        $campaign->segment_id   = $request->segment_id;
        $campaign->scheduled_at = $request->scheduled_at ? \Carbon\Carbon::createFromFormat(company()->date_format, $request->scheduled_at)->format('Y-m-d') . ' 00:00:00' : null;
        $campaign->save();

        return Reply::successWithData('Campaign updated successfully!', [
            'redirectUrl' => route('crm-email-campaigns.index'),
        ]);
    }

    /**
     * Remove the specified campaign from storage.
     *
     * @param int $id
     * @return array
     */
    public function destroy($id)
    {
        $campaign = Campaign::where('company_id', company()->id)->findOrFail($id);

        $deletePermission = user()->permission('delete_crm_email');
        abort_403(
            !in_array('admin', user_roles())
            && !($deletePermission == 'all' || ($deletePermission == 'added' && $campaign->added_by == user()->id))
        );

        // Cannot delete a campaign that is actively sending.
        abort_403($campaign->status === 'sending');

        $campaign->delete();

        return Reply::success('Campaign deleted successfully!');
    }

    /**
     * Launch the campaign by dispatching the master LaunchCampaignJob.
     *
     * POST /account/crm-email-campaigns/{id}/launch
     *
     * @param int $id
     * @return array
     */
    public function launch($id)
    {
        $campaign = Campaign::where('company_id', company()->id)->findOrFail($id);

        $addPermission = user()->permission('add_crm_email');
        abort_403(!in_array('admin', user_roles()) && $addPermission == 'none');

        // Only allow launching from draft or scheduled state.
        if (!in_array($campaign->status, ['draft', 'scheduled'])) {
            return Reply::error('Campaign cannot be launched from its current status: ' . $campaign->status);
        }

        $campaign->update([
            'launched_by' => user()->id,
            'launched_at' => now(),
        ]);

        // Read throttle from DB settings (never hardcoded).
        $crmSetting     = CrmEmailSetting::getForCompany();
        $emailsPerMinute = $crmSetting->throttle_per_minute;

        // Dispatch the master launcher job.
        LaunchCampaignJob::dispatch($campaign->id, $emailsPerMinute);

        return Reply::successWithData('Campaign launch initiated. Emails are being dispatched.', [
            'status' => 'sending',
        ]);
    }

    /**
     * Pause an actively-sending campaign by cancelling its batch.
     *
     * POST /account/crm-email-campaigns/{id}/pause
     *
     * @param int $id
     * @return array
     */
    public function pause($id)
    {
        $campaign = Campaign::where('company_id', company()->id)->findOrFail($id);

        $editPermission = user()->permission('edit_crm_email');
        abort_403(
            !in_array('admin', user_roles())
            && !($editPermission == 'all' || ($editPermission == 'added' && $campaign->added_by == user()->id))
        );

        if ($campaign->status !== 'sending') {
            return Reply::error('Only actively-sending campaigns can be paused.');
        }

        // Cancel the Bus batch if we have a batch ID reference.
        if ($campaign->batch_id) {
            $batch = Bus::findBatch($campaign->batch_id);

            if ($batch && !$batch->cancelled()) {
                $batch->cancel();
            }
        }

        $campaign->update(['status' => 'paused']);

        return Reply::successWithData('Campaign has been paused.', [
            'status' => 'paused',
        ]);
    }

    /**
     * Return campaign delivery statistics as JSON.
     *
     * GET /account/crm-email-campaigns/{id}/stats
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats($id)
    {
        $campaign = Campaign::where('company_id', company()->id)->findOrFail($id);

        $viewPermission = user()->permission('view_crm_email');
        abort_403(!in_array('admin', user_roles()) && $viewPermission == 'none');

        $emailCounts = $campaign->emails()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $total   = array_sum($emailCounts);
        $sent    = $emailCounts['sent']    ?? 0;
        $failed  = $emailCounts['failed']  ?? 0;
        $pending = $emailCounts['pending'] ?? 0;

        // Query batch progress if batch_id is present.
        $batchProgress = null;
        if ($campaign->batch_id) {
            $batch = Bus::findBatch($campaign->batch_id);
            if ($batch) {
                $batchProgress = [
                    'total_jobs'      => $batch->totalJobs,
                    'pending_jobs'    => $batch->pendingJobs,
                    'failed_jobs'     => $batch->failedJobs,
                    'progress'        => $batch->progress(),
                    'finished'        => $batch->finished(),
                    'cancelled'       => $batch->cancelled(),
                ];
            }
        }

        $this->campaignStats = [
            'campaign_id'    => $campaign->id,
            'campaign_name'  => $campaign->name,
            'status'         => $campaign->status,
            'total'          => $total,
            'sent'           => $sent,
            'failed'         => $failed,
            'pending'        => $pending,
            'sent_pct'       => $total > 0 ? round(($sent / $total) * 100, 1) : 0,
            'failed_pct'     => $total > 0 ? round(($failed / $total) * 100, 1) : 0,
            'batch_progress' => $batchProgress,
            'launched_at'    => $campaign->launched_at?->format(company()->date_format . ' H:i'),
        ];

        $this->campaign = $campaign;

        if (request()->ajax()) {
            return view('crmemail::campaigns.ajax.stats', $this->data)->render();
        }

        return response()->json($this->campaignStats);
    }
}
