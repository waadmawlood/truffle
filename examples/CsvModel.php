<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Waad\Truffle\Enums\DataType;
use Waad\Truffle\Truffle;

/**
 * Load records from a CSV file.
 */
class Employee extends Model
{
    use Truffle;

    protected $fillable = ['name', 'email', 'department'];

    protected $schema = [
        'id' => DataType::Id,
        'name' => DataType::String,
        'email' => DataType::String,
        'department' => DataType::String,
    ];

    /**
     * Using $truffleFile property (auto-detected format):
     *   protected $truffleFile = __DIR__ . '/../data/employees.csv';
     *
     * Or load explicitly via getRecords():
     */
    public function getRecords(): array
    {
        return $this->fromCsvFile(__DIR__ . '/../data/employees.csv');
    }
}

// Usage:
// Employee::all();
// Employee::where('department', 'Engineering')->get();
