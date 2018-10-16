<?php
class Input
{
    const INPUT_OUTPUT_FOLDER = 'io';

    public static function get($config, $params)
    {
        if (!array_key_exists('environments', $config)) {
            static::inputError('There is no environments defined. Look at your config file.', $config);
        }

        if (count($params) < 3) {
            static::inputError('Invalid input parameters', $config);
        }

        // SOURCE ENVIRONMENT
        list($params, $environment) = static::getEnvironment($params, $config);
        $config['execution']['source_environment'] = $environment;
        echo "\n[SOURCE ENV] " . $environment;

        // TRANSFORMATIONS
        if (static::optionalParamNext($params)) {
            list($params, $config['execution']['transformations']) = static::getTransformations($params);
        }

        // TARGET ENVIRONMENT
        list($params, $environment) = static::getEnvironment($params, $config);
        $config['execution']['target_environment'] = $environment;
        echo "\n[TARGET ENV] " . $environment;


        // KEY
        list ($params, $config['execution']['key']) = static::getKey($params);


        // HOOKS
        if (static::optionalParamNext($params)) {
            list($params, $config['execution']['hooks']) = static::getHooks($params);
        }

        echo "\n[GROUPS TO IMPORT] " . implode(',', $config['groups-to-import']);

        $config = static::setNamesToEnvironments($config);

        return $config;

    }

    public static function setNamesToEnvironments($config)
    {
        foreach ($config['environments'] as $name => $environment) {
            $config['environments'][$name]['name'] = $name;
        }

        return $config;
    }

    public static function getEnvironment($params, $config)
    {
        $environment = array_shift($params);
        $availableEnvironments = array_keys($config['environments']);

        if (!in_array($environment, $availableEnvironments)) {
            static::inputError('Unknown Environment [' . $environment . ']', $config);
        }

        return array($params, $environment);
    }

    public static function optionalParamNext($params)
    {
        if (empty($params)) {
            return false;
        }

        return (strpos($params[0], '-') === 0);
    }

    public static function getTransformations($params)
    {
        $param = array_shift($params);
        $transformations = explode(',', substr($param, 1));

        return array($params, $transformations);
    }

    public static function getHooks($params)
    {
        $rawHooks = array_shift($params);
        $rawHooks = substr($rawHooks, 1);
        $hooksWithParams = array();
        $hooks = explode('.', $rawHooks);


        foreach ($hooks as $hook) {
            $hasParams = strpos($hook, ':');
            if ($hasParams !== false) {
                list($hookName, $rawParams) = explode(':', $hook);
                $hooksWithParams[$hookName] = explode(',', $rawParams);
            } else {
                $hooksWithParams[$hook] = array();
            }
        }

        return array($params, $hooksWithParams);
    }

    public static function inputError($msg, $config, $verbose = false)
    {
        echo "\n\n[ERROR][INPUT] " . $msg;
        echo "\n [USAGE - I] php mikado.php [SOURCE] [DEST] [KEYS]";
        echo "\n [USAGE - II] php mikado.php cfg:[CUSTOM_CONFIG] [SOURCE] [DEST] [KEYS]";
        echo "\n [USAGE - III] php mikado.php cfg:[CUSTOM_CONFIG]:[GROUP] [SOURCE] [DEST] [KEYS]";
        echo "\n [USAGE - IV] php mikado.php cfg:[CUSTOM_CONFIG]::[QUERY_INDEX] [SOURCE] [DEST] [KEYS]";
        echo "\n\n\n";
        if ($verbose) {
            print_r($config);
            print_r($verbose);
        }
        exit(0);
    }

    public static function getKey($params)
    {
        $key = array_shift($params);

        if ((strpos($key, '.') !== false) && (is_file(static::INPUT_OUTPUT_FOLDER .'/' . $key))) {
            $key = file_get_contents(static::INPUT_OUTPUT_FOLDER .'/' . $key);
            $key = trim($key);
        }

        return array($params, $key);
    }

}
