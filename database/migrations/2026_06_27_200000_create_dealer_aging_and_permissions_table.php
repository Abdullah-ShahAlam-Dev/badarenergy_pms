<?php

use App\Models\Company;
use App\Models\Module;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create aging snapshots table
        Schema::create('dealer_aging_snapshots', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('company_id')->index();
            $table->unsignedInteger('dealer_id')->unique();
            $table->decimal('outstanding', 12, 2)->default(0.00);
            $table->decimal('current', 12, 2)->default(0.00);
            $table->decimal('bucket_1_15', 12, 2)->default(0.00);
            $table->decimal('bucket_16_30', 12, 2)->default(0.00);
            $table->decimal('bucket_31_60', 12, 2)->default(0.00);
            $table->decimal('bucket_61_90', 12, 2)->default(0.00);
            $table->decimal('bucket_91_plus', 12, 2)->default(0.00);
            $table->decimal('credit_utilization', 5, 2)->default(0.00);
            $table->date('last_payment_date')->nullable();
            $table->integer('days_since_payment')->nullable();
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('dealer_id')->references('id')->on('users')->onDelete('cascade');
        });

        // 2. Create aging buckets config table
        Schema::create('dealer_aging_buckets', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id')->index();
            $table->string('name', 50);
            $table->integer('min_days');
            $table->integer('max_days')->nullable();
            $table->integer('sequence');
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });

        // 3. Add aging basis setting to invoice_settings table
        Schema::table('invoice_settings', function (Blueprint $table) {
            $table->string('aging_basis', 20)->default('due_date')->after('invoice_prefix');
        });

        // 4. Seed default aging buckets for existing companies
        $companies = Company::all();
        foreach ($companies as $company) {
            $buckets = [
                ['name' => '1-15 Days', 'min_days' => 1, 'max_days' => 15, 'sequence' => 1],
                ['name' => '16-30 Days', 'min_days' => 16, 'max_days' => 30, 'sequence' => 2],
                ['name' => '31-60 Days', 'min_days' => 31, 'max_days' => 60, 'sequence' => 3],
                ['name' => '61-90 Days', 'min_days' => 61, 'max_days' => 90, 'sequence' => 4],
                ['name' => '90+ Days', 'min_days' => 91, 'max_days' => null, 'sequence' => 5],
            ];
            foreach ($buckets as $b) {
                DB::table('dealer_aging_buckets')->insert([
                    'company_id' => $company->id,
                    'name' => $b['name'],
                    'min_days' => $b['min_days'],
                    'max_days' => $b['max_days'],
                    'sequence' => $b['sequence'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 5. Seed Permissions
        $module = Module::where('module_name', 'invoices')->first();
        if ($module) {
            $permissionsData = [
                [
                    'name' => 'view_aging_report',
                    'display_name' => 'View Aging Report',
                    'allowed' => '{"all":4, "added":1, "owned":2, "both":3, "none":5}'
                ],
                [
                    'name' => 'view_salesperson_aging',
                    'display_name' => 'View Salesperson Aging',
                    'allowed' => '{"all":4, "none":5}'
                ],
                [
                    'name' => 'view_outstanding_dashboard',
                    'display_name' => 'View Outstanding Dashboard',
                    'allowed' => '{"all":4, "none":5}'
                ],
                [
                    'name' => 'export_aging_reports',
                    'display_name' => 'Export Aging Reports',
                    'allowed' => '{"all":4, "none":5}'
                ],
            ];

            $adminUsers = User::allAdmins();

            foreach ($permissionsData as $permData) {
                $permission = Permission::firstOrCreate(
                    ['name' => $permData['name']],
                    [
                        'display_name' => $permData['display_name'],
                        'is_custom' => 1,
                        'module_id' => $module->id,
                        'allowed_permissions' => $permData['allowed']
                    ]
                );

                // Assign to Admin Role for all companies
                foreach ($companies as $company) {
                    $role = Role::where('name', 'admin')
                        ->where('company_id', $company->id)
                        ->first();

                    if ($role) {
                        PermissionRole::firstOrCreate([
                            'permission_id' => $permission->id,
                            'role_id' => $role->id,
                        ], [
                            'permission_type_id' => 4 // All
                        ]);
                    }
                }

                // Assign directly to Admin Users
                foreach ($adminUsers as $adminUser) {
                    UserPermission::firstOrCreate([
                        'user_id' => $adminUser->id,
                        'permission_id' => $permission->id,
                    ], [
                        'permission_type_id' => 4 // All
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Drop Permissions
        $names = [
            'view_aging_report',
            'view_salesperson_aging',
            'view_outstanding_dashboard',
            'export_aging_reports'
        ];

        $perms = Permission::whereIn('name', $names)->get();
        foreach ($perms as $perm) {
            PermissionRole::where('permission_id', $perm->id)->delete();
            UserPermission::where('permission_id', $perm->id)->delete();
            $perm->delete();
        }

        // 2. Drop setting column
        if (Schema::hasColumn('invoice_settings', 'aging_basis')) {
            Schema::table('invoice_settings', function (Blueprint $table) {
                $table->dropColumn('aging_basis');
            });
        }

        // 3. Drop tables
        Schema::dropIfExists('dealer_aging_buckets');
        Schema::dropIfExists('dealer_aging_snapshots');
    }
};
