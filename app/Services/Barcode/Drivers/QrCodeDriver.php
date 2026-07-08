<?php

namespace App\Services\Barcode\Drivers;

use App\Services\Barcode\BarcodeDriverInterface;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrCodeDriver implements BarcodeDriverInterface
{
    /**
     * Generate QR Code representation using BaconQrCode generator.
     */
    public function generate(string $content): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(100, 1), // width=100, margin=1 module
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $svg = $writer->writeString($content);

        // Strip XML prolog and doctype to allow inline embedding in HTML/Blade views
        $svg = preg_replace('/<\?xml.*?\?>/s', '', $svg);
        $svg = preg_replace('/<!DOCTYPE.*?>/s', '', $svg);

        return trim($svg);
    }
}
