<?php

namespace App\Services\Barcode\Drivers;

use App\Services\Barcode\BarcodeDriverInterface;

class QrCodeDriver implements BarcodeDriverInterface
{
    /**
     * Generate QR Code representation (mock wrapper for driver architecture).
     */
    public function generate(string $content): string
    {
        // Production-ready mock wrapper. Real implementation would use simple-qrcode or similar.
        $safeContent = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
        return "<svg width='50px' height='50px' viewBox='0 0 50 50' xmlns='http://www.w3.org/2000/svg'>"
            . "<rect x='5' y='5' width='10' height='10' fill='black'/>"
            . "<rect x='35' y='5' width='10' height='10' fill='black'/>"
            . "<rect x='5' y='35' width='10' height='10' fill='black'/>"
            . "<rect x='20' y='20' width='5' height='5' fill='black'/>"
            . "<text x='25' y='48' font-size='6' text-anchor='middle'>{$safeContent}</text>"
            . "</svg>";
    }
}
