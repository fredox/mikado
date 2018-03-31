<?php

require_once('Environments/EnvironmentFactory.php');
require_once ('Transformation/TransformationFactory.php');
require_once('Monad/MonadFactory.php');

function transfer($sourceEnvironment, $targetEnvironment, $queries, $key, $config)
{
    print_key($key);

    $transformations = array();

    $sourceEnvironment = EnvironmentFactory::getEnvironment($config['environments'][$sourceEnvironment]);
    $targetEnvironment = EnvironmentFactory::getEnvironment($config['environments'][$targetEnvironment]);

    $queriesIndexes       = array_keys($queries);
    $queriesToProcess     = array();
    $groupsIndexToProcess = $config['groups-to-import'];


    foreach ($groupsIndexToProcess as $groupIndex){
        foreach ($config['groups'][$groupIndex] as $queryIndexToProcess) {
            if (in_array($queryIndexToProcess, $queriesIndexes)) {
                $queriesToProcess[$queryIndexToProcess] = $queries[$queryIndexToProcess];
            }
        }
    }

    echo "\n [i] Collecting data from [" . $sourceEnvironment->name . "]";
    $data = $sourceEnvironment->get($queriesToProcess, $key);

    if (array_key_exists('transformations', $config['execution'])) {
        foreach ($config['execution']['transformations'] as $transformationId) {
            $transformations[] = TransformationFactory::getTransformation($transformationId, $config);
        }
    }

    /** @var Monad $monad */
    $monad = MonadFactory::getMonad($sourceEnvironment, $targetEnvironment, $config);

    $data = $monad->bind($data, $sourceEnvironment, $targetEnvironment, $transformations);

    echo "\n [i] Transfering data to [" . $targetEnvironment->name . "]";
    $targetEnvironment->put($data);
}

function print_key($key)
{
    if (strlen($key) > 70) {
        $key = substr($key, 0, 66) . '...';
    }

    $text    = 'KEY: ' . $key;
    $length  = strlen($text);
    $topDown = '+--' . str_repeat('-', $length) . '--+';
    $middle  = '|  ' . $text . '  |';

    echo "\t\t" . $topDown . "\n\t\t" . $middle . "\n\t\t" . $topDown . "\n\n";
}
