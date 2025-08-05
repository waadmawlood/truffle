<?php

namespace Waad\Truffle\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Waad\Truffle\Enums\DataType;
use Waad\Truffle\Truffle;

class People extends Model
{
    use Truffle;

    protected $fillable = ['name', 'email', 'is_admin', 'age', 'metadata', 'category_id'];

    protected $casts = [
        'is_admin' => 'boolean',
        'age' => 'integer',
        'metadata' => 'array',
        'category_id' => 'integer',
    ];

    // Define the data records for this model
    protected $records = [
        [
            'id' => 1,
            'name' => 'Waad Mawlood',
            'email' => 'waad@example.com',
            'is_admin' => true,
            'age' => 30,
            'metadata' => ['role' => 'developer'],
            'category_id' => 1,
        ],
        [
            'id' => 2,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'is_admin' => false,
            'age' => 25,
            'metadata' => ['role' => 'user'],
            'category_id' => 1,
        ],
        [
            'id' => 3,
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'is_admin' => false,
            'age' => 28,
            'metadata' => ['role' => 'manager'],
            'category_id' => 1,
        ],
    ];

    // Define the schema for this model (optional, will be inferred from rows if not provided)
    protected $schema = [
        'id' => DataType::Id,
        'name' => DataType::String,
        'email' => DataType::String,
        'is_admin' => DataType::Boolean,
        'age' => DataType::Integer,
        'metadata' => DataType::String, // will be cast to array by Laravel
        'category_id' => DataType::UnsignedBigInteger,
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
}
