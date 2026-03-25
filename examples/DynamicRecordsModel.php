<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Waad\Truffle\Enums\DataType;
use Waad\Truffle\Truffle;

/**
 * Generate records dynamically via getRecords().
 */
class Sequence extends Model
{
    use Truffle;

    protected $schema = [
        'id' => DataType::Id,
        'label' => DataType::String,
        'value' => DataType::Integer,
    ];

    public function getRecords(): array
    {
        return collect(range(1, 50))->map(fn ($i) => [
            'id' => $i,
            'label' => "Item {$i}",
            'value' => $i * 10,
        ])->toArray();
    }
}

// Usage:
// Sequence::count();           // 50
// Sequence::where('value', '>', 200)->get();
// Sequence::sum('value');
