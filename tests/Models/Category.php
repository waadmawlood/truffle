<?php

namespace Waad\Truffle\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name'];

    public function people()
    {
        return $this->hasMany(People::class);
    }
}
