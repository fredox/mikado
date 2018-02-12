<?php

class InputHelp {


    public static function getHelp($args, $config=false)
    {
        if (empty($args)) {
            static::showDefaultConfigEnvironments();
            echo "\n\n";
            exit;
        }

        $commandArgs = implode(' ', $args);

        if (preg_match("/^recipe:$/", $commandArgs)) {
            static::showRecipes();
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

        include_once('config/config.php');

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
            echo "\n  - " . $environmentName . ' (type:' . $environment['type'] . ')';
        }
    }

    public static function showConfigEnvironments($configPath)
    {
        $config = array();

        include_once('config/' . $configPath . '/config.php');

        static::showEnvironments($config);
    }

    public static function showConfigGroups($configPath)
    {
        $config = array();

        include_once('config/' . $configPath . '/config.php');

        if (!$config['groups']) {
            echo "\n [ERROR] groups key it is not set on default config.\n\n";
            exit;
        }

        echo "\n [HELP] Available Groups:\n";

        foreach ($config['groups'] as $groupName => $tables) {
            echo "\n  - " . $groupName . ' (with tables:' . implode(',', $tables) . ')';
        }
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

    public static function showRecipes()
    {
        $files = array_filter(glob('recipes/*'), 'is_file');
        echo "\n [HELP] Available recipes:\n";

        foreach ($files as $file) {
            $recipe = substr($file, 8);
            echo "\n  - " . $recipe;
        }

        echo "\n\n";
    }


}