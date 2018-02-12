<?php

class RawEnvironment implements Environment
{
    public $name;
    public $rawQueries;
    public $putOperation;

    public function __construct($name, $putOperation)
    {
        $this->name         = $name;
        $this->putOperation = $putOperation;
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
        foreach ($data as $index => $rows) {
            foreach ($rows as $row) {
                print_r($row);
            }
        }
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