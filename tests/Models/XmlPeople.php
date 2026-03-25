<?php

namespace Waad\Truffle\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Waad\Truffle\Enums\DataType;
use Waad\Truffle\Truffle;

class XmlPeople extends Model
{
    use Truffle;

    protected $table = 'xml_people';

    protected $fillable = ['name', 'email', 'is_admin', 'age'];

    protected $casts = [
        'is_admin' => 'boolean',
        'age' => 'integer',
    ];

    protected $schema = [
        'id' => DataType::Id,
        'name' => DataType::String,
        'email' => DataType::String,
        'is_admin' => DataType::Boolean,
        'age' => DataType::Integer,
    ];

    public function getRecords()
    {
        return $this->fromXmlFile(__DIR__ . '/../fixtures/people.xml', 'person');
    }
}
