<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Waad\Truffle\Enums\DataType;
use Waad\Truffle\Truffle;

class Product extends Model
{
    use Truffle;

    protected $fillable = ['name', 'price', 'category'];

    protected $casts = [
        'price' => 'float',
    ];

    protected $records = [
        ['id' => 1, 'name' => 'Laptop', 'price' => 999.99, 'category' => 'Electronics'],
        ['id' => 2, 'name' => 'Coffee Mug', 'price' => 12.50, 'category' => 'Kitchen'],
        ['id' => 3, 'name' => 'Desk Lamp', 'price' => 45.00, 'category' => 'Office'],
    ];

    protected $schema = [
        'id' => DataType::Id,
        'name' => DataType::String,
        'price' => DataType::Float,
        'category' => DataType::String,
    ];

    public function scopeInCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}

// Usage:
// Product::all();
// Product::where('price', '>', 20)->get();
// Product::inCategory('Electronics')->first();
// Product::avg('price');
