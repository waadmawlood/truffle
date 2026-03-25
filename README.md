![Laravel Truffle](truffle.jpg)

# Waad/Truffle

[![Latest Version on Packagist](https://img.shields.io/packagist/v/waad/truffle.svg?style=flat-square)](https://packagist.org/packages/waad/truffle)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/waad/truffle/run-tests?label=tests)](https://github.com/waad/truffle/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/waad/truffle.svg?style=flat-square)](https://packagist.org/packages/waad/truffle)

Eloquent models backed by in-memory SQLite. Perfect for static data, reference tables, and config that doesn't belong in your database.

**Zero config** &bull; **Full Eloquent API** &bull; **CSV / JSON / XML support** &bull; **Per-model caching** &bull; **Optional file-based SQLite**

## Installation

```bash
composer require waad/truffle
```

## Quick Start

```php
use Illuminate\Database\Eloquent\Model;
use Waad\Truffle\Truffle;

class Product extends Model
{
    use Truffle;

    protected $fillable = ['name', 'price', 'category'];
    protected $casts = ['price' => 'float'];

    protected $records = [
        ['id' => 1, 'name' => 'Laptop', 'price' => 999.99, 'category' => 'Electronics'],
        ['id' => 2, 'name' => 'Coffee Mug', 'price' => 12.50, 'category' => 'Kitchen'],
    ];
}

Product::all();
Product::where('category', 'Electronics')->first();
Product::avg('price');
```

## Schema

Define column types explicitly with `DataType`. If omitted, types are inferred from your data.

```php
use Waad\Truffle\Enums\DataType;

protected $schema = [
    'id'    => DataType::Id,
    'name'  => DataType::String,
    'price' => DataType::Decimal,
    'active'=> DataType::Boolean,
];
```

<details>
<summary>All DataType values</summary>

| DataType | DB Type |
|----------|---------|
| `Id` | INTEGER (PK, auto-increment) |
| `String` | VARCHAR(255) |
| `Text` | TEXT |
| `Integer` | INTEGER |
| `BigInteger` | BIGINT |
| `UnsignedBigInteger` | UNSIGNED BIGINT |
| `Float` | FLOAT |
| `Double` | DOUBLE |
| `Decimal` | DECIMAL |
| `Boolean` | BOOLEAN |
| `Json` / `Jsonb` | TEXT |
| `Date` | DATE |
| `DateTime` | DATETIME |
| `Time` | TIME |
| `Timestamp` | TIMESTAMP |
| `Uuid` | CHAR(36) |
| `Ulid` | CHAR(26) |

</details>

## Dynamic Records

Override `getRecords()` to generate data at runtime:

```php
public function getRecords(): array
{
    return collect(range(1, 100))->map(fn ($i) => [
        'id' => $i,
        'name' => "User {$i}",
    ])->toArray();
}
```

## File-Based Records

Load records from CSV, JSON, or XML files. Format is auto-detected from the extension.

```php
// Via property (auto-detected)
protected $truffleFile = __DIR__ . '/../data/countries.csv';

// Or via getRecords()
public function getRecords(): array
{
    return $this->fromCsvFile(__DIR__ . '/../data/countries.csv');
    // return $this->fromJsonFile(__DIR__ . '/../data/products.json');
    // return $this->fromXmlFile(__DIR__ . '/../data/categories.xml', 'category');
}
```

CSV custom delimiters are supported via `$truffleFileDelimiter`, `$truffleFileEnclosure`, and `$truffleFileEscape` properties.

> See full examples: [`CsvModel.php`](examples/CsvModel.php), [`JsonModel.php`](examples/JsonModel.php), [`XmlModel.php`](examples/XmlModel.php)

## Caching

Enable per-model caching to avoid rebuilding the SQLite table on every request:

```php
protected $truffleCache = true;
protected $truffleCacheTtl = 3600;        // seconds (null = forever)
// protected $truffleCacheDriver = 'redis';
// protected $truffleCachePrefix = 'app_';
```

```php
Model::clearTruffleCache();    // clear cached records
Model::refreshTruffleCache();  // clear + rebuild
```

> See full example: [`CachedModel.php`](examples/CachedModel.php)

## SQLite File Storage

Persist to a SQLite file instead of in-memory. Ideal for large datasets:

```php
protected static $truffleSqliteFile = '/path/to/database.sqlite';
```

```php
Model::deleteTruffleSqliteFile();    // delete the file
Model::refreshTruffleSqliteFile();   // delete + rebuild
```

> See full example: [`SqliteFileModel.php`](examples/SqliteFileModel.php)

## Performance Tuning

```php
protected $insertChunkRecords = 500;       // batch insert size
protected $foreignKeyConstraints = true;   // enable FK constraints

protected function thenMigration(Blueprint $table)
{
    $table->index('name');
}
```

## Validation

Works with Laravel's `exists` rule:

```php
'category_id' => ['required', Rule::exists(Category::class, 'id')],
```

## Examples

See the [`examples/`](examples/) directory for complete, runnable models:

| Example | Description |
|---------|-------------|
| [`BasicModel.php`](examples/BasicModel.php) | Inline records with scopes |
| [`CountryModel.php`](examples/CountryModel.php) | Non-incrementing string primary key |
| [`DynamicRecordsModel.php`](examples/DynamicRecordsModel.php) | Generated records via `getRecords()` |
| [`CachedModel.php`](examples/CachedModel.php) | Caching with TTL and custom driver |
| [`SqliteFileModel.php`](examples/SqliteFileModel.php) | File-based SQLite persistence |
| [`CsvModel.php`](examples/CsvModel.php) | Load data from CSV |
| [`JsonModel.php`](examples/JsonModel.php) | Load data from JSON |
| [`XmlModel.php`](examples/XmlModel.php) | Load data from XML |
| [`TruffleExample.php`](examples/TruffleExample.php) | Full-featured model with all options |

## Testing

```bash
composer test
```

## Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Add tests and make your changes
4. Run tests: `composer test`
5. Submit a Pull Request

## Roadmap

- [x] Eloquent integration
- [x] SQLite in-memory support
- [x] SQLite file support
- [x] Caching support
- [x] Support for CSV/JSON/XML files
- [ ] Multi-tenancy support

## Credits

Built with love for the Laravel community by [Waad Mawlood](https://github.com/waadmawlood)
