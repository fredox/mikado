<?php

class RawEnvironment implements Environment
{
    public $name;
    public $rawQueries;
    public $putOperation;
    public $file = false;

    public function __construct($name, $putOperation, $file)
    {
        $this->name         = $name;
        $this->putOperation = $putOperation;
        $this->file         = $file;
    }

    public function getName()
    {
        return $this->name;
    }

    public function addRawQueries($descriptor, $queries)
    {
        if (!empty($queries)) {
            echo " \n [RAW] setting " . count($queries) . " queries in Raw queries";
            $this->rawQueries[$descriptor] = $queries;
        }
    }

    public function get($queries, $key)
    {
        $finalQueries = array();

        foreach ($queries as $index => $query) {
            $finalQueries[$index] = str_replace('@KEY', $key, $query);
        }

        $this->addRawQueries('RAW', $finalQueries);

        return array();
    }

    public function put($data)
    {
        $op = $this->putOperation;
        $this->$op($data);
    }

    public function printRaw($data)
    {
        if ($this->file) {
            $content = print_r($data, TRUE);
            file_put_contents(Input::INPUT_OUTPUT_FOLDER . '/' . $this->file, $content);
        } else {
            print_r($data);
        }
        foreach ($data as $index => $rows) {
            foreach ($rows as $row) {
                print_r($row);
            }
        }
    }

    public function saveJson($data)
    {
        if (count($data) == 0) {
            return;
        }

        $jsonData = array();
        foreach ($data as $field => $dataRows) {
            if (empty($dataRows)) {
                echo "\n [RAW] Warning, empty data set for [" . $field . "]";
                continue;
            }

            foreach ($dataRows as $dataRow) {
                if (!isset($dataRow[0]) && count($dataRow) == 1) {
                    $jsonData[$field] = self::dataToHash($dataRow);
                } else {
                    $jsonData[$field][] = self::dataToHash($dataRow);
                }

            }
        }

        $jsonDataResult = json_encode($jsonData);
        $file = ($this->file) ? $this->file : 'raw-result.json';

        echo "\n [RAW] Saving json in FILE: " . Input::INPUT_OUTPUT_FOLDER . '/' . $file;
        file_put_contents(Input::INPUT_OUTPUT_FOLDER . '/' . $file, $jsonDataResult);
    }

    private function dataToHash($data)
    {
        $result = array();

        if (count($data) == 0) {
            return array();
        }

        foreach ($data as $field => $value) {
            if (strpos($field, '.') === false) {
                $result[$field] = $value;
            } else {
                $fieldParts = explode('.', $field);
                $head = array_shift($fieldParts);
                $newKey = implode('.',$fieldParts);

                if (!array_key_exists($head, $result)) {
                    $result[$head] = array();
                }

                $result[$head] = array_merge_recursive(self::dataToHash(array($newKey => $value)), $result[$head]);
            }
        }

        return $result;
    }

    public function describe($dataIndex)
    {
        return false;
    }

    public function getType()
    {
        return 'Raw';
    }

}