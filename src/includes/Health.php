<?php

Class Health {

    public static $configDir;
    public static $queries;
    public static $config;

    public static function checkHealth($healthParam, $params)
    {
        if ($healthParam != '-health') {
            return;
        }

        self::checkConfigFolder($params);
        self::loadQueries();
        self::loadConfig();
        self::checkAllGroupsContainsValidQueries();

        self::endCheck();
    }

    public static function endCheck()
    {
        echo "\n [HEALTH] Check end\n\n";
        exit;
    }

    public static function checkConfigFolder($params)
    {
        if (!array_key_exists(1, $params)) {
            echo " \n [ERROR][HEALTH] Health param must be preceded by a config folder\n\n";
            InputHelp::showConfigFolders();
            exit;
        }

        if (is_dir('config/' . $params[1])) {
            self::$configDir = 'config/' . $params[1];
            echo " \n [OK][HEALTH] Directory " . $params[1] . " exists";
        } else {
            echo " \n [FAIL][HEALTH] Directory " . $params[1] . " does not exists\n\n";
            exit;
        }
    }

    public static function loadQueries()
    {
        if (!is_file(self::$configDir . "/queries.php")) {
            echo " \n [ERROR][HEALTH] queries file does not exists in " . self::$configDir . "\n\n";
            exit;
        }

        echo " \n [OK][HEALTH] Queries file exists";

        $queries = array();

        include_once(self::$configDir . "/queries.php");

        self::$queries = $queries;
    }

    public static function loadConfig()
    {
        if (!is_file(self::$configDir . "/config.php")) {
            echo " \n [ERROR][HEALTH] queries file does not exists in " . self::$configDir . "\n\n";
            exit;
        }

        echo " \n [OK][HEALTH] Queries file exists";

        $config = array();

        include_once(self::$configDir . "/config.php");

        self::$config = $config;
    }

    public static function checkAllGroupsContainsValidQueries()
    {
        foreach (self::$config['groups'] as $groupName => $queryIndexes) {
            echo "\n [INFO][HEALTH] Checking group [" . $groupName . "]";

            foreach ($queryIndexes as $queryIndex) {
                if (!in_array($queryIndex, array_keys(self::$queries))) {
                    echo " \n [ERROR][HEALTH] query index [" . $queryIndex . "] is not present in queries file.";
                    exit;
                }
            }
        }
    }
}