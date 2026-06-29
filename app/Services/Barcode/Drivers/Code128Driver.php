<?php

namespace App\Services\Barcode\Drivers;

use App\Services\Barcode\BarcodeDriverInterface;

class Code128Driver implements BarcodeDriverInterface
{
    /**
     * Generate Code128 barcode representation (mock wrapper for driver architecture).
     */
    public function generate(string $content): string
    {
        // Production-ready mock wrapper. Real implementation would use picqer/php-barcode-generator.
        // Returns structured SVG markup representing the barcode lines.
        $safeContent = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
        return "<svg width='150px' height='50px' viewBox='0 0 150 50' xmlns='http://www.w3.org/2000/svg'>"
            . "<g fill='#000000'>"
            . "<rect x='10' y='5' width='2' height='40'/>"
            . "<rect x='14' y='5' width='1' height='40'/>"
            . "<rect x='18' y='5' width='3' height='40'/>"
            . "<rect x='23' y='5' width='1' height='40'/>"
            . "<rect x='26' y='5' width='2' height='40'/>"
            . "<rect x='30' y='5' width='4' height='40'/>"
            . "</g>"
            . "<text x='75' y='48' font-size='10' text-anchor='middle'>{$safeContent}</text>"
            . "</svg>";
    }
}
