<?php

include_once('Transformation.php');

class DropTransformation implements Transformation
{
    public $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function transform($data, Environment $sourceEnvironment, Environment $targetEnvironment)
    {
        echo " \n [TRANSFORMATION][DROP] Collecting tables tables";

        if (empty($tables)) {
            echo "\n [TRANSFORMATION][DROP] There no tables to put schema";
        }

        foreach ($data as $table=>$rows) {
            echo " \n [TRANSFORMATION][DROP] Adding table [" . $table . "] to delete";
            $rawData['Delete table ' . $table] = 'DROP TABLE IF EXISTS ' . $table;

        }

        $sourceEnvironment->addRawQueries('Drop tables', $rawData);

        return $data;
    }
}