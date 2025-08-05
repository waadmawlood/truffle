<?php

namespace Waad\Truffle\Concerns;

trait SetterAndGetter
{
    public function getRecords()
    {
        return isset($this->records) ? $this->records : [];
    }

    public function getSchema()
    {
        return isset($this->schema) ? $this->schema : [];
    }

    public function getInsertChunkRecords()
    {
        return isset($this->insertChunkRecords) ? $this->insertChunkRecords : 100;
    }
}
