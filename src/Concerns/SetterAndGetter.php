<?php

namespace Waad\Truffle\Concerns;

trait SetterAndGetter
{
    public function getRecords()
    {
        if (isset($this->records) && ! empty($this->records)) {
            return $this->records;
        }

        if ($this->getTruffleFile()) {
            return $this->getFileRecords();
        }

        return [];
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
