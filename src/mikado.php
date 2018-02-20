<?php

error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

include_once('includes/InputHelp.php');

//remove script name from params
array_shift($argv);

InputHelp::getHelp($argv);

Mikado::start($argv);

class Mikado {

    public static function start($args)
    {
        if (!preg_match("/^recipe:(.*)$/", $args[0], $matches)) {
            static::run($args);
        } else {
            $recipe = $matches[1];
            array_shift($args);
            static::bakeRecipe($args, $recipe);
        }
    }

    public static function checkConfig($config, $queries, $configPath)
    {
        if (empty($config)) {
            echo "\n [ERROR] Config file is empty: " . $configPath . "/config.php\n";
            exit;
        }

        foreach ($config['groups-to-import'] as $group) {

            if (!array_key_exists($group, $config['groups'])) {
                echo "\n [ERROR] " . $group . " does not exist as index in groups config\n";
                exit;
            }

            foreach ($config['groups'][$group] as $queryIndex) {
                if (!array_key_exists($queryIndex, $queries)) {
                    echo "\n [ERROR] " . $queryIndex . " does not exist as index in query files\n";
                    exit;
                }
            }
        }
    }

    public static function run($args)
    {
        list($configPath, $customGroupTag, $params) = static::getConfigPath($args);

        echo "\n[CONFIG PATH] " . $configPath;

        $config = $queries = array();

        require($configPath . '/config.php');

        $config = static::getGroupsToImport($customGroupTag, $config);

        require($config['queries-file']);

        static::checkConfig($config, $queries, $configPath);

        echo "\n[QUERIES FILE] " . $config['queries-file'];

        require_once('hooks.php');
        require_once('includes/transfer.php');
        require_once('includes/Input.php');
        require_once('includes/format.php');

        $config['all-queries-indexes'] = array_keys($queries);

        $config = Input::get($config, $params);

        $sourceEnvironment = $config['execution']['source_environment'];
        $targetEnvironment = $config['execution']['target_environment'];
        $key = $config['execution']['key'];

        echo "\n\n[TRANSFER BEGIN]";
        echo "\n\n";

        $keys = static::getKeys($key, $config);

        foreach ($keys as $key) {
            transfer($sourceEnvironment, $targetEnvironment, $queries, $key, $config);
            echo "\n\n";
        }

        try {
            hooks($sourceEnvironment, $targetEnvironment, $queries, $key, $config);
        } Catch (Exception $e) {
            exit("\n[ERROR][HOOKS] Error: " . $e->getMessage());
        }

        echo "\n\n[TRANSFER END]\n\n";
    }

    public static function getConfigPath($args)
    {
        $customGroupTags = false;

        if (preg_match('/^cfg:(.*)$/', $args[0], $matches)) {

            $newArgs = array();
            $path    = $matches[1];

            if (preg_match('/^([^:]+):([^:]+)$/', $matches[1],$matchesGroup)) {
                $customGroupTags = array('type' => 'custom-group', 'value' => $matchesGroup[2]);
                $path = $matchesGroup[1];
            }

            if (preg_match('/^([^:]+)::(.+)$/', $matches[1],$matchesGroup)) {
                $customGroupTags = array('type' => 'custom-tmp-group', 'value' => $matchesGroup[2]);
                $path = $matchesGroup[1];
            }

            $configPath        = 'config/' . $path;
            $configFileAndPath = $configPath.'/config.php';

            if (!is_file($configFileAndPath)) {
                exit("\n[ERROR] Specified config file (" . $configFileAndPath . ") does NOT exists!");
            }

            // Remove cfg param from array params.
            foreach ($args as $key=>$arg) {
                if ($key != 0) {
                    $newArgs[] = $arg;
                }
            }

            return array($configPath, $customGroupTags, $newArgs);
        }

        return array('config', $customGroupTags, $args);
    }

    public static function getGroupsToImport($customGroupTags, $config)
    {
        if ($customGroupTags === false) {
            return $config;
        }

        if ($customGroupTags['type'] == 'custom-group') {
            echo " \n[INFO] Selecting custom group";
            $config['groups-to-import'] = explode(',', $customGroupTags['value']);
            return $config;
        }

        if ($customGroupTags['type'] == 'custom-tmp-group') {
            echo " \n[INFO] Creating custom group on the fly [" . $customGroupTags['value'] . "]";
            $tmpGroupName = 'tmp-' . str_replace(',', '-', $customGroupTags['value']);
            $config['groups'][$tmpGroupName] = explode(',', $customGroupTags['value']);
            $config['groups-to-import'] = array($tmpGroupName);
        }

        return $config;
    }

    public static function bakeRecipe($args, $recipe)
    {
        $filePath = 'recipes/' . $recipe;

        if (!is_file($filePath)) {
                exit("\n[ERROR] Specified recipe file (" . $recipe . ") does NOT exists!\n\n");
        }

        $steps  = array();
        $handle = fopen($filePath, "r");

        echo "\n [INFO] Baking a delicious recipe...\n";

        while (($line = fgets($handle)) !== false) {
            $line = trim($line);

            if (strpos($line, '--') === 0)
                continue;

            if (empty($line))
                continue;

            $stepParams = $line;
            foreach ($args as $index => $arg) {
                $stepParams = str_replace('$' . $index, $arg, $stepParams);
            }

            $steps[] = explode(' ', $stepParams);
        }

        fclose($handle);

        $nSteps = count($steps);
        $currentStep = 1;

        try {

            foreach ($steps as $stepArguments) {
                echo "\n - Step " . $currentStep . " of " . $nSteps . "  [args] " . implode(" ", $stepArguments);
                echo "\n =========================================================================================\n";
                static::start($stepArguments);
                $currentStep++;
            }

        } Catch (Exception $e) {
            echo "[UNEXPECTED ERROR] " . $e->getMessage();
            exit;
        }

        echo "\n[INFO] RECIPE END\n\n";

    }

    public static function getKeys($key, $config)
    {
        if (!array_key_exists('compact-mode', $config)) {
            echo "\n[ERROR] You must define compact-mode in config.\n\n";
            exit;
        }

        if ($config['compact-mode'] === false) {
            $key = explode(',', $key);
        } else {
            $key = array($key);
        }

        return $key;
    }
}


