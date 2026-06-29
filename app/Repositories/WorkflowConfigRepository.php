<?php

namespace App\Repositories;

use App\Models\ErpWorkflowSetting;
use Illuminate\Support\Facades\Cache;

class WorkflowConfigRepository
{
    /**
     * Cache duration in seconds (24 hours).
     */
    protected const CACHE_TTL = 86400;

    /**
     * Get a setting value.
     */
    public function get(string $group, string $key, mixed $default = null, ?int $companyId = null): mixed
    {
        $companyId = $companyId ?? $this->getCurrentCompanyId();
        if (!$companyId) {
            return $default;
        }

        $settings = $this->getAllForCompany($companyId);
        $cacheKey = "{$group}.{$key}";

        if (array_key_exists($cacheKey, $settings)) {
            return $this->castValue($settings[$cacheKey]['value'], $settings[$cacheKey]['datatype']);
        }

        return $default;
    }

    /**
     * Check if a setting exists.
     */
    public function has(string $group, string $key, ?int $companyId = null): bool
    {
        $companyId = $companyId ?? $this->getCurrentCompanyId();
        if (!$companyId) {
            return false;
        }

        $settings = $this->getAllForCompany($companyId);
        $cacheKey = "{$group}.{$key}";

        return array_key_exists($cacheKey, $settings);
    }

    /**
     * Set/Update a setting value.
     */
    public function set(string $group, string $key, mixed $value, ?int $companyId = null): void
    {
        $companyId = $companyId ?? $this->getCurrentCompanyId();
        if (!$companyId) {
            return;
        }

        $setting = ErpWorkflowSetting::where('company_id', $companyId)
            ->where('setting_group', $group)
            ->where('setting_key', $key)
            ->first();

        $stringValue = $this->serializeValue($value);

        if ($setting) {
            $setting->update(['setting_value' => $stringValue]);
        } else {
            ErpWorkflowSetting::create([
                'company_id' => $companyId,
                'setting_group' => $group,
                'setting_key' => $key,
                'setting_value' => $stringValue,
                'datatype' => $this->inferDatatype($value),
                'is_public' => true,
            ]);
        }

        $this->clearCache($companyId);
    }

    /**
     * Get all cached settings for a company.
     */
    public function all(?int $companyId = null): array
    {
        $companyId = $companyId ?? $this->getCurrentCompanyId();
        if (!$companyId) {
            return [];
        }

        $settings = $this->getAllForCompany($companyId);
        $result = [];

        foreach ($settings as $cacheKey => $data) {
            [$group, $key] = explode('.', $cacheKey, 2);
            $result[$group][$key] = $this->castValue($data['value'], $data['datatype']);
        }

        return $result;
    }

    /**
     * Clear settings cache for a company.
     */
    public function clearCache(?int $companyId = null): void
    {
        $companyId = $companyId ?? $this->getCurrentCompanyId();
        if ($companyId) {
            Cache::forget("company_{$companyId}_erp_workflow_settings");
        }
    }

    /**
     * Get and cache all settings for a company.
     */
    protected function getAllForCompany(int $companyId): array
    {
        return Cache::remember("company_{$companyId}_erp_workflow_settings", self::CACHE_TTL, function () use ($companyId) {
            $dbSettings = ErpWorkflowSetting::where('company_id', $companyId)->get();
            $cachedArray = [];

            foreach ($dbSettings as $setting) {
                $cachedArray["{$setting->setting_group}.{$setting->setting_key}"] = [
                    'value' => $setting->setting_value,
                    'datatype' => $setting->datatype,
                ];
            }

            return $cachedArray;
        });
    }

    /**
     * Cast the database string value to its proper PHP type.
     */
    protected function castValue(string $value, string $datatype): mixed
    {
        return match ($datatype) {
            'boolean', 'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer', 'int' => (int) $value,
            'float', 'double' => (float) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Serialize any value to string for database storage.
     */
    protected function serializeValue(mixed $value): string
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        return (string) $value;
    }

    /**
     * Infer datatype key based on value type.
     */
    protected function inferDatatype(mixed $value): string
    {
        if (is_bool($value)) {
            return 'boolean';
        }
        if (is_int($value)) {
            return 'integer';
        }
        if (is_float($value)) {
            return 'float';
        }
        if (is_array($value) || is_object($value)) {
            return 'json';
        }
        return 'string';
    }

    /**
     * Get the active company ID from session/context.
     */
    protected function getCurrentCompanyId(): ?int
    {
        if (app()->bound('company')) {
            $company = app('company');
            if ($company) {
                return $company->id;
            }
        }

        // Fallback for user company ID context
        $user = auth()->user();
        if ($user) {
            return $user->company_id;
        }

        return null;
    }
}
