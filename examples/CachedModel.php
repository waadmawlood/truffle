<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Waad\Truffle\Enums\DataType;
use Waad\Truffle\Truffle;

/**
 * Per-model caching with TTL and custom driver.
 */
class Setting extends Model
{
    use Truffle;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'key';

    protected $truffleCache = true;
    protected $truffleCacheTtl = 3600;
    // protected $truffleCacheDriver = 'redis';
    // protected $truffleCachePrefix = 'app_settings_';

    protected $schema = [
        'key' => DataType::String,
        'value' => DataType::Text,
    ];

    protected $records = [
        ['key' => 'app_name', 'value' => 'My Application'],
        ['key' => 'maintenance_mode', 'value' => 'false'],
        ['key' => 'items_per_page', 'value' => '25'],
    ];

    public static function getValue(string $key, $default = null)
    {
        return static::find($key)?->value ?? $default;
    }
}

// Usage:
// Setting::getValue('app_name');
// Setting::getValue('missing_key', 'fallback');
// Setting::clearTruffleCache();
// Setting::refreshTruffleCache();
