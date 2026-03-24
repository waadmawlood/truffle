<?php

namespace Waad\Truffle\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Waad\Truffle\Enums\DataType;
use Waad\Truffle\Truffle;

class CachedWithDriverPeople extends Model
{
    use Truffle;

    protected $table = 'cached_driver_people';

    protected $truffleCache = true;

    protected $truffleCacheDriver = 'array';

    protected $truffleCacheTtl = 600;

    protected $truffleCachePrefix = 'custom_prefix_';

    protected $fillable = ['name', 'email'];

    protected $schema = [
        'id' => DataType::Id,
        'name' => DataType::String,
        'email' => DataType::String,
    ];

    protected $records = [
        ['id' => 1, 'name' => 'Dave', 'email' => 'dave@example.com'],
        ['id' => 2, 'name' => 'Eve', 'email' => 'eve@example.com'],
    ];
}
