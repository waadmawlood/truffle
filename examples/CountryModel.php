<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Waad\Truffle\Enums\DataType;
use Waad\Truffle\Truffle;

/**
 * Non-incrementing string primary key example.
 */
class Country extends Model
{
    use Truffle;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'code';

    protected $schema = [
        'code' => DataType::String,
        'name' => DataType::String,
        'continent' => DataType::String,
    ];

    protected $records = [
        ['code' => 'US', 'name' => 'United States', 'continent' => 'North America'],
        ['code' => 'CA', 'name' => 'Canada', 'continent' => 'North America'],
        ['code' => 'UK', 'name' => 'United Kingdom', 'continent' => 'Europe'],
        ['code' => 'JP', 'name' => 'Japan', 'continent' => 'Asia'],
        ['code' => 'BR', 'name' => 'Brazil', 'continent' => 'South America'],
    ];
}

// Usage:
// Country::find('US');
// Country::where('continent', 'Europe')->get();
// Country::pluck('name', 'code');
