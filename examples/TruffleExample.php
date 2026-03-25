<?php

namespace Waad\Truffle\Examples;

use Illuminate\Database\Eloquent\Model;
use Waad\Truffle\Enums\DataType;
use Waad\Truffle\Truffle;

/**
 * Full-featured example demonstrating most Truffle capabilities.
 */
class TruffleExample extends Model
{
    use Truffle;

    protected $table = 'example_users';

    protected $fillable = ['name', 'email', 'age', 'is_active', 'salary', 'department', 'metadata'];

    protected $casts = [
        'is_active' => 'boolean',
        'age' => 'integer',
        'salary' => 'float',
        'metadata' => 'array',
    ];

    protected $records = [
        ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'age' => 30, 'is_active' => true, 'salary' => 75000.00, 'department' => 'Engineering', 'metadata' => ['skills' => ['PHP', 'Laravel']]],
        ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com', 'age' => 28, 'is_active' => true, 'salary' => 80000.00, 'department' => 'Engineering', 'metadata' => ['skills' => ['React', 'Node.js']]],
        ['id' => 3, 'name' => 'Bob Johnson', 'email' => 'bob@example.com', 'age' => 35, 'is_active' => false, 'salary' => 65000.00, 'department' => 'Marketing', 'metadata' => ['skills' => ['SEO', 'Analytics']]],
        ['id' => 4, 'name' => 'Alice Brown', 'email' => 'alice@example.com', 'age' => 32, 'is_active' => true, 'salary' => 90000.00, 'department' => 'Management', 'metadata' => ['skills' => ['Leadership', 'Strategy']]],
    ];

    protected $schema = [
        'id' => DataType::Id,
        'name' => DataType::String,
        'email' => DataType::String,
        'age' => DataType::Integer,
        'is_active' => DataType::Boolean,
        'salary' => DataType::Float,
        'department' => DataType::String,
        'metadata' => DataType::Json,
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInDepartment($query, string $department)
    {
        return $query->where('department', $department);
    }

    public function scopeWithSalaryAbove($query, float $amount)
    {
        return $query->where('salary', '>', $amount);
    }

    public function getFormattedSalaryAttribute(): string
    {
        return '$' . number_format($this->salary, 2);
    }
}

// Usage:
// TruffleExample::all();
// TruffleExample::active()->count();
// TruffleExample::inDepartment('Engineering')->get();
// TruffleExample::withSalaryAbove(70000)->active()->orderBy('salary', 'desc')->get();
// TruffleExample::avg('salary');
// TruffleExample::paginate(10);
