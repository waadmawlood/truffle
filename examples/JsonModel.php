<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Waad\Truffle\Enums\DataType;
use Waad\Truffle\Truffle;

/**
 * Load records from a JSON file.
 */
class Permission extends Model
{
    use Truffle;

    protected $fillable = ['name', 'guard', 'description'];

    protected $schema = [
        'id' => DataType::Id,
        'name' => DataType::String,
        'guard' => DataType::String,
        'description' => DataType::String,
    ];

    public function getRecords(): array
    {
        return $this->fromJsonFile(__DIR__ . '/../data/permissions.json');
    }
}

// Usage:
// Permission::all();
// Permission::where('guard', 'web')->get();
