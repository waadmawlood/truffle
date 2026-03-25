<?php

namespace Waad\Truffle\Tests;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;
use Waad\Truffle\Enums\DataType;
use Waad\Truffle\Enums\FileType;
use Waad\Truffle\Tests\Models\CsvPeople;
use Waad\Truffle\Tests\Models\JsonPeople;
use Waad\Truffle\Tests\Models\XmlPeople;
use Waad\Truffle\Truffle;

class FileRecordsTest extends TestCase
{
    protected function tearDown(): void
    {
        CsvPeople::clearConnections();
        JsonPeople::clearConnections();
        XmlPeople::clearConnections();
        parent::tearDown();
    }

    // ── CSV Tests ──────────────────────────────────────────────

    public function test_csv_file_loads_records(): void
    {
        $data = CsvPeople::all();
        $this->assertCount(3, $data);
    }

    public function test_csv_file_first_record_values(): void
    {
        $person = CsvPeople::find(1);
        $this->assertNotNull($person);
        $this->assertEquals('Waad Mawlood', $person->name);
        $this->assertEquals('waad@example.com', $person->email);
        $this->assertEquals(30, $person->age);
    }

    public function test_csv_file_querying(): void
    {
        $young = CsvPeople::where('age', '<', 29)->get();
        $this->assertCount(2, $young);
    }

    public function test_csv_with_custom_delimiter(): void
    {
        $model = new class () extends Model {
            use Truffle;

            protected $table = 'semicolon_csv_people';

            protected $truffleFile = __DIR__ . '/fixtures/people_semicolon.csv';

            protected $truffleFileDelimiter = ';';

            protected $schema = [
                'id' => DataType::Id,
                'name' => DataType::String,
                'email' => DataType::String,
                'is_admin' => DataType::Boolean,
                'age' => DataType::Integer,
            ];
        };

        $records = $model->getRecords();
        $this->assertCount(3, $records);
        $this->assertEquals('Waad Mawlood', $records[0]['name']);

        $model::clearConnections();
    }

    // ── JSON Tests ─────────────────────────────────────────────

    public function test_json_file_loads_records(): void
    {
        $data = JsonPeople::all();
        $this->assertCount(3, $data);
    }

    public function test_json_file_first_record_values(): void
    {
        $person = JsonPeople::find(1);
        $this->assertNotNull($person);
        $this->assertEquals('Waad Mawlood', $person->name);
        $this->assertEquals('waad@example.com', $person->email);
        $this->assertTrue($person->is_admin);
        $this->assertEquals(30, $person->age);
    }

    public function test_json_file_querying(): void
    {
        $admins = JsonPeople::where('is_admin', true)->get();
        $this->assertCount(1, $admins);
        $this->assertEquals('Waad Mawlood', $admins->first()->name);
    }

    public function test_json_file_aggregation(): void
    {
        $avgAge = JsonPeople::avg('age');
        $this->assertEqualsWithDelta(27.666, $avgAge, 0.01);
    }

    // ── XML Tests ──────────────────────────────────────────────

    public function test_xml_file_loads_records(): void
    {
        $data = XmlPeople::all();
        $this->assertCount(3, $data);
    }

    public function test_xml_file_first_record_values(): void
    {
        $person = XmlPeople::find(1);
        $this->assertNotNull($person);
        $this->assertEquals('Waad Mawlood', $person->name);
        $this->assertEquals('waad@example.com', $person->email);
        $this->assertTrue($person->is_admin);
        $this->assertEquals(30, $person->age);
    }

    public function test_xml_file_querying(): void
    {
        $nonAdmins = XmlPeople::where('is_admin', false)->get();
        $this->assertCount(2, $nonAdmins);
    }

    // ── Format Detection ───────────────────────────────────────

    public function test_auto_detect_csv_type(): void
    {
        $model = new class () extends Model {
            use Truffle;

            protected $table = 'detect_csv';

            protected $truffleFile = __DIR__ . '/fixtures/people.csv';
        };

        $this->assertEquals(FileType::Csv, $model->getTruffleFileType());
        $model::clearConnections();
    }

