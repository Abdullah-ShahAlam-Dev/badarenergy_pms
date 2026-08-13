<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItems;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderLine;
use App\Models\CareOfLedger;
use App\Models\User;
use Modules\GatePass\Entities\GatePassRequest;
use Modules\GatePass\Entities\GatePassItem;
use Illuminate\Support\Facades\DB;
use Exception;

class InternalProductIssueService
{
    /**
     * Process an Internal Product Issue end-to-end:
     * Order (customer_type='care_of') -> Delivery Order (DO) -> Stock Minus -> Gate Pass Request -> Care Of Ledger Entry.
     *
     * @param array $data Basic details (care_of_id, warehouse_id, purpose, vehicle_number, driver_name, notes)
     * @param array $items Array of [product_id, quantity, unit_price, batch_id]
     * @return array
     * @throws Exception
     */
    public function processProductIssue(array $data, array $items): array
    {
        return DB::transaction(function () use ($data, $items) {
            $companyId = company() ? company()->id : 1;
            $careOfId = $data['care_of_id'];
            $warehouseId = $data['warehouse_id'];

            $careOfUser = User::findOrFail($careOfId);

            // 1. Calculate Totals
            $subTotal = 0;
            foreach ($items as $item) {
                $qty = (float) $item['quantity'];
                $price = (float) ($item['unit_price'] ?? 0.00);
                $subTotal += ($qty * $price);
            }
            $totalAmount = round($subTotal, 2);

            // 2. Create Order (customer_type = 'care_of')
            $lastOrderNumber = Order::lastOrderNumber() + 1;
            $orderSetting = invoice_setting();
            $customOrderNumber = 'ORD-INTERNAL-' . $lastOrderNumber;
            if ($orderSetting && isset($orderSetting->order_prefix)) {
                $customOrderNumber = $orderSetting->order_prefix . '-INT-' . sprintf('%04d', $lastOrderNumber);
            }

            $order = new Order();
            $order->company_id = $companyId;
            $order->customer_type = 'care_of';
            $order->care_of_id = $careOfId;
            $order->warehouse_id = $warehouseId;
            $order->order_date = now()->format('Y-m-d');
            $order->sub_total = $totalAmount;
            $order->total = $totalAmount;
            $order->discount = 0;
            $order->discount_type = '%';
            $order->status = 'approved';
            $order->currency_id = company()->currency_id;
            $order->note = $data['purpose'] ?? 'Internal Product Issue';
            $order->order_number = $lastOrderNumber;
            $order->sale_type = 0; // Internal issue
            $order->added_by = auth()->id();
            $order->save();

            $itemSummaryList = [];

            // Insert Order Lines
            foreach ($items as $itemData) {
                $pId = $itemData['product_id'];
                $qty = (float) $itemData['quantity'];
                $price = (float) ($itemData['unit_price'] ?? 0.00);
                $amt = round($qty * $price, 2);
                $product = \App\Models\Product::find($pId);
                $pName = $product ? $product->name : "Product #{$pId}";

                OrderItems::create([
                    'order_id' => $order->id,
                    'product_id' => $pId,
                    'batch_id' => $itemData['batch_id'] ?? null,
                    'item_name' => $pName,
                    'type' => $pName,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'amount' => $amt,
                ]);

                $itemSummaryList[] = "{$qty}x {$pName}";
            }

            // 3. Create Delivery Order (DO)
            $do = new DeliveryOrder();
            $do->company_id = $companyId;
            $do->source_type = 'order';
            $do->source_id = $order->id;
            $do->issue_date = now();
            $do->status = 'pending';
            $do->vehicle_number = $data['vehicle_number'] ?? null;
            $do->driver_name = $data['driver_name'] ?? null;
            $do->save();

            // Create DO lines
            foreach ($items as $itemData) {
                DeliveryOrderLine::create([
                    'delivery_order_id' => $do->id,
                    'product_id' => $itemData['product_id'],
                    'batch_id' => $itemData['batch_id'] ?? null,
                    'quantity_requested' => $itemData['quantity'],
                    'quantity_dispatched' => $itemData['quantity'],
                    'quantity_delivered' => $itemData['quantity'],
                ]);
            }

            // 4. Dispatch DO & Deduct Inventory
            $doService = resolve(DeliveryOrderService::class);
            $doService->dispatch($do, auth()->id());

            // 5. Create Automated Gate Pass Request
            $gatePassNumber = 'GP-INT-' . date('Ymd') . '-' . sprintf('%03d', rand(100, 999));
            $gatePass = new GatePassRequest();
            $gatePass->company_id = $companyId;
            $gatePass->user_id = $careOfId;
            $gatePass->department_id = $careOfUser->employeeDetail ? $careOfUser->employeeDetail->department_id : null;
            $gatePass->request_number = $gatePassNumber;
            $gatePass->request_date = now();
            $gatePass->type = 'out';
            $gatePass->return_type = 'non-returnable';
            $gatePass->purpose = $data['purpose'] ?? 'Internal Product Issue';
            $gatePass->from_location = \App\Models\Warehouse::find($warehouseId)->name ?? 'Warehouse';
            $gatePass->to_location = $careOfUser->name;
            $gatePass->vehicle_number = $data['vehicle_number'] ?? null;
            $gatePass->driver_name = $data['driver_name'] ?? null;
            $gatePass->status = 'approved'; // Auto-approved for internal product issue chain
            $gatePass->remarks = "Automated Gate Pass generated for Internal Issue DO #{$do->delivery_order_number}";
            $gatePass->save();

            foreach ($items as $itemData) {
                $prod = \App\Models\Product::find($itemData['product_id']);
                GatePassItem::create([
                    'gate_pass_request_id' => $gatePass->id,
                    'item_name' => $prod ? $prod->name : 'Product',
                    'quantity' => $itemData['quantity'],
                    'unit' => 'Pcs',
                    'remarks' => 'Internal product issue',
                ]);
            }

            // 6. Post Entry to Care Of Ledger
            $lastLedger = CareOfLedger::where('company_id', $companyId)
                ->where('care_of_id', $careOfId)
                ->orderBy('id', 'desc')
                ->first();

            $prevBalance = $lastLedger ? (float) $lastLedger->balance : 0.00;
            $newBalance = round($prevBalance + $totalAmount, 2);

            $ledger = CareOfLedger::create([
                'company_id' => $companyId,
                'care_of_id' => $careOfId,
                'order_id' => $order->id,
                'delivery_order_id' => $do->id,
                'gate_pass_request_id' => $gatePass->id,
                'date' => now(),
                'transaction_type' => 'product_issue',
                'reference_number' => $order->order_number ?: $customOrderNumber,
                'description' => "Internal Product Issue (" . implode(', ', $itemSummaryList) . ")",
                'item_details' => json_encode($itemSummaryList),
                'debit' => $totalAmount,
                'credit' => 0.00,
                'balance' => $newBalance,
                'remarks' => $data['purpose'] ?? null,
                'created_by' => auth()->id(),
            ]);

            return [
                'order' => $order,
                'delivery_order' => $do,
                'gate_pass' => $gatePass,
                'ledger' => $ledger,
            ];
        });
    }
}
