<?php

namespace Waad\Truffle\Tests;

use Illuminate\Database\Eloquent\Model;
use Waad\Truffle\Enums\DataType;
use Waad\Truffle\Tests\Models\Category;
use Waad\Truffle\Tests\Models\Country;
use Waad\Truffle\Tests\Models\People;
use Waad\Truffle\Tests\Models\SchemaOnlyModel;
use Waad\Truffle\Truffle;

class TruffleTest extends TestCase
{
    protected People $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new People();
    }

    protected function tearDown(): void
    {
        People::clearConnections();
        SchemaOnlyModel::clearConnections();
        Country::clearConnections();
        parent::tearDown();
    }

    public function test_model_can_be_instantiated(): void
    {
        $this->assertInstanceOf(People::class, $this->model);
        $this->assertInstanceOf(Model::class, $this->model);
    }

    public function test_model_uses_truffle_trait(): void
    {
        $traits = class_uses_recursive($this->model);
        $this->assertTrue(in_array(Truffle::class, $traits));
    }

    public function test_model_data_exists(): void
    {
        $data = People::all();
        $this->assertNotEmpty($data);
        $this->assertCount(3, $data);
    }

    public function test_model_can_query_data(): void
    {
        $user = People::find(1);
        $this->assertNotNull($user);
        $this->assertEquals('Waad Mawlood', $user->name);
        $this->assertEquals('waad@example.com', $user->email);
        $this->assertTrue($user->is_admin);
    }

    public function test_model_can_filter_data(): void
    {
        $admins = People::where('is_admin', true)->get();
        $this->assertCount(1, $admins);
        $this->assertEquals('Waad Mawlood', $admins->first()->name);

        $nonAdmins = People::where('is_admin', false)->get();
        $this->assertCount(2, $nonAdmins);
    }

    public function test_model_can_order_data(): void
    {
        $users = People::orderBy('age', 'desc')->get();
        $this->assertEquals('Waad Mawlood', $users->first()->name);
        $this->assertEquals('John Doe', $users->last()->name);
    }

    public function test_model_can_limit_data(): void
    {
        $users = People::limit(2)->get();
        $this->assertCount(2, $users);
    }

    public function test_model_can_paginate_data(): void
    {
        $paginated = People::paginate(2);
        $this->assertCount(2, $paginated->items());
        $this->assertEquals(3, $paginated->total());
        $this->assertEquals(2, $paginated->lastPage());
    }

    public function test_model_can_be_filled(): void
    {
        $this->model->fill([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'is_admin' => true,
            'age' => 35,
            'metadata' => ['department' => 'IT'],
        ]);

        $this->assertEquals('Test User', $this->model->name);
        $this->assertEquals('test@example.com', $this->model->email);
        $this->assertTrue($this->model->is_admin);
        $this->assertEquals(35, $this->model->age);
        $this->assertEquals(['department' => 'IT'], $this->model->metadata);
    }

    public function test_model_casts_work(): void
    {
        $user = People::find(1);
        $this->assertIsBool($user->is_admin);
        $this->assertIsInt($user->age);
        $this->assertIsArray($user->metadata);
    }

    public function test_model_attributes_are_correct_types(): void
    {
        $user = People::find(1);
        $this->assertIsString($user->name);
        $this->assertIsString($user->email);
        $this->assertIsBool($user->is_admin);
        $this->assertIsInt($user->age);
        $this->assertIsArray($user->metadata);
    }

    public function test_model_can_aggregate_data(): void
    {
        $count = People::count();
        $this->assertEquals(3, $count);

        $avgAge = People::avg('age');
        $this->assertEqualsWithDelta(27.666666666667, $avgAge, 0.1);

        $maxAge = People::max('age');
        $this->assertEquals(30, $maxAge);

        $minAge = People::min('age');
        $this->assertEquals(25, $minAge);
    }

    public function test_model_schema_is_correct(): void
    {
        $schema = $this->model->getSchema();
        $this->assertArrayHasKey('id', $schema);
        $this->assertArrayHasKey('name', $schema);
        $this->assertArrayHasKey('email', $schema);
        $this->assertArrayHasKey('is_admin', $schema);
        $this->assertArrayHasKey('age', $schema);
        $this->assertArrayHasKey('metadata', $schema);
        $this->assertArrayHasKey('category_id', $schema);

        $this->assertEquals(DataType::Id, $schema['id']);
        $this->assertEquals(DataType::String, $schema['name']);
        $this->assertEquals(DataType::Boolean, $schema['is_admin']);
        $this->assertEquals(DataType::Integer, $schema['age']);
        $this->assertEquals(DataType::String, $schema['metadata']);
        $this->assertEquals(DataType::UnsignedBigInteger, $schema['category_id']);
    }

    public function test_model_records_are_correct(): void
    {
        $records = $this->model->getRecords();
        $this->assertCount(3, $records);
        $this->assertEquals('Waad Mawlood', $records[0]['name']);
        $this->assertEquals('waad@example.com', $records[0]['email']);
        $this->assertTrue($records[0]['is_admin']);
    }

    public function test_model_can_handle_null_values(): void
    {
        $user = People::find(2);
        $this->assertNotNull($user);

        // Test that nullable fields can be null
        $user->age = null;
        $this->assertNull($user->age);
    }

    public function test_model_validates_data_types(): void
    {
        $user = People::find(1);

        // Test type validation through casting
        $user->is_admin = 'true';
        $this->assertTrue($user->is_admin);

        $user->is_admin = '0';
        $this->assertFalse($user->is_admin);

        $user->age = '35';
        $this->assertEquals(35, $user->age);
        $this->assertIsInt($user->age);
    }

    public function test_model_with_schema_only(): void
    {
        $model = new SchemaOnlyModel();

        $schema = $model->getSchema();
        $this->assertArrayHasKey('id', $schema);
        $this->assertArrayHasKey('name', $schema);
        $this->assertArrayHasKey('email', $schema);

        $records = $model->getRecords();
        $this->assertEmpty($records);
    }

    public function test_insert_chunk_records(): void
    {
        $chunkRecords = $this->model->getInsertChunkRecords();
        $this->assertEquals(100, $chunkRecords);
    }

    public function test_connection_resolution(): void
    {
        $connection = People::resolveConnection();
        $this->assertNotNull($connection);
    }

    public function test_ensure_data_is_inserted(): void
    {
        $data = People::all();
        $this->assertCount(3, $data);
    }

    public function test_ensure_fetch_first_record(): void
    {
        $data = People::find(1);
        $this->assertEquals('Waad Mawlood', $data->name);
        $this->assertEquals('waad@example.com', $data->email);
        $this->assertTrue($data->is_admin);
        $this->assertEquals(30, $data->age);
        $this->assertEquals(['role' => 'developer'], $data->metadata);
    }

    public function test_category_model(): void
    {
        $category = Category::find(1);
        $this->assertEquals('Human', $category->name);
    }

    public function test_people_from_category_model(): void
    {
        $category = Category::find(1);
        $people = $category->people;
        $this->assertCount(3, $people);
        $this->assertEquals('Waad Mawlood', $people->first()->name);
        $this->assertEquals('waad@example.com', $people->first()->email);
        $this->assertTrue($people->first()->is_admin);
        $this->assertEquals(30, $people->first()->age);
    }

    public function test_category_from_people_model(): void
    {
        $people = People::find(1);
        $category = $people->category;
        $this->assertEquals('Human', $category->name);
    }

    public function test_country_model_with_custom_primary_key(): void
    {
        $country = Country::find('UK');
        $this->assertNotNull($country);
        $this->assertEquals('code', $country->getKeyName());
        $this->assertEquals('string', $country->getKeyType());
        $this->assertEquals(false, $country->incrementing);
        $this->assertEquals('United Kingdom', $country->name);
    }
}
