<?php

include_once('Environment.php');
include_once('includes/Input.php');

class KeyFileEnvironment implements Environment
{
    public $name;
    public $filePath;
    public $keyField;
    public $fileAppend;

    public function __construct($name, $keyField='value', $fileAppend = false)
    {
        $this->name     = $name;
        $this->filePath = Input::INPUT_OUTPUT_FOLDER . '/' . $name;
        $this->keyField = $keyField;
    }

    public function getName()
    {
        return $this->name;
    }

    public function get($queries, $key)
    {
        $data = file_get_contents($this->filePath);

        return $data;
    }

    public function put($data)
    {
        $keys = array();

        echo "\n [KEY-FILE] Cleaning key file: " . $this->filePath;

        file_put_contents($this->filePath, "");

        echo "\n [KEY-FILE] Saving keys into file: " . $this->filePath;

        if (empty($data)) {
            echo "\n [KEY-FILE][WARNING] Empty data set";
            return;
        }

        foreach ($data as $index => $keyRows) {

            if (empty($keyRows)) {
                echo "\n [KEY-FILE][WARNING] Empty data set [" . $index . "]";
                continue;
            }

            foreach ($keyRows as $row) {
                $keys[] = $row[$this->keyField];
            }
        }

        file_put_contents($this->filePath, implode(',', $keys));
    }

    public function describe($dataIndex)
    {
        return false;
    }

    public function getType()
    {
        return 'KeyFile';
    }
}