<?php

include_once('Transformation.php');

class SchemaTransformation implements Transformation
{
    public $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function transform($data, Environment $sourceEnvironment, Environment $targetEnvironment)
    {
        echo " \n [TRANSFORMATION][SCHEMA] Adding create tables";

        $tablesInTargetEnvironment = $rawData = array();

        $tables = $targetEnvironment->query('SHOW TABLES', true);

        if (empty($tables)) {
            echo "\n [TRANSFORMATION][SCHEMA] There no tables in target environment";
        }

        foreach ($tables as $tableInTargetEnvironment) {
            $tablesInTargetEnvironment[] = $tableInTargetEnvironment['Tables_in_' . $targetEnvironment->dbname];
        }

        foreach ($data as $table=>$rows) {
            $targetEnvironmentType = $this->getTargetEnvironmentRealType();

            if (!in_array($table, $tablesInTargetEnvironment) || ($targetEnvironmentType == 'dryrun')) {
                echo " \n [TRANSFORMATION][SCHEMA] Adding create table for [" . $table . "]";
                $query = 'SHOW CREATE TABLE ' . $table;
                $createTable = $sourceEnvironment->query($query, true);

                if ($createTable === false) {
                    echo " \n [TRANSFORMATION][SCHEMA] Source environment table: " . $table . " does not exists";
                    continue;
                }

                $createTableStatement = $createTable[0]['Create Table'] . ";";
                $createTableStatement = str_replace(array("\n","\r"), " ", $createTableStatement);


                $rawData['Create table ' . $table] = $createTableStatement;
            }
        }

        $sourceEnvironment->addRawQueries('Creation tables', $rawData);

        return $data;
    }

    public function getTargetEnvironmentRealType()
    {
        $targetEnvirnmentName = $this->config['execution']['target_environment'];

        return $this->config['environments'][$targetEnvirnmentName]['type'];
    }
}