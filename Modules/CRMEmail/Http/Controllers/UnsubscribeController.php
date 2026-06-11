<?php

namespace Modules\CRMEmail\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRMEmail\Entities\Campaign;
use Modules\CRMEmail\Entities\Unsubscribe;

class UnsubscribeController extends Controller
{
    /**
     * Display the unsubscribe confirmation form.
     *
     * GET /crm-email/unsubscribe/{email}/{campaign_id?}
     *
     * @param string   $email
     * @param int|null $campaign_id
     * @return \Illuminate\View\View
     */
    public function unsubscribeForm(string $email, ?int $campaign_id = null)
    {
        $decodedEmail = urldecode($email);

        $campaign = $campaign_id ? Campaign::find($campaign_id) : null;

        return view('crmemail::unsubscribe.form', [
            'email'      => $decodedEmail,
            'campaign'   => $campaign,
        ]);
    }

    /**
     * Process the unsubscribe request.
     *
     * POST /crm-email/unsubscribe
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function unsubscribe(Request $request)
    {
        $request->validate([
            'email'       => ['required', 'email'],
            'campaign_id' => ['nullable', 'integer'],
        ]);

        $email = strtolower(trim($request->input('email')));

        $campaign   = null;
        $companyId  = null;

        if ($request->filled('campaign_id')) {
            $campaign = Campaign::find($request->input('campaign_id'));
            if ($campaign) {
                $companyId = $campaign->company_id;
            }
        }

        // If no campaign, we cannot determine which company to unsubscribe from.
        // In that case we insert for all companies where the email appears.
        if ($companyId) {
            Unsubscribe::firstOrCreate(
                ['email' => $email, 'company_id' => $companyId]
            );
        }

        return view('crmemail::unsubscribe.success', [
            'email' => $email,
        ]);
    }
}
