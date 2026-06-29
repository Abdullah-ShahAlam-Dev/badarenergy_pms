<?php

namespace App\Services;

use App\Models\StockTransfer;
use App\Models\TransferItem;
use App\Models\TransferSerial;
use App\Models\ProductSerial;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StockTransferService
{
    protected $validationService;

    public function __construct()
    {
        $this->validationService = new TransferValidationService();
    }

    /**
     * Create a new draft transfer request.
     */
    public function createDraft(array $data): StockTransfer
    {
        $sourceId = (int)$data['source_warehouse_id'];
        $destId = (int)$data['destination_warehouse_id'];

        $this->validationService->validateWarehouseAvailability($sourceId, $destId);

        return DB::transaction(function () use ($data, $sourceId, $destId) {
            $companyId = company() ? company()->id : 1;
            
            // Sequential transfer number generation
            $count = StockTransfer::where('company_id', $companyId)->count() + 1;
            $transferNo = 'TRF-' . date('Ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            $transfer = StockTransfer::create([
                'company_id' => $companyId,
                'transfer_number' => $transferNo,
                'source_warehouse_id' => $sourceId,
                'destination_warehouse_id' => $destId,
                'status' => StockTransfer::STATUS_DRAFT,
                'vehicle_number' => $data['vehicle_number'] ?? null,
                'driver_name' => $data['driver_name'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'created_by' => user()->id,
                'created_ip' => request()->ip()
            ]);

            if (!empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    $productId = (int)$item['product_id'];
                    $qty = (float)$item['quantity'];
                    $serials = $item['serials'] ?? [];

                    $this->validationService->validateProductStock($productId, $sourceId, $qty);
                    $this->validationService->validateSerials($productId, $sourceId, $serials);

                    $transferItem = TransferItem::create([
                        'transfer_id' => $transfer->id,
                        'product_id' => $productId,
                        'quantity' => $qty,
                        'quantity_received' => 0.00
                    ]);

                    // Reserve serial numbers to lock them
                    foreach ($serials as $sn) {
                        $serialModel = ProductSerial::where('serial_number', trim($sn))
                            ->where('product_id', $productId)
                            ->first();

                        if ($serialModel) {
                            $serialModel->update(['status' => 'reserved']);

                            TransferSerial::create([
                                'transfer_item_id' => $transferItem->id,
                                'product_serial_id' => $serialModel->id,
                                'status' => 'reserved'
                            ]);
                        }
                    }
                }
            }

            return $transfer;
        });
    }

    /**
     * Update an existing draft transfer request.
     */
    public function updateDraft(int $id, array $data): StockTransfer
    {
        return DB::transaction(function () use ($id, $data) {
            $transfer = StockTransfer::findOrFail($id);

            if ($transfer->status !== StockTransfer::STATUS_DRAFT) {
                throw new \Exception("Only draft transfers can be modified.");
            }

            $sourceId = (int)$data['source_warehouse_id'];
            $destId = (int)$data['destination_warehouse_id'];

            $this->validationService->validateWarehouseAvailability($sourceId, $destId);

            // Revert previously reserved serials back to available
            foreach ($transfer->items as $item) {
                foreach ($item->serials as $ts) {
                    $ts->serial->update(['status' => ProductSerial::STATUS_AVAILABLE]);
                }
                $item->delete(); // Cascading delete will remove transfer_serials rows
            }

            $transfer->update([
                'source_warehouse_id' => $sourceId,
                'destination_warehouse_id' => $destId,
                'vehicle_number' => $data['vehicle_number'] ?? null,
                'driver_name' => $data['driver_name'] ?? null,
                'remarks' => $data['remarks'] ?? null,
            ]);

            if (!empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    $productId = (int)$item['product_id'];
                    $qty = (float)$item['quantity'];
                    $serials = $item['serials'] ?? [];

                    $this->validationService->validateProductStock($productId, $sourceId, $qty);
                    $this->validationService->validateSerials($productId, $sourceId, $serials);

                    $transferItem = TransferItem::create([
                        'transfer_id' => $transfer->id,
                        'product_id' => $productId,
                        'quantity' => $qty,
                        'quantity_received' => 0.00
                    ]);

                    // Reserve serial numbers to lock them
                    foreach ($serials as $sn) {
                        $serialModel = ProductSerial::where('serial_number', trim($sn))
                            ->where('product_id', $productId)
                            ->first();

                        if ($serialModel) {
                            $serialModel->update(['status' => 'reserved']);

                            TransferSerial::create([
                                'transfer_item_id' => $transferItem->id,
                                'product_serial_id' => $serialModel->id,
                                'status' => 'reserved'
                            ]);
                        }
                    }
                }
            }

            return $transfer;
        });
    }
}
