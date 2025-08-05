<?php

namespace Waad\Truffle\Examples;

use Illuminate\Database\Eloquent\Model;
use Waad\Truffle\Truffle;

/**
 * Example model demonstrating the Truffle trait usage
 *
 * This example shows how to:
 * - Define in-memory data records
 * - Set up a schema for type safety
 * - Use Laravel's casting and fillable properties
 * - Query the in-memory data using Eloquent
 */
class TruffleExample extends Model
{
    use Truffle;

    protected $table = 'example_users';

    protected $fillable = [
        'name',
        'email',
        'age',
        'is_active',
        'salary',
        'department',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'age' => 'integer',
        'salary' => 'float',
        'metadata' => 'array',
    ];

    // Define the in-memory data records
    protected $records = [
        [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'age' => 30,
            'is_active' => true,
            'salary' => 75000.00,
            'department' => 'Engineering',
            'metadata' => [
                'skills' => ['PHP', 'Laravel', 'Vue.js'],
                'location' => 'Remote',
                'start_date' => '2022-01-15',
            ],
        ],
        [
            'id' => 2,
            'name' => 'Jane Smith',
            'email' => 'jane.smith@example.com',
            'age' => 28,
            'is_active' => true,
            'salary' => 80000.00,
            'department' => 'Engineering',
            'metadata' => [
                'skills' => ['JavaScript', 'React', 'Node.js'],
                'location' => 'New York',
                'start_date' => '2021-06-10',
            ],
        ],
        [
            'id' => 3,
            'name' => 'Bob Johnson',
            'email' => 'bob.johnson@example.com',
            'age' => 35,
            'is_active' => false,
            'salary' => 65000.00,
            'department' => 'Marketing',
            'metadata' => [
                'skills' => ['SEO', 'Content Marketing', 'Analytics'],
                'location' => 'San Francisco',
                'start_date' => '2020-03-01',
            ],
        ],
        [
            'id' => 4,
            'name' => 'Alice Brown',
            'email' => 'alice.brown@example.com',
            'age' => 32,
            'is_active' => true,
            'salary' => 90000.00,
            'department' => 'Management',
            'metadata' => [
                'skills' => ['Leadership', 'Project Management', 'Strategy'],
                'location' => 'Chicago',
                'start_date' => '2019-09-15',
            ],
        ],
        [
            'id' => 5,
            'name' => 'Charlie Wilson',
            'email' => 'charlie.wilson@example.com',
            'age' => 26,
            'is_active' => true,
            'salary' => 55000.00,
            'department' => 'Design',
            'metadata' => [
                'skills' => ['UI/UX', 'Figma', 'Adobe Creative Suite'],
                'location' => 'Los Angeles',
                'start_date' => '2023-02-20',
            ],
        ],
    ];

    // Define the schema for type safety and validation
    protected $schema = [
        'id' => 'integer',
        'name' => 'string',
        'email' => 'string',
        'age' => 'integer',
        'is_active' => 'boolean',
        'salary' => 'float',
        'department' => 'string',
        'metadata' => 'string', // Will be cast to array by Laravel
    ];

    /**
     * Accessor for formatted salary
     */
    public function getFormattedSalaryAttribute(): string
    {
        return '$'.number_format($this->salary, 2);
    }

    /**
     * Accessor for skills list
     */
    public function getSkillsAttribute(): array
    {
        return $this->metadata['skills'] ?? [];
    }

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for users by department
     */
    public function scopeInDepartment($query, string $department)
    {
        return $query->where('department', $department);
    }

    /**
     * Scope for users with salary above threshold
     */
    public function scopeWithSalaryAbove($query, float $amount)
    {
        return $query->where('salary', '>', $amount);
    }

    /**
     * Example method to demonstrate usage
     */
    public static function demonstrateUsage(): array
    {
        $results = [];

        // Basic queries
        $results['total_users'] = static::count();
        $results['active_users'] = static::active()->count();
        $results['engineering_team'] = static::inDepartment('Engineering')->count();

        // Find specific user
        $john = static::where('name', 'John Doe')->first();
        $results['john_formatted_salary'] = $john ? $john->formatted_salary : null;
        $results['john_skills'] = $john ? $john->skills : [];

        // Aggregate data
        $results['average_salary'] = static::avg('salary');
        $results['max_salary'] = static::max('salary');
        $results['min_age'] = static::min('age');

        // Complex queries
        $results['high_earners'] = static::withSalaryAbove(70000)
            ->active()
            ->orderBy('salary', 'desc')
            ->pluck('name')
            ->toArray();

        // Department breakdown
        $results['department_stats'] = static::selectRaw('department, COUNT(*) as count, AVG(salary) as avg_salary')
            ->groupBy('department')
            ->get()
            ->toArray();

        // Pagination example
        $paginated = static::paginate(3);
        $results['pagination'] = [
            'current_page' => $paginated->currentPage(),
            'total_pages' => $paginated->lastPage(),
            'total_records' => $paginated->total(),
            'per_page' => $paginated->perPage(),
        ];

        return $results;
    }

    /**
     * Performance test with larger dataset
     */
    public static function performanceTest(int $recordCount = 1000): array
    {
        $start = microtime(true);

        // Generate test data
        $testRecords = [];
        for ($i = 1; $i <= $recordCount; $i++) {
            $testRecords[] = [
                'id' => $i,
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'age' => rand(22, 65),
                'is_active' => (bool) rand(0, 1),
                'salary' => rand(40000, 120000),
                'department' => ['Engineering', 'Marketing', 'Design', 'Management'][rand(0, 3)],
                'metadata' => [
                    'location' => ['Remote', 'New York', 'San Francisco', 'Chicago'][rand(0, 3)],
                    'skills' => ['Skill A', 'Skill B', 'Skill C'],
                ],
            ];
        }

        // Create temporary model with test data
        $testModel = new class extends Model
        {
            use Truffle;

            protected $table = 'performance_test';

            protected $casts = ['is_active' => 'boolean', 'metadata' => 'array'];
        };

        $testModel->records = $testRecords;

        $insertTime = microtime(true) - $start;

        // Performance queries
        $queryStart = microtime(true);
        $totalCount = $testModel::count();
        $activeCount = $testModel::where('is_active', true)->count();
        $avgSalary = $testModel::avg('salary');
        $queryTime = microtime(true) - $queryStart;

        // Memory usage
        $memoryUsage = memory_get_peak_usage(true) / 1024 / 1024; // MB

        // Cleanup
        $testModel::clearConnections();

        return [
            'records_created' => $recordCount,
            'actual_count' => $totalCount,
            'active_users' => $activeCount,
            'average_salary' => round($avgSalary, 2),
            'insert_time' => round($insertTime, 4),
            'query_time' => round($queryTime, 4),
            'memory_usage' => round($memoryUsage, 2),
        ];
    }
}
