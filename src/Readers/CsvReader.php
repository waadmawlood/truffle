<?php

namespace Waad\Truffle\Readers;

use RuntimeException;

class CsvReader
{
    /** @var string */
    protected $delimiter;

    /** @var string */
    protected $enclosure;

    /** @var string */
    protected $escape;

    /**
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escape
     */
    public function __construct($delimiter = ',', $enclosure = '"', $escape = '\\')
    {
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        $this->escape = $escape;
    }

    /**
     * @param string $filePath
     * @return array
     */
    public function read($filePath)
    {
        if (! file_exists($filePath)) {
            throw new RuntimeException(sprintf('Truffle file not found: %s', $filePath));
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new RuntimeException(sprintf('Unable to open file: %s', $filePath));
        }

        try {
            $headers = fgetcsv($handle, 0, $this->delimiter, $this->enclosure, $this->escape);

            if ($headers === false || $headers === [null]) {
                return [];
            }

            $headers = array_map('trim', $headers);
            $records = [];

            while (($row = fgetcsv($handle, 0, $this->delimiter, $this->enclosure, $this->escape)) !== false) {
                if ($row === [null]) {
                    continue;
                }

                $record = [];
                foreach ($headers as $index => $header) {
                    $record[$header] = isset($row[$index]) ? $row[$index] : null;
                }
                $records[] = $record;
            }

            return $records;
        } finally {
            fclose($handle);
        }
    }
}
