<?php

namespace Waad\Truffle\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Waad\Truffle\Enums\DataType;
use Waad\Truffle\Truffle;

class Country extends Model
{
    use Truffle;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'code';

    protected $records = [
        ['code' => 'UK', 'name' => 'United Kingdom', 'continent' => 'Europe'],
        ['code' => 'PS', 'name' => 'Palestine', 'continent' => 'Asia'],
        ['code' => 'BR', 'name' => 'Brazil', 'continent' => 'South America'],
    ];

    protected $schema = [
        'code' => DataType::String,
        'name' => DataType::String,
        'continent' => DataType::String,
    ];
}
