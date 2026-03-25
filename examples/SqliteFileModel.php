<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Waad\Truffle\Enums\DataType;
use Waad\Truffle\Truffle;

/**
 * Persist data to a SQLite file instead of in-memory.
 * Ideal for large datasets that don't need rebuilding every request.
 */
class Region extends Model
{
    use Truffle;

    protected static $truffleSqliteFile;

    protected $schema = [
        'id' => DataType::Id,
        'name' => DataType::String,
        'timezone' => DataType::String,
    ];

    protected $records = [
        ['id' => 1, 'name' => 'US East', 'timezone' => 'America/New_York'],
        ['id' => 2, 'name' => 'US West', 'timezone' => 'America/Los_Angeles'],
        ['id' => 3, 'name' => 'Europe', 'timezone' => 'Europe/London'],
    ];

    public function __construct(array $attributes = [])
    {
        static::$truffleSqliteFile ??= storage_path('truffle/regions.sqlite');
        parent::__construct($attributes);
    }
}

// Usage:
// Region::all();
// Region::deleteTruffleSqliteFile();   // delete the file
// Region::refreshTruffleSqliteFile();  // delete + rebuild
