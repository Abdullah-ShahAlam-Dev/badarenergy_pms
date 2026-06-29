<?php

namespace App\Services;

use App\Models\StockIntakeVoucher;
use App\Models\StockIntakeItem;
use App\Events\StockIntakeApproved;
use App\Facades\WorkflowConfig;
use Illuminate\Support\Facades\DB;
use Exception;

class StockIntakeService
{
    /**
     * Create a stock intake voucher in draft or pending status.
     */
    public function createVoucher(array $data, array $items): StockIntakeVoucher
    {
        return DB::transaction(function () use ($data, $items) {
            $companyId = company() ? company()->id : 1;

            $generator = app(IntakeNumberGeneratorService::class);
            $voucherNumber = $generator->generate($companyId);

            $voucher = new StockIntakeVoucher();
            $voucher->company_id = $companyId;
            $voucher->voucher_number = $voucherNumber;
            $voucher->shipment_id = $data['shipment_id'] ?? null;
            $voucher->warehouse_id = $data['warehouse_id'];
            $voucher->intake_date = $data['intake_date'];
            $voucher->remarks = $data['remarks'] ?? null;
            $voucher->created_by = auth()->id() ?: (\App\Models\User::first() ? \App\Models\User::first()->id : null);

            // Set initial status based on approval configuration
            $approvalRequired = WorkflowConfig::get('approvals', 'stock_intake', false, $companyId);
            $voucher->status = $approvalRequired ? 'pending' : 'completed';
            $voucher->save();

            // Insert line items
            foreach ($items as $item) {
                StockIntakeItem::create([
                    'intake_voucher_id' => $voucher->id,
                    'product_id' => $item['product_id'],
                    'quantity_declared' => $item['quantity_declared'],
                    'quantity_received' => $item['quantity_received'],
                    'unit_cost' => $item['unit_cost'] ?? 0.00,
                    'batch_id' => $item['batch_id'] ?? null,
                ]);
            }

            // If approval is not required, immediately trigger approval events
            if ($voucher->status === 'completed') {
                event(new StockIntakeApproved($voucher));
            }

            return $voucher;
        });
    }

    /**
     * Approve and post a stock intake voucher.
     */
    public function approveVoucher(StockIntakeVoucher $voucher): void
    {
        DB::transaction(function () use ($voucher) {
            $voucher->lockForUpdate();

            if (in_array($voucher->status, ['approved', 'completed'])) {
                return; // Idempotent check
            }

            $voucher->status = 'completed';
            $voucher->save();

            // Trigger event-driven architecture inventory increment and serial generation
            event(new StockIntakeApproved($voucher));
        });
    }

    /**
     * Cancel a stock intake voucher (only allowed if not yet approved/completed).
     */
    public function cancelVoucher(StockIntakeVoucher $voucher): void
    {
        DB::transaction(function () use ($voucher) {
            $voucher->lockForUpdate();

            if (in_array($voucher->status, ['approved', 'completed'])) {
                throw new Exception("Approved or completed vouchers cannot be cancelled.");
            }

            $voucher->status = 'cancelled';
            $voucher->save();
        });
    }
}
