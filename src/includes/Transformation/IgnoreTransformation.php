<?php

include_once('Transformation.php');

class IgnoreTransformation implements Transformation
{
    public function transform($data, Environment $sourceEnvironment, Environment $targetEnvironment)
    {
        echo " \n [TRANSFORMATION][REPLACE] Changing to replace mode";
        $targetEnvironment->operation = 'INSERT IGNORE';

        return $data;
    }
}