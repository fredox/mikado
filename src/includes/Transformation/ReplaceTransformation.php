<?php

include_once('Transformation.php');

class ReplaceTransformation implements Transformation
{
    public function transform($data, Environment $sourceEnvironment, Environment $targetEnvironment)
    {
        echo " \n [TRANSFORMATION][REPLACE] Changing to replace mode";
        $targetEnvironment->operation = 'REPLACE';

        return $data;
    }
}