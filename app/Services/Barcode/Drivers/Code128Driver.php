<?php

namespace App\Services\Barcode\Drivers;

use App\Services\Barcode\BarcodeDriverInterface;
use App\Services\Barcode\Picqer\BarcodeGeneratorSVG;
use App\Services\Barcode\Picqer\BarcodeGenerator;

class Code128Driver implements BarcodeDriverInterface
{
    /**
     * Generate Code128 barcode representation using Picqer SVG generator.
     */
    public function generate(string $content): string
    {
        $generator = new BarcodeGeneratorSVG();
        $svg = $generator->getBarcode($content, BarcodeGenerator::TYPE_CODE_128, 2, 40, 'black');
        
        // Strip XML prolog and doctype to allow inline embedding in HTML/Blade views
        $svg = preg_replace('/<\?xml.*?\?>/s', '', $svg);
        $svg = preg_replace('/<!DOCTYPE.*?>/s', '', $svg);
        
        return trim($svg);
    }
}
