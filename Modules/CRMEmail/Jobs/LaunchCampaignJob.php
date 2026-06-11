<?php

namespace Modules\CRMEmail\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Bus;
use Illuminate\Support\Facades\Log;
use Modules\CRMEmail\Entities\Campaign;
use Modules\CRMEmail\Entities\CampaignEmail;
use Modules\CRMEmail\Entities\Unsubscribe;
use Modules\CRMEmail\Services\SegmentResolverService;

class LaunchCampaignJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maximum attempts for the master launcher job itself.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * Maximum execution time in seconds.
     * The master job only builds the recipient list and dispatches child jobs.
     * It should complete well under the 90-second retry_after limit.
     *
     * @var int
     */
    public $timeout = 60;

    /**
     * The campaign ID to launch.
     *
     * @var int
     */
    protected int $campaignId;

    /**
     * Maximum emails dispatched per minute (throttle rate).
     * Stagger delay offsets are calculated from this value.
     *
     * @var int
     */
    protected int $emailsPerMinute;

    /**
     * Create a new LaunchCampaignJob instance.
     *
     * @param int $campaignId     The campaign to launch
     * @param int $emailsPerMinute Throttle rate (default 60 emails per minute)
     */
    public function __construct(int $campaignId, int $emailsPerMinute = 60)
    {
        $this->campaignId     = $campaignId;
        $this->emailsPerMinute = max(1, $emailsPerMinute);
    }

    /**
     * Execute the master launcher job.
     *
     * Responsibilities:
     *   1. Validate the campaign is still in a launchable state.
     *   2. Resolve the segment recipients using SegmentResolverService.
     *   3. Deduplicate emails via LOWER(TRIM(email)).
     *   4. Exclude unsubscribed and already-sent addresses.
     *   5. Insert crm_campaign_emails log rows for each unique recipient.
     *   6. Dispatch a Bus::batch() of SendCampaignEmailJob instances with staggered delays.
     *   7. Update campaign status to 'sending' with batch_id reference.
     *
     * @param SegmentResolverService $resolver
     * @return void
     */
    public function handle(SegmentResolverService $resolver): void
    {
        /** @var Campaign|null $campaign */
        $campaign = Campaign::with(['segment', 'template'])->find($this->campaignId);

        if (!$campaign) {
            Log::warning("CRMEmail: LaunchCampaignJob — campaign #{$this->campaignId} not found.");
            return;
        }

        // Guard: only launch if the campaign is still in a queued/scheduled state.
        if (!in_array($campaign->status, ['draft', 'scheduled'])) {
            Log::info("CRMEmail: Campaign #{$this->campaignId} is in status '{$campaign->status}'. Aborting launch.");
            return;
        }

        $companyId = $campaign->company_id;

        // Mark campaign as sending before dispatching jobs.
        $campaign->update(['status' => 'sending', 'launched_at' => now()]);

        // Resolve the full recipient list from the segment.
        if (!$campaign->segment) {
            Log::error("CRMEmail: Campaign #{$this->campaignId} has no segment attached. Aborting.");
            $campaign->update(['status' => 'draft']);
            return;
        }

        $recipientQuery = $resolver->resolve($campaign->segment);
        $recipients     = $recipientQuery->get();

        if ($recipients->isEmpty()) {
            Log::info("CRMEmail: Campaign #{$this->campaignId} resolved 0 recipients. Marking completed.");
            $campaign->update(['status' => 'completed']);
            return;
        }

        // Build a deduplicated set of emails already sent in this campaign (idempotency).
        $alreadySentEmails = CampaignEmail::where('campaign_id', $campaign->id)
            ->where('status', 'sent')
            ->pluck('email')
            ->map(fn($e) => strtolower(trim($e)))
            ->toArray();

        // Build a set of unsubscribed emails for this company.
        $unsubscribedEmails = Unsubscribe::where('company_id', $companyId)
            ->pluck('email')
            ->map(fn($e) => strtolower(trim($e)))
            ->toArray();

        // De-duplicate recipients: only process each unique normalized email once.
        $seen     = [];
        $jobs     = [];
        $index    = 0;

        foreach ($recipients as $recipient) {
            $normalizedEmail = strtolower(trim($recipient->email ?? ''));

            // Skip blank, already-sent, or unsubscribed addresses.
            if (
                empty($normalizedEmail)
                || in_array($normalizedEmail, $seen)
                || in_array($normalizedEmail, $alreadySentEmails)
                || in_array($normalizedEmail, $unsubscribedEmails)
            ) {
                continue;
            }

            $seen[] = $normalizedEmail;

            // Create the tracking log row in 'pending' state.
            $log = CampaignEmail::create([
                'company_id'     => $companyId,
                'campaign_id'    => $campaign->id,
                'recipient_type' => $recipient->recipient_type,
                'recipient_id'   => $recipient->recipient_id,
                'email'          => $normalizedEmail,
                'status'         => 'pending',
                'attempts'       => 0,
            ]);

            // Calculate staggered delay to throttle SMTP load.
            $delaySeconds = (int) floor($index / $this->emailsPerMinute) * 60;

            $job = (new SendCampaignEmailJob($log->id))
                ->delay(now()->addSeconds($delaySeconds));

            $jobs[] = $job;
            $index++;
        }

        if (empty($jobs)) {
            Log::info("CRMEmail: Campaign #{$this->campaignId} had no new eligible recipients after dedup/exclusion.");
            $campaign->update(['status' => 'completed']);
            return;
        }

        Log::info("CRMEmail: Dispatching " . count($jobs) . " jobs for Campaign #{$this->campaignId}.");

        // Dispatch as a batch — enables progress tracking and cancellation.
        $batch = Bus::batch($jobs)
            ->name("crm_campaign_{$campaign->id}")
            ->allowFailures()
            ->then(function (\Illuminate\Bus\Batch $batch) use ($campaign) {
                // Batch completed without cancellation.
                if ($batch->failedJobs === 0) {
                    Campaign::where('id', $campaign->id)->update(['status' => 'completed']);
                } else {
                    // Completed with some failures — still mark as completed (partial send).
                    Campaign::where('id', $campaign->id)->update(['status' => 'completed']);
                    Log::warning("CRMEmail: Campaign #{$campaign->id} completed with {$batch->failedJobs} failures.");
                }
            })
            ->catch(function (\Illuminate\Bus\Batch $batch, \Throwable $e) use ($campaign) {
                Log::error("CRMEmail: Campaign #{$campaign->id} batch encountered an error: " . $e->getMessage());
            })
            ->finally(function (\Illuminate\Bus\Batch $batch) use ($campaign) {
                // Store the batch ID on the campaign for progress tracking.
                Campaign::where('id', $campaign->id)->update(['batch_id' => $batch->id]);
            })
            ->dispatch();

        // Store the batch ID immediately after dispatch for UI tracking.
        $campaign->update(['batch_id' => $batch->id]);

        Log::info("CRMEmail: Campaign #{$this->campaignId} batch dispatched with ID: {$batch->id}");
    }

    /**
     * Handle a failed LaunchCampaignJob.
     *
     * @param \Throwable $e
     * @return void
     */
    public function failed(\Throwable $e): void
    {
        Campaign::where('id', $this->campaignId)->update(['status' => 'draft']);
        Log::error("CRMEmail: LaunchCampaignJob failed for campaign #{$this->campaignId}: " . $e->getMessage());
    }
}
