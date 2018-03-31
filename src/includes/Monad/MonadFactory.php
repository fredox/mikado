<?php

include_once('idMonad.php');

require_once('MysqlToDryrunMonad.php');
require_once('MysqlToMysqlMonad.php');
require_once('DryrunToMysqlMonad.php');
require_once('RawToDryrunMonad.php');
require_once('RawToMysqlMonad.php');
require_once('SerializeddatafileToDryrunMonad.php');
require_once('SerializeddatafileToMysqlMonad.php');

class MonadFactory
{
    public static function getMonad(Environment $sourceEnvironment, Environment $targetEnvironment, $config)
    {
        $sourceEnvironmentType = ucfirst(strtolower($sourceEnvironment->getType()));
        $targetEnvironmentType = ucfirst(strtolower($targetEnvironment->getType()));

        $monadNameClass = $sourceEnvironmentType . 'To' . $targetEnvironmentType . 'Monad';

        if (!class_exists($monadNameClass)) {
            echo "\n [INFO] Monad Adapter used: idMonad (Virtual: " . $monadNameClass . ")";
            return new idMonad($config);
        }

        echo "\n [INFO] Monad Adapter used: " . $monadNameClass;

        return new $monadNameClass($config);
    }
}