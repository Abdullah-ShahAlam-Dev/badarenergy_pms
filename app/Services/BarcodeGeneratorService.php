<?php

namespace App\Services;

use App\Services\Barcode\BarcodeDriverFactory;
use App\Facades\WorkflowConfig;

class BarcodeGeneratorService
{
    protected BarcodeDriverFactory $factory;

    public function __construct(BarcodeDriverFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Generate a barcode representation using the configured driver.
     *
     * @param string $content
     * @param int|null $companyId
     * @return string Barcode SVG or HTML markup.
     */
    public function generate(string $content, ?int $companyId = null): string
    {
        $driverName = WorkflowConfig::get('barcode', 'type', 'code128', $companyId);
        $driver = $this->factory->make($driverName);

        return $driver->generate($content);
    }
}
