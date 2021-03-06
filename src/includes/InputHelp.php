<?php

include_once('format.php');

class InputHelp {


    public static function getHelp($args, $config=false)
    {
        if (empty($args)) {
            static::showDefaultConfigEnvironments();
            echo "\n\n";
            exit;
        }

        $commandArgs = implode(' ', $args);

        if (preg_match("/^(recipe|r):$/", $commandArgs)) {
            static::showRecipes();
            echo "\n\n";
            exit;
        }

        if (preg_match("/^(recipe|r): -v$/", $commandArgs)) {
            static::showRecipes($verbose=true);
            echo "\n\n";
            exit;
        }

        if (preg_match("/^cfg:$/", $commandArgs)) {
            static::showConfigFolders();
            echo "\n\n";
            exit;
        }

        if (preg_match("/^cfg:([^\s:]+) ?$/", $commandArgs, $matches)) {
            static::showConfigEnvironments($matches[1]);
            echo "\n\n";
            exit;
        }

        if (preg_match("/^cfg:([^\s:]+): ?$/", $commandArgs, $matches)) {
            static::showConfigGroups($matches[1]);
            echo "\n\n";
            exit;
        }

        if (preg_match("/^cfg:([^\s:]+):: ?$/", $commandArgs, $matches)) {
            static::showQueries($matches[1]);
            echo "\n\n";
            exit;
        }

        if (preg_match("/^cfg:([^\s:]+)::([^\s]+) ?$/", $commandArgs, $matches)) {
            static::showConfigEnvironments($matches[1]);
            echo "\n\n";
            exit;
        }

        if (preg_match("/^cfg:([^\s]+):([^\s]+) ?$/", $commandArgs, $matches)) {
            static::showConfigEnvironments($matches[1]);
            echo "\n\n";
            exit;
        }
    }

    public static function showConfigFolders()
    {
        $dirs = array_filter(glob('config/*'), 'is_dir');
        echo "\n [HELP] Available Configs:\n";

        foreach ($dirs as $dir) {
            $configDir = substr($dir, 7);
            echo "\n  - " . $configDir;
        }

        echo "\n\n";
    }

    public static function showDefaultConfigEnvironments()
    {
        $config = array();

        if (!is_file('config/default/config.php')) {
            echo "\n [ERROR] There is no default config to load.\n\n";
            exit;
        }

        include_once('config/default/config.php');

        static::showEnvironments($config);
    }

    public static function showEnvironments($config)
    {
        if (!$config['environments']) {
            echo "\n [ERROR] Environments key it is not set on default config.\n\n";
            exit;
        }

        echo "\n [HELP] Available Environments:\n";

        foreach ($config['environments'] as $environmentName => $environment) {
            if (!array_key_exists('type', $environment)) {
                echo "\n [ERROR] Environment type is not set for: [". $environmentName ."]\n\n";
                exit;
            }
            echo "\n  - " . $environmentName . ' (type:' . $environment['type'] . ')';

            if (array_key_exists('comments', $environment)) {
                echo " " . $environment['comments'];
            }
        }
    }

    public static function showConfigEnvironments($configPath)
    {
        $config = array();

        if (!is_dir('config/' . $configPath)) {
            echo "\n [ERROR] config " . $configPath . " does NOT exists.\n\n";
            self::showConfigFolders();
            die;
        }

        include_once('config/' . $configPath . '/config.php');

        static::showEnvironments($config);
    }

    public static function showConfigGroups($configPath)
    {
        $config = array();

        include_once('config/' . $configPath . '/config.php');

        if ((!array_key_exists('groups', $config)) || !$config['groups']) {
            echo "\n [ERROR] groups key it is not set on config [". $configPath ."]\n\n";
            exit;
        }

        echo "\n [HELP] Available Groups:\n";

        foreach ($config['groups'] as $groupName => $tables) {
            $defaultTxt = (in_array($groupName, $config['groups-to-import'])) ? ' [DEFAULT]' : '';
            echo "\n  - " . $groupName . ' (with tables -> ' . implode(',', $tables) . ')' . ' ' . $defaultTxt;
        }

        echo "\n";
    }

    public static function showQueries($configPath)
    {
        $queries = array();

        include_once('config/' . $configPath . '/queries.php');

        if (empty($queries)) {
            echo "\n [ERROR] Queries are empty\n\n";
            exit;
        }

        echo "\n [HELP] Available Queries:\n";

        foreach ($queries as $queryName => $query) {
            echo "\n  - " . $queryName;
        }
    }

    public static function showRecipes($verbose=false)
    {
        $files = array_filter(array_merge(glob('recipes/*/*'),glob('recipes/*')), 'is_file');
        echo "\n [HELP] Available recipes:\n";

        foreach ($files as $file) {
            $recipe = substr($file, 8);
            $recipeContent = file($file);

            if ($verbose) {
                squaredText($recipe, $indented=1);
                foreach ($recipeContent as $recipeContentLine) {
                    echo "\t\t" . $recipeContentLine;
                }
                echo "\n\n\n";
            } else {
                echo "\n  - " . $recipe . ": " . trim($recipeContent[0], "\n-");
            }

        }

        echo "\n\n";
    }


}