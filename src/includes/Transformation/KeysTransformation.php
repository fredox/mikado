<?php

include_once('Transformation.php');

class KeysTransformation implements Transformation
{
    public function transform($data, Environment $sourceEnvironment, Environment $targetEnvironment)
    {
        echo " \n [TRANSFORMATION][Keys] Looking for primary keys in data rows.";

        $finalData = array();
        $keysField = 'value';

        if ($targetEnvironment instanceof KeyFileEnvironment) {
            $keysField = $targetEnvironment->keyField;
        }

        foreach ($data as $table=>$rows) {
            $primaryKeyField = $this->getPrimaryKeyOfTable($table, $sourceEnvironment);

            if ($primaryKeyField === false) {
                echo "\n [TRANSFORMATION][Keys] No primary key found for table: " . $table . "\n\n";
                exit;
            }

            foreach ($rows as $index => $row) {
                $finalData[$table][$index][$keysField] = $row[$primaryKeyField];
            }
        }

        return $finalData;
    }

    private function getPrimaryKeyOfTable($table, Environment $targetEnvironment)
    {
        $fieldsDescription = $targetEnvironment->describe($table);

        foreach ($fieldsDescription as $fieldName => $fieldDescription) {
            if ($fieldDescription['key'] == 'PRI') {
                return $fieldName;
            }
        }

        return false;
    }
}