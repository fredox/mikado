<?php

include_once('MysqlToMysqlMonad.php');
include_once('Monad.php');

class MysqlToMysqlMonad implements Monad
{
    public $config;
    public $maxRowsPerInsert = 50;

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

            if (empty($rows)) {
                continue;
            }

            $fieldsDefinition = $targetEnvironment->describe($tableName);

            foreach ($rows as $index => $row) {

                foreach ($fieldsDefinition as $fieldName => $fieldDefinition) {
                    $finalData[$tableName][$index][$fieldName] = $this->getPreparedValue($fieldName, $row, $fieldsDefinition);
                }
            }

        }

        foreach ($finalData as $tableName => $rows) {

            $fields = array_keys($rows[0]);

            $insertedRows = 0;
            $nRows  = count($rows);
            $values = array();

            foreach ($rows as $index => $row) {
                $values[] = "(" . implode($row, ',') . ")";
                $insertedRows++;

                $lastRowInserted = ($nRows == $insertedRows);
                $nextInsertBulk  = ($insertedRows % $this->maxRowsPerInsert) == 0;

                if ($lastRowInserted || $nextInsertBulk) {
                    $query  = $targetEnvironment->operation;
                    $query .= " INTO " . $tableName . " (" . implode($fields, ',') . ") VALUES ";
                    $query .= implode(',', $values);

                    //echo "\n Table: " . $tableName . " Bulk size: " . count($values);
                    $values = array();
                    $sqlData[$tableName][] = $query;
                }
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

        if (($value == 0 OR $value == '0') AND $fieldsDefinition[$field]['type'] == 'int') {
            return 0;
        }

        if (!empty($value)) {
           return $this->wrapNonEmptyValue($value, $fieldsDefinition[$field]);
        }

        if ($fieldsDefinition[$field]['null'] == 'YES') {
            return 'NULL';
        }

        if ($fieldsDefinition[$field]['default'] == "") {
            return '""';
        }

        $value = $fieldsDefinition[$field]['default'];

        return $this->wrapNonEmptyValue($value, $fieldsDefinition[$field]);
    }

    /**
     * @param $value
     * @return string
     */
    public function wrapNonEmptyValue($value, $fieldDefiniton)
    {
        if ($fieldDefiniton['type'] == 'int') {
            return $value;
        }

        if (is_numeric($value) && !$fieldDefiniton['isText']) {
            return $value;
        }

        return '"' . addslashes($value) . '"';
    }

    public function executeRawQueriesAtFirst($targetEnvironment)
    {
        $targetEnvironment->executeRawQueries();
    }
}