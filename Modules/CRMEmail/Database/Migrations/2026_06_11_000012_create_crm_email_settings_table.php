<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Company;
use App\Scopes\CompanyScope;
use Modules\CRMEmail\Entities\CrmEmailSetting;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the crm_email_settings table and seeds one row per existing company.
     */
    public function up(): void
    {
        Schema::create('crm_email_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id')->unique()->index();
            $table->string('from_name')->nullable();
            $table->string('from_email')->nullable();
            $table->unsignedSmallInteger('throttle_per_minute')->default(60)
                ->comment('Maximum emails dispatched per minute per campaign launch');
            $table->enum('track_opens', ['yes', 'no'])->default('no');
            $table->enum('track_clicks', ['yes', 'no'])->default('no');
            $table->enum('unsubscribe_footer', ['yes', 'no'])->default('yes')
                ->comment('Append unsubscribe link footer to every campaign email');
            $table->text('footer_text')->nullable()
                ->comment('Custom HTML/text appended below the unsubscribe link');
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });

        // Seed a default row for every existing company.
        $companies = Company::withoutGlobalScope(CompanyScope::class)->get();

        foreach ($companies as $company) {
            CrmEmailSetting::addModuleSetting($company);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_email_settings');
    }
};
