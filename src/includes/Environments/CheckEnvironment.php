<?php

class CheckEnvironment implements Environment
{
    public $name;
    public $strict;
    public static $debug = false;
    public $file = false;

    public function __construct($name, $strict=false, $file=false)
    {
        $this->name = $name;
        $this->strict = $strict;
        $this->file   = $file;
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
                    if ($this->strict) {
                        echo "\n\n [Check] Exiting check. This check is configured as strict and stops at the first fail\n\n";
                        exit(0);
                    }
                } else {
                    echo "\n [ERROR][Check] The resultant row does not have proper format.\n\n";
                    exit;
                }
            }
        }

        if ($this->file) {
            echo "\n [Check] Result check must be saved at: " . Input::INPUT_OUTPUT_FOLDER . '/' . $this->file['path'];
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
        echo "\n";

        if (self::$debug) {
            $msg = $row['description'] . " -> " . $row['expected_value'] . ' - ' . $row['real_value'] . "\n";
            file_put_contents('result-check-debug.txt', $msg, FILE_APPEND);
        }

        if ($this->file) {
            if (!file_exists(Input::INPUT_OUTPUT_FOLDER . '/' . $this->file['path'])) {
                file_put_contents(Input::INPUT_OUTPUT_FOLDER . '/' . $this->file['path'], implode(',',$this->file['fields']));
            }

            $valuesToSave = array();

            foreach ($this->file['fields'] as $fieldToSave) {
                $valuesToSave[] = $row[$fieldToSave];
            }
            file_put_contents(Input::INPUT_OUTPUT_FOLDER . '/' . $this->file['path'], "\n" . implode(',', $valuesToSave), FILE_APPEND);
        }
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