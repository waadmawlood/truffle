<?php

namespace Waad\Truffle\Readers;

use RuntimeException;

class JsonReader
{
    /**
     * @param string $filePath
     * @return array
     */
    public function read($filePath)
    {
        if (! file_exists($filePath)) {
            throw new RuntimeException(sprintf('Truffle file not found: %s', $filePath));
        }

        $contents = file_get_contents($filePath);
        if ($contents === false) {
            throw new RuntimeException(sprintf('Unable to read file: %s', $filePath));
        }

        $data = json_decode($contents, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(sprintf(
                'Invalid JSON in file %s: %s',
                $filePath,
                json_last_error_msg()
            ));
        }

        if (! is_array($data)) {
            throw new RuntimeException(sprintf(
                'JSON file %s must contain an array of objects',
                $filePath
            ));
        }

        return $data;
    }
}
