<?php

include_once('ReplaceTransformation.php');
include_once('LazyTransformation.php');
include_once('SchemaTransformation.php');
include_once('KeysTransformation.php');
include_once('DropTransformation.php');
include_once('IgnoreTransformation.php');

class TransformationFactory {
    public static function getTransformation($transformationId, $config)
    {
        $transformationClassName = ucfirst(strtolower($transformationId)) . 'Transformation';

        if (!class_exists($transformationClassName)) {
            echo "\n [ERROR][TRANSFORMATION] Class " . $transformationClassName . " does not exists\n\n";
            exit;
        }

        return new $transformationClassName($config);
    }
}