    public function test_auto_detect_json_type(): void
    {
        $model = new class () extends Model {
            use Truffle;

            protected $table = 'detect_json';

            protected $truffleFile = __DIR__ . '/fixtures/people.json';
        };

        $this->assertEquals(FileType::Json, $model->getTruffleFileType());
        $model::clearConnections();
    }

    public function test_auto_detect_xml_type(): void
    {
        $model = new class () extends Model {
            use Truffle;

            protected $table = 'detect_xml';

            protected $truffleFile = __DIR__ . '/fixtures/people.xml';
        };

        $this->assertEquals(FileType::Xml, $model->getTruffleFileType());
        $model::clearConnections();
    }

    public function test_explicit_file_type_override(): void
    {
        $model = new class () extends Model {
            use Truffle;

            protected $table = 'override_type_people';

            protected $truffleFile = __DIR__ . '/fixtures/people.json';

            protected $truffleFileType = 'json';

            protected $schema = [
                'id' => DataType::Id,
                'name' => DataType::String,
            ];
        };

        $this->assertEquals(FileType::Json, $model->getTruffleFileType());
        $model::clearConnections();
    }

    // ── Priority: $records wins over $truffleFile ──────────────

    public function test_records_property_takes_priority_over_file(): void
    {
        $model = new class () extends Model {
            use Truffle;

            protected $table = 'priority_people';

            protected $records = [
                ['id' => 99, 'name' => 'From Records'],
            ];

            protected $truffleFile = __DIR__ . '/fixtures/people.json';

            protected $schema = [
                'id' => DataType::Id,
                'name' => DataType::String,
            ];
        };

        $records = $model->getRecords();
        $this->assertCount(1, $records);
        $this->assertEquals('From Records', $records[0]['name']);
        $model::clearConnections();
    }

    // ── Error Handling ─────────────────────────────────────────

    public function test_file_not_found_throws_exception(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Truffle file not found');

        $model = new class () extends Model {
            use Truffle;

            protected $table = 'missing_file';

            protected $truffleFile = __DIR__ . '/fixtures/nonexistent.csv';
        };

        $model->getRecords();
    }

    public function test_unsupported_extension_throws_exception(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unsupported file extension');

        $model = new class () extends Model {
            use Truffle;

            protected $table = 'bad_extension';

            protected $truffleFile = __DIR__ . '/fixtures/people.txt';
        };

        $model->getTruffleFileType();
    }

    public function test_invalid_json_throws_exception(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'truffle_test_') . '.json';
        file_put_contents($tmpFile, '{invalid json content');

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('Invalid JSON');

            $model = new class () extends Model {
                use Truffle;

                protected $table = 'invalid_json';
            };

