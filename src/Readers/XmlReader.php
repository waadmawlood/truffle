<?php

namespace Waad\Truffle\Readers;

use RuntimeException;
use SimpleXMLElement;

class XmlReader
{
    /** @var string|null */
    protected $recordElement;

    /**
     * @param string|null $recordElement
     */
    public function __construct($recordElement = null)
    {
        $this->recordElement = $recordElement;
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

        $contents = file_get_contents($filePath);
        if ($contents === false) {
            throw new RuntimeException(sprintf('Unable to read file: %s', $filePath));
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($contents);

        if ($xml === false) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            $message = ! empty($errors) ? $errors[0]->message : 'Unknown XML error';
            throw new RuntimeException(sprintf('Invalid XML in file %s: %s', $filePath, trim($message)));
        }

        $children = $this->recordElement !== null
            ? $xml->{$this->recordElement}
            : $xml->children();

        $records = [];
        foreach ($children as $child) {
            $records[] = $this->xmlElementToArray($child);
        }

        return $records;
    }

    /**
     * @param SimpleXMLElement $element
     * @return array
     */
    protected function xmlElementToArray(SimpleXMLElement $element)
    {
        $record = [];

        foreach ($element->children() as $name => $child) {
            if ($child->count() > 0) {
                $record[$name] = json_encode($this->xmlElementToArray($child));
            } else {
                $value = (string) $child;
                $record[$name] = $this->castValue($value);
            }
        }

        return $record;
    }

    /**
     * @param string $value
     * @return mixed
     */
    protected function castValue($value)
    {
        if ($value === '') {
            return null;
        }

        if ($value === 'true') {
            return true;
        }

        if ($value === 'false') {
            return false;
        }

        if (is_numeric($value) && strpos($value, '.') === false && strlen($value) === strlen((string) (int) $value)) {
            return (int) $value;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        return $value;
    }
}
