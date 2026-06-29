<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dealer_ledgers', function (Blueprint $table) {
            $table->unsignedInteger('credit_note_id')->nullable()->index()->after('payment_id');
            $table->unsignedBigInteger('voucher_id')->nullable()->index()->after('credit_note_id');
            $table->string('transaction_type', 30)->nullable()->index()->after('date');
            $table->string('reference_number')->nullable()->index()->after('transaction_type');
            $table->text('remarks')->nullable()->after('balance');
            $table->unsignedInteger('created_by')->nullable()->index()->after('remarks');
            $table->unsignedInteger('branch_id')->nullable()->index()->after('company_id');
            $table->unsignedInteger('financial_year_id')->nullable()->index()->after('branch_id');
            $table->decimal('exchange_rate', 12, 6)->default(1.000000)->after('reference_number');
            $table->decimal('debit_base', 12, 2)->default(0.00)->after('credit');
            $table->decimal('credit_base', 12, 2)->default(0.00)->after('debit_base');
            $table->boolean('is_reversed')->default(false)->after('balance');
            $table->unsignedBigInteger('reversal_entry_id')->nullable()->index()->after('is_reversed');

            $table->foreign('credit_note_id')->references('id')->on('credit_notes')->onDelete('set null');
            $table->foreign('voucher_id')->references('id')->on('dealer_ledger_vouchers')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('reversal_entry_id')->references('id')->on('dealer_ledgers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dealer_ledgers', function (Blueprint $table) {
            $table->dropForeign(['credit_note_id']);
            $table->dropForeign(['voucher_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['reversal_entry_id']);

            $table->dropColumn([
                'credit_note_id',
                'voucher_id',
                'transaction_type',
                'reference_number',
                'remarks',
                'created_by',
                'branch_id',
                'financial_year_id',
                'exchange_rate',
                'debit_base',
                'credit_base',
                'is_reversed',
                'reversal_entry_id'
            ]);
        });
    }
};
