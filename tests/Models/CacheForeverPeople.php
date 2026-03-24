<?php

namespace Waad\Truffle\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Waad\Truffle\Enums\DataType;
use Waad\Truffle\Truffle;

class CacheForeverPeople extends Model
{
    use Truffle;

    protected $table = 'cache_forever_people';

    protected $truffleCache = true;

    protected $fillable = ['name', 'email'];

    protected $schema = [
        'id' => DataType::Id,
        'name' => DataType::String,
        'email' => DataType::String,
    ];

    protected $records = [
        ['id' => 1, 'name' => 'Frank', 'email' => 'frank@example.com'],
        ['id' => 2, 'name' => 'Grace', 'email' => 'grace@example.com'],
    ];
}
