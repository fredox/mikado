<?php

include_once('MysqlToMysqlMonad.php');
include_once('Monad.php');

class MysqlToDryrunMonad extends MysqlToMysqlMonad implements Monad
{
    /**
     * @param $data
     * @param Environment $sourceEnvironment
     * @param Environment $targetEnvironment
     * @param array $transformations
     * @return array
     */
    public function bind($data, Environment $sourceEnvironment, Environment $targetEnvironment, $transformations = array())
    {
        $virtualTargetEnvironment = $this->getVirtualTargetEnvironment($sourceEnvironment, $targetEnvironment);

        $data = parent::bind($data, $sourceEnvironment, $virtualTargetEnvironment, $transformations);

        $targetEnvironment->rawQueries = $sourceEnvironment->rawQueries;

        return $data;
    }

    /**
     * @param Environment $sourceEnvironment
     * @param Environment $targetEnvironment
     * @return DryRunEnvironment|KeyFileEnvironment|MysqlEnvironment
     */
    private function getVirtualTargetEnvironment(Environment $sourceEnvironment, Environment $targetEnvironment)
    {
        $dryRunEnvironmentName = $targetEnvironment->getName();

        $config = $this->getConfig();

        if (array_key_exists('targetEnvironment', $config['environments'][$dryRunEnvironmentName])) {
            $virtualTargetEnvironmentName = $config['environments'][$dryRunEnvironmentName]['targetEnvironment'];
            $virtualTargetEnvironmentConfig = $config['environments'][$virtualTargetEnvironmentName];
            $virtualEnvironment = EnvironmentFactory::getEnvironment($virtualTargetEnvironmentConfig);
        } else {
            $virtualEnvironment = $sourceEnvironment;
        }

        return $virtualEnvironment;
    }

    public function executeRawQueriesAtFirst($targetEnvironment)
    {
        // Dry run must not execute raw queries because it will be written in some file.
    }
}