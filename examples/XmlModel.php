<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Waad\Truffle\Enums\DataType;
use Waad\Truffle\Truffle;

/**
 * Load records from an XML file.
 */
class Currency extends Model
{
    use Truffle;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'code';

    protected $schema = [
        'code' => DataType::String,
        'name' => DataType::String,
        'symbol' => DataType::String,
    ];

    public function getRecords(): array
    {
        return $this->fromXmlFile(__DIR__ . '/../data/currencies.xml', 'currency');
    }
}

// Usage:
// Currency::find('USD');
// Currency::pluck('name', 'code');
