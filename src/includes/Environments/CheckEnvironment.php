<?php

class CheckEnvironment implements Environment
{
    public $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function get($queries, $key)
    {
        echo "\n[ERROR] Environment of type check must be target";
        exit;

    }

    public function put($data)
    {
        echo "\n\n [Check][" . $this->name . "] Initializing checks...";

        foreach ($data as $index => $rows) {
            if (empty($rows)) {
                $this->displayOk($index);
                continue;
            }
            foreach ($rows as $row) {
                if ($this->checkRowStructure($row)) {
                    $this->displayKo($index, $row);
                } else {
                    echo "\n [ERROR][Check] The resultant row does not have proper format.\n\n";
                    exit;
                }
            }
        }
    }

    private function displayOk($description)
    {
        echo "\n [" . $description . "] ";
        echo greenFormat(" OK ");
    }

    private function displayKo($description, $row)
    {
        echo "\n [" . $description . "]";
        echo " EXPECTED: " . whiteFormat($row['expected_value']);
        echo " ACTUAL: " . whiteFormat($row['real_value']) . " ";
        echo redFormat(" FAIL ");
        echo "\n [" . $description . "] DESCRIPTION: " . $row['description'];
        echo "\n  - - - ";
    }

    private function checkRowStructure($row)
    {
        if (!is_array($row)) {
            return false;
        }

        $mandatoryFields = array('real_value', 'expected_value', 'description');

        foreach ($mandatoryFields as $field) {
            if (!in_array($field, array_keys($row))) {
                return false;
            }
        }

        return true;
    }

    public function describe($dataIndex)
    {
        return false;
    }

    public function getType()
    {
        return 'Check';
    }

}