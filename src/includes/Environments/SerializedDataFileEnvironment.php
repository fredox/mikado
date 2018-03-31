<?php

class SerializedDataFileEnvironment implements Environment
{
    public $name;
    public $filePath;
    public $rawQueries = array();

    public function __construct($name, $filePath)
    {
        $this->name       = $name;
        $this->filePath   = $filePath;
    }

    public function getName()
    {
        return $this->name;
    }

    public function get($queries, $key)
    {
        $returnedData = array();

        echo "\n [SerializedDataFile] reading data from file [" . $this->filePath . "]";
        $this->checkFile();

        $serializedData = file_get_contents($this->filePath);
        $data           = unserialize($serializedData);

        $selectedKeys = array_keys($queries);

        foreach ($data as $tableName => $rows) {
            if (in_array($tableName, $selectedKeys)) {
                echo "\n [SerializedDataFile] Getting data from [" . $tableName . "]";
                echo " (" . count($rows) . ")";
                $returnedData[$tableName] = $rows;
            }
        }

        return $returnedData;
    }

    public function put($data)
    {
        echo "\n [SerializedDataFile] putting data to [" . $this->filePath . "] file";

        $serializedData = serialize($data);
        file_put_contents($this->filePath, $serializedData);
    }

    private function checkFile()
    {
        if (!is_file($this->filePath)) {
            echo "\n [ERROR][SerializedDataFile] file not found: " . $this->filePath . "\n\n";
            exit(0);
        }
    }

    public function describe($dataIndex)
    {
        return false;
    }

    public function getType()
    {
        return 'SerializedDataFile';
    }

}