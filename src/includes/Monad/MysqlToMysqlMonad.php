<?php

include_once('MysqlToMysqlMonad.php');
include_once('Monad.php');

class MysqlToMysqlMonad implements Monad
{
    public $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param $data
     * @param Environment $targetEnvironment
     * @return array
     */
    public function unit($data, Environment $targetEnvironment)
    {
        $finalData = array();
        $sqlData   = array();

        $this->executeRawQueriesAtFirst($targetEnvironment);

        foreach ($data as $tableName => $rows) {

            $fieldsDefinition = $targetEnvironment->describe($tableName);

            foreach ($rows as $index => $row) {

                foreach ($fieldsDefinition as $fieldName => $fieldDefinition) {
                    $finalData[$tableName][$index][$fieldName] = $this->getPreparedValue($fieldName, $row, $fieldsDefinition);
                }
            }

        }

        foreach ($finalData as $tableName => $rows) {

            $fields = array_keys($rows[0]);

            foreach ($rows as $row) {
                $query  = $targetEnvironment->operation;
                $query .= " INTO " . $tableName . " (" . implode($fields, ',');
                $query .= ") VALUES (" . implode($row, ',') . ");";

                $sqlData[$tableName][] = $query;
            }

        }

        return $sqlData;
    }


    /**
     * @param $data
     * @param Environment $sourceEnvironment
     * @param Environment $targetEnvironment
     * @param array $transformations
     * @return array
     */
    public function bind($data, Environment $sourceEnvironment, Environment $targetEnvironment, $transformations = array())
    {
        if (!empty($transformations)) {
            foreach ($transformations as $transformation) {
                echo "\n [TRANSFORMATIONS] Applying Transformation " . get_class($transformation);
                $data = $transformation->transform($data, $sourceEnvironment, $targetEnvironment);
            }
        }

        $targetEnvironment->rawQueries = $sourceEnvironment->rawQueries;

        return $this->unit($data, $targetEnvironment);
    }

    /**
     * @param $field
     * @param $row
     * @param $fieldsDefinition
     * @return string
     */
    public function getPreparedValue($field, $row, $fieldsDefinition)
    {
        $value = array_key_exists($field, $row) ? $row[$field] : null;


        if (!empty($value)) {
           return $this->wrapNonEmptyValue($value);
        }

        if ($fieldsDefinition[$field]['null'] == 'YES') {
            return 'NULL';
        }

        if ($fieldsDefinition[$field]['default'] == "") {
            return '""';
        }

        $value = $fieldsDefinition[$field]['default'];

        return $value;
    }

    /**
     * @param $value
     * @return string
     */
    public function wrapNonEmptyValue($value)
    {
        if (is_numeric($value)) {
            return $value;
        }

        return '"' . addslashes($value) . '"';
    }

    public function executeRawQueriesAtFirst($targetEnvironment)
    {
        $targetEnvironment->executeRawQueries();
    }
}