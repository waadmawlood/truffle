<?php

namespace Waad\Truffle\Concerns;

use RuntimeException;
use Waad\Truffle\Enums\FileType;
use Waad\Truffle\Readers\CsvReader;
use Waad\Truffle\Readers\JsonReader;
use Waad\Truffle\Readers\XmlReader;

trait FileRecords
{
    public function getTruffleFile()
    {
        return isset($this->truffleFile) ? $this->truffleFile : null;
    }

    public function getTruffleFileType()
    {
        if (isset($this->truffleFileType)) {
            return $this->truffleFileType;
        }

        $file = $this->getTruffleFile();
        if ($file === null) {
            return null;
        }

        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        $map = [
            'csv' => FileType::Csv,
            'json' => FileType::Json,
            'xml' => FileType::Xml,
        ];

        if (! isset($map[$extension])) {
            throw new RuntimeException(sprintf(
                'Unsupported file extension "%s" for Truffle file: %s. Supported: csv, json, xml.',
                $extension,
                $file
            ));
        }

        return $map[$extension];
    }

    public function getTruffleFileDelimiter()
    {
        return isset($this->truffleFileDelimiter) ? $this->truffleFileDelimiter : ',';
    }

    public function getTruffleFileEnclosure()
    {
        return isset($this->truffleFileEnclosure) ? $this->truffleFileEnclosure : '"';
    }

    public function getTruffleFileEscape()
    {
        return isset($this->truffleFileEscape) ? $this->truffleFileEscape : '\\';
    }

    public function getTruffleFileRecordElement()
    {
        return isset($this->truffleFileRecordElement) ? $this->truffleFileRecordElement : null;
    }

    public function getFileRecords()
    {
        $file = $this->getTruffleFile();
        if ($file === null) {
            return [];
        }

        $type = $this->getTruffleFileType();

        switch ($type) {
            case FileType::Csv:
                return $this->fromCsvFile(
                    $file,
                    $this->getTruffleFileDelimiter(),
                    $this->getTruffleFileEnclosure(),
                    $this->getTruffleFileEscape()
                );

            case FileType::Json:
                return $this->fromJsonFile($file);

            case FileType::Xml:
                return $this->fromXmlFile($file, $this->getTruffleFileRecordElement());

            default:
                throw new RuntimeException(sprintf('Unsupported Truffle file type: %s', $type));
        }
    }

    /**
     * @param string $filePath
     * @param string $type
     * @param array $options
     * @return array
     */
    public function fromFile($filePath, $type = null, $options = [])
    {
        if ($type === null) {
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $map = [
                'csv' => FileType::Csv,
                'json' => FileType::Json,
                'xml' => FileType::Xml,
            ];

            if (! isset($map[$extension])) {
                throw new RuntimeException(sprintf(
                    'Unsupported file extension "%s": %s. Supported: csv, json, xml.',
                    $extension,
                    $filePath
                ));
            }

            $type = $map[$extension];
        }

        switch ($type) {
            case FileType::Csv:
                return $this->fromCsvFile(
                    $filePath,
                    isset($options['delimiter']) ? $options['delimiter'] : ',',
                    isset($options['enclosure']) ? $options['enclosure'] : '"',
                    isset($options['escape']) ? $options['escape'] : '\\'
                );

            case FileType::Json:
                return $this->fromJsonFile($filePath);

            case FileType::Xml:
                return $this->fromXmlFile(
                    $filePath,
                    isset($options['recordElement']) ? $options['recordElement'] : null
                );

            default:
                throw new RuntimeException(sprintf('Unsupported Truffle file type: %s', $type));
        }
    }

    /**
     * @param string $filePath
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escape
     * @return array
     */
    public function fromCsvFile($filePath, $delimiter = ',', $enclosure = '"', $escape = '\\')
    {
        $reader = new CsvReader($delimiter, $enclosure, $escape);

        return $reader->read($filePath);
    }

    /**
     * @param string $filePath
     * @return array
     */
    public function fromJsonFile($filePath)
    {
        $reader = new JsonReader();

        return $reader->read($filePath);
    }

    /**
     * @param string $filePath
     * @param string|null $recordElement
     * @return array
     */
    public function fromXmlFile($filePath, $recordElement = null)
    {
        $reader = new XmlReader($recordElement);

        return $reader->read($filePath);
    }
}
