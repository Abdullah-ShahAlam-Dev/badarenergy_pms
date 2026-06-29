<?php

namespace App\Services\Barcode;

interface BarcodeDriverInterface
{
    /**
     * Generate barcode representation for the given content.
     *
     * @param string $content
     * @return string SVG, HTML, or base64 data.
     */
    public function generate(string $content): string;
}