            $model->truffleFile = $tmpFile;
            $model->getRecords();
        } finally {
            @unlink($tmpFile);
        }
    }

    public function test_invalid_xml_throws_exception(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'truffle_test_') . '.xml';
        file_put_contents($tmpFile, '<not-valid-xml><unclosed>');

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('Invalid XML');

            $model = new class () extends Model {
                use Truffle;

                protected $table = 'invalid_xml';
            };

            $model->truffleFile = $tmpFile;
            $model->getRecords();
        } finally {
            @unlink($tmpFile);
        }
    }

    // ── Dynamic fromFile / fromCsvFile / fromJsonFile / fromXmlFile ──

    public function test_from_csv_file_in_get_records(): void
    {
        $model = new class () extends Model {
            use Truffle;

            protected $table = 'dynamic_csv';

            protected $schema = [
                'id' => DataType::Id,
                'name' => DataType::String,
                'email' => DataType::String,
                'is_admin' => DataType::Boolean,
                'age' => DataType::Integer,
            ];

            public function getRecords()
            {
                return $this->fromCsvFile(__DIR__ . '/fixtures/people.csv');
            }
        };

        $data = $model::all();
        $this->assertCount(3, $data);
        $this->assertEquals('Waad Mawlood', $data->first()->name);
        $model::clearConnections();
    }

    public function test_from_json_file_in_get_records(): void
    {
        $model = new class () extends Model {
            use Truffle;

            protected $table = 'dynamic_json';

            protected $schema = [
                'id' => DataType::Id,
                'name' => DataType::String,
                'email' => DataType::String,
                'is_admin' => DataType::Boolean,
                'age' => DataType::Integer,
            ];

            public function getRecords()
            {
                return $this->fromJsonFile(__DIR__ . '/fixtures/people.json');
            }
        };

        $data = $model::all();
        $this->assertCount(3, $data);
        $this->assertEquals('Waad Mawlood', $data->first()->name);
        $model::clearConnections();
    }

    public function test_from_xml_file_in_get_records(): void
    {
        $model = new class () extends Model {
            use Truffle;

            protected $table = 'dynamic_xml';

            protected $schema = [
                'id' => DataType::Id,
                'name' => DataType::String,
                'email' => DataType::String,
                'is_admin' => DataType::Boolean,
                'age' => DataType::Integer,
            ];

            public function getRecords()
            {
                return $this->fromXmlFile(__DIR__ . '/fixtures/people.xml', 'person');
            }
        };

        $data = $model::all();
        $this->assertCount(3, $data);
        $this->assertEquals('Waad Mawlood', $data->first()->name);
        $model::clearConnections();
    }

    public function test_from_file_auto_detect(): void
    {
        $model = new class () extends Model {
            use Truffle;

            protected $table = 'dynamic_auto';

            protected $schema = [
                'id' => DataType::Id,
                'name' => DataType::String,
                'email' => DataType::String,
                'is_admin' => DataType::Boolean,
                'age' => DataType::Integer,
            ];

            public function getRecords()
            {
                return $this->fromFile(__DIR__ . '/fixtures/people.json');
            }
        };

        $records = $model->getRecords();
        $this->assertCount(3, $records);
        $this->assertEquals('Waad Mawlood', $records[0]['name']);
        $model::clearConnections();
    }

    public function test_from_file_with_explicit_type_and_options(): void
    {
        $model = new class () extends Model {
            use Truffle;

            protected $table = 'dynamic_explicit';

            protected $schema = [
                'id' => DataType::Id,
                'name' => DataType::String,
                'email' => DataType::String,
                'is_admin' => DataType::Boolean,
                'age' => DataType::Integer,
            ];

            public function getRecords()
            {
                return $this->fromFile(__DIR__ . '/fixtures/people_semicolon.csv', 'csv', [
                    'delimiter' => ';',
                ]);
            }
        };

        $records = $model->getRecords();
        $this->assertCount(3, $records);
        $this->assertEquals('Waad Mawlood', $records[0]['name']);
        $model::clearConnections();
    }

    public function test_from_csv_file_with_custom_delimiter(): void
    {
        $model = new class () extends Model {
            use Truffle;

            protected $table = 'dynamic_semicolon';

            public function getRecords()
            {
                return $this->fromCsvFile(__DIR__ . '/fixtures/people_semicolon.csv', ';');
            }
        };

        $records = $model->getRecords();
        $this->assertCount(3, $records);
        $this->assertEquals('john@example.com', $records[1]['email']);
        $model::clearConnections();
    }

    // ── No file configured returns empty ───────────────────────

    public function test_no_truffle_file_returns_null(): void
    {
        $model = new class () extends Model {
            use Truffle;

            protected $table = 'no_file';
        };

        $this->assertNull($model->getTruffleFile());
        $model::clearConnections();
    }

    public function test_no_truffle_file_type_returns_null(): void
    {
        $model = new class () extends Model {
            use Truffle;

            protected $table = 'no_file_type';
        };

        $this->assertNull($model->getTruffleFileType());
        $model::clearConnections();
    }
}
