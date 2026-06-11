<?php

namespace Modules\CRMEmail\Entities;

use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * CrmEmailSetting
 *
 * Central settings model for the CRM Email module.
 * All runtime configuration (throttle, sender details, footer, etc.)
 * is read from this model — never hardcoded.
 *
 * @property int    $id
 * @property int    $company_id
 * @property string $from_name           Default sender display name for campaigns
 * @property string $from_email          Default sender email for campaigns
 * @property int    $throttle_per_minute Max emails dispatched per minute
 * @property string $track_opens         yes|no
 * @property string $track_clicks        yes|no
 * @property string $unsubscribe_footer  yes|no — append unsubscribe link in every campaign email
 * @property string|null $footer_text    Custom footer text shown below the unsubscribe link
 */
class CrmEmailSetting extends BaseModel
{
    use HasFactory, HasCompany;

    const MODULE_NAME = 'crm_email';

    protected $table = 'crm_email_settings';

    protected $guarded = ['id'];

    protected $casts = [
        'throttle_per_minute' => 'integer',
    ];

    /**
     * Retrieve the settings row for the current authenticated company.
     * Falls back to a sensible in-memory default so the app never crashes
     * even if the migration hasn't run yet.
     *
     * @return static
     */
    public static function getForCompany(): static
    {
        return static::where('company_id', company()->id)->firstOrNew([
            'company_id'         => company()->id,
            'from_name'          => company()->company_name,
            'from_email'         => config('mail.from.address', ''),
            'throttle_per_minute' => (int) config('crmemail.throttle_emails_per_minute', 60),
            'track_opens'        => 'no',
            'track_clicks'       => 'no',
            'unsubscribe_footer' => 'yes',
            'footer_text'        => null,
        ]);
    }

    /**
     * Called by the activation migration and any CompanyCreated listener.
     * Seeds a default settings row for the given company.
     *
     * @param \App\Models\Company $company
     * @return void
     */
    public static function addModuleSetting($company): void
    {
        $existing = static::withoutGlobalScope(CompanyScope::class)
            ->where('company_id', $company->id)
            ->first();

        if (!$existing) {
            static::withoutGlobalScope(CompanyScope::class)->create([
                'company_id'          => $company->id,
                'from_name'           => $company->company_name,
                'from_email'          => config('mail.from.address', ''),
                'throttle_per_minute' => (int) config('crmemail.throttle_emails_per_minute', 60),
                'track_opens'         => 'no',
                'track_clicks'        => 'no',
                'unsubscribe_footer'  => 'yes',
                'footer_text'         => null,
            ]);
        }
    }
}
