<?php

namespace App\Services\Barcode;

use App\Services\Barcode\Drivers\Code128Driver;
use App\Services\Barcode\Drivers\QrCodeDriver;
use InvalidArgumentException;

class BarcodeDriverFactory
{
    /**
     * Create the concrete driver instance.
     *
     * @param string $driverName
     * @return BarcodeDriverInterface
     */
    public function make(string $driverName): BarcodeDriverInterface
    {
        return match (strtolower($driverName)) {
            'code128' => new Code128Driver(),
            'qrcode', 'qr' => new QrCodeDriver(),
            default => throw new InvalidArgumentException("Unsupported barcode driver type: [{$driverName}]."),
        };
    }
}
