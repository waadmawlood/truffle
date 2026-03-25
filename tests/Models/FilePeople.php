<?php

namespace Waad\Truffle\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Waad\Truffle\Enums\DataType;
use Waad\Truffle\Truffle;

class FilePeople extends Model
{
    use Truffle;

    protected $table = 'file_people';

    protected static $truffleSqliteFile;

    protected $fillable = ['name', 'email', 'role'];

    protected $schema = [
        'id' => DataType::Id,
        'name' => DataType::String,
        'email' => DataType::String,
        'role' => DataType::String,
    ];

    protected $records = [
        ['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com', 'role' => 'admin'],
        ['id' => 2, 'name' => 'Bob', 'email' => 'bob@example.com', 'role' => 'editor'],
        ['id' => 3, 'name' => 'Charlie', 'email' => 'charlie@example.com', 'role' => 'viewer'],
    ];

    public static function setTruffleSqliteFile(?string $path): void
    {
        static::$truffleSqliteFile = $path;
    }
}
