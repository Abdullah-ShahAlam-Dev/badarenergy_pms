<?php

namespace App\Services;

use App\Models\Product;

class GatePassConditionalApprovalService
{
    /**
     * Keywords that trigger mandatory battery approval rule.
     */
    protected array $batteryKeywords = [
        'battery', 'cell', 'cells', 'lithium', 'lfp', 'ncm', 'battery pack', 'pack', 'lead acid', 'agm'
    ];

    /**
     * Check if a list of item names or product IDs contains battery parts.
     */
    public function containsBatteryParts(array $items): bool
    {
        foreach ($items as $item) {
            $itemName = '';
            if (is_array($item)) {
                $itemName = $item['item_name'] ?? ($item['name'] ?? '');
                $productId = $item['product_id'] ?? null;
            } elseif (is_object($item)) {
                $itemName = $item->item_name ?? ($item->name ?? '');
                $productId = $item->product_id ?? null;
            } else {
                $itemName = (string) $item;
                $productId = null;
            }

            if ($productId) {
                $product = Product::find($productId);
                if ($product) {
                    $itemName .= ' ' . $product->name . ' ' . ($product->category ? $product->category->category_name : '');
                }
            }

            $lowerName = strtolower($itemName);
            foreach ($this->batteryKeywords as $keyword) {
                if (str_contains($lowerName, $keyword)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Evaluate conditional approval status for Gate Pass.
     * Battery Parts = Mandatory Manager/HOD Approval (pending_hod).
     * Non-Battery Parts = Auto-Approved / Direct Exit (approved).
     */
    public function evaluateGatePass(array $items, bool $isManual = false): array
    {
        $hasBattery = $this->containsBatteryParts($items);

        if ($hasBattery) {
            return [
                'requires_battery_approval' => true,
                'status' => 'pending_hod',
                'message' => 'Gate Pass contains Battery Parts — Manager/HOD Approval required before warehouse exit.',
            ];
        }

        return [
            'requires_battery_approval' => false,
            'status' => 'approved',
            'message' => 'Gate Pass auto-approved for direct warehouse exit (Non-battery items).',
        ];
    }
}
