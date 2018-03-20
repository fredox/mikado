<?php

include_once('Environments/EnvironmentFactory.php');

class Analyze extends Health {

    /** @var MysqlEnvironment */
    public static $environment;

    public static function checkAnalyze($analyzeParam, $params)
    {
        if ($analyzeParam != '-analyze') {
            return;
        }

        self::showHelpIfNeeded($params);
        self::checkConfigFolder($params);
        self::loadConfig();
        self::loadQueries();
        self::loadEnvironment($params);
        self::loadTablesInformation();

        self::endCheck();
    }

    public static function endCheck()
    {
        echo "\n [ANALYZE] end\n\n";
        exit;
    }

    public static function loadEnvironment($params)
    {
        $environmentName = $params[2];

        if (array_key_exists($environmentName, self::$config['environments'])) {
            echo "\n [ANALYZE] Environment (". $environmentName .") exists in Config";
        } else {
            echo "\n [ERROR][ANALYZE] Environment (". $environmentName .") does NOT exists in config\n\n";
            die;
        }

        self::$config['environments'][$environmentName]['name'] = $environmentName;

        /** @var MysqlEnvironment environment */
        self::$environment = EnvironmentFactory::getEnvironment(self::$config['environments'][$environmentName]);

        if (!self::$environment instanceof MysqlEnvironment) {
            echo "\n [ANALYZE][WARNING] Environment is not analyzable because it is not an instance of MysqlEnv\n\n";
        }
    }

    public static function showHelpIfNeeded($params)
    {
        if (empty($params) || count($params) < 3) {
            echo "\n params: -analyze [config] [env]";
            InputHelp::showConfigFolders();
        }
    }

    public static function loadTablesInformation()
    {
        $tablesInformation = self::$environment->query('SHOW TABLES', true);
    }
}