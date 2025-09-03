<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'description',
        'type',
    ];

    /**
     * Get a setting value by key.
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        return static::castValue($setting->value, $setting->type);
    }

    /**
     * Set a setting value by key.
     */
    public static function setValue(string $key, $value, string $description = null, string $type = 'string'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'description' => $description,
                'type' => $type,
            ]
        );
    }

    /**
     * Cast value to appropriate type.
     */
    private static function castValue($value, string $type)
    {
        return match ($type) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'json' => json_decode($value, true),
            'text', 'string' => $value,
            default => $value,
        };
    }

    /**
     * Get OpenAI API key.
     */
    public static function getOpenAiApiKey(): ?string
    {
        return static::getValue('openai_api_key');
    }

    /**
     * Get maximum file upload size in MB.
     */
    public static function getMaxFileSize(): int
    {
        return static::getValue('max_file_size_mb', 10);
    }

    /**
     * Get allowed file types.
     */
    public static function getAllowedFileTypes(): array
    {
        return static::getValue('allowed_file_types', ['pdf']);
    }

    /**
     * Get minimum suitability score threshold.
     */
    public static function getMinSuitabilityScore(): int
    {
        return static::getValue('min_suitability_score', 50);
    }

    /**
     * Check if AI processing is enabled.
     */
    public static function isAiProcessingEnabled(): bool
    {
        return static::getValue('ai_processing_enabled', true);
    }
}
