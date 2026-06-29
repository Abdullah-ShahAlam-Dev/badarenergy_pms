<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed get(string $group, string $key, mixed $default = null, ?int $companyId = null)
 * @method static void set(string $group, string $key, mixed $value, ?int $companyId = null)
 * @method static bool has(string $group, string $key, ?int $companyId = null)
 * @method static array all(?int $companyId = null)
 * @method static void clearCache(?int $companyId = null)
 * 
 * @see \App\Repositories\WorkflowConfigRepository
 */
class WorkflowConfig extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'workflow_config';
    }
}
