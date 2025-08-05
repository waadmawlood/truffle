<?php

namespace Waad\Truffle\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Waad\Truffle\Enums\DataType;
use Waad\Truffle\Truffle;

class SchemaOnlyModel extends Model
{
    use Truffle;

    protected $fillable = ['name', 'email'];

    // Define schema without data
    protected $schema = [
        'id' => DataType::Id,
        'name' => DataType::String,
        'email' => DataType::String,
    ];
}
