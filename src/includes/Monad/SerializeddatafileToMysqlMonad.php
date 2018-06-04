<?php

include_once('MysqlToMysqlMonad.php');

class SerializeddatafileToMysqlMonad extends MysqlToMysqlMonad {
    /**
     * @param $data
     * @param SerializedDataFileEnvironment|Environment $sourceEnvironment
     * @param Environment|MysqlEnvironment $targetEnvironment
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

        $targetEnvironment->rawQueries = $sourceEnvironment->getRawQueries();

        return $this->unit($data, $targetEnvironment);
    }
}