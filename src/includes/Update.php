<?php


class Update {

    const MIKADO_INI_FILE = 'mikado.ini';

    public static function checkUpdate($arg)
    {
        if ($arg != '-update') {
            return;
        }

        echo " \n [UPDATE] Updating configs...";

        self::checkIniFileExists();
        $configs = self::parseIniFile();

        $replaceVarsInIni = self::getVars($configs);
        $configs = self::replaceVarsInIni($configs, $replaceVarsInIni);

        $configFolders = self::getConfigFolders($configs);
        self::createConfigFolders($configFolders);
        self::createConfigFiles($configFolders);
        self::createQueryFiles($configFolders);

        $recipes = self::getRecipes($configs);
        self::createRecipesFolder();
        self::createRecipes($recipes);


        echo " \n [UPDATE] Updated configs end. \n\n";
        exit;
    }

    public static function getVars($configs)
    {
        $replaceVars = array();

        echo "\n [update] Reading vars to replace in ini file...";

        foreach ($configs as $section => $sectionContent) {
            if (preg_match("/vars:(.*)/", $section, $matches)) {
                foreach ($sectionContent as $var => $replacement) {
                    $replaceVars[$var] = $replacement;
                }
            }
        }

        return $replaceVars;
    }

    public static function replaceVarsInIni($configs, $replaceVarsInIni)
    {
        if (empty($replaceVarsInIni)) {
            echo "\n [update] No vars found to replace in ini file.";
            return $configs;
        }

        foreach ($configs as $section => $configContent) {
            foreach ($configContent as $config => $value) {
                foreach ($replaceVarsInIni as $var => $replacement) {
                    $value = str_replace('[' . $var . ']', $replacement, $value);
                    $configs[$section][$config] = $value;
                }
            }
        }

        return $configs;
    }

    public static function checkIniFileExists()
    {
        if (!is_file(self::MIKADO_INI_FILE)) {
            echo " \n [ERROR][UPDATE] Mikado ini file not found.";
            exit;
        }
    }

    public static function parseIniFile()
    {
        return parse_ini_file(self::MIKADO_INI_FILE, true);
    }

    public static function getConfigFolders($configs)
    {
        $configFolders = array();

        foreach ($configs as $config=>$info) {
            if (strpos($config, ':') === false) {
                $configFolders[$config] = $info;
            }
        }

        return $configFolders;
    }

    public static function getRecipes($configs)
    {
        $recipes = array();

        foreach ($configs as $index=>$info) {
            if (preg_match("/recipe:(.*)/", $index, $matches)) {
                $recipe = $matches[1];
                $recipes[$recipe] = $info;
            }
        }

        return $recipes;
    }

    public static function createRecipesFolder()
    {
        if (!is_dir('recipes')) {
            echo "\n [UPDATE] Creating [recipes] dir";
            self::createDir('recipes');
        }
    }

    public static function createRecipes($recipes)
    {
        foreach ($recipes as $recipeName=>$recipe) {
            $fileContent = file_get_contents($recipe['resource']);

            echo "\n [UPDATE] Creating recipe file for [" . $recipeName . "] config";
            file_put_contents('recipes/' . $recipeName, $fileContent);
        }
    }


    public static function createConfigFolders($configs)
    {
        $configFolders = array_keys($configs);

        if (!is_dir('config')) {
            echo "\n [UPDATE] Creating [config] base dir";
            self::createDir('config');
        }

        echo "\n [UPDATE] Creating folders for configs";

        foreach ($configFolders as $configFolder) {
            self::createDir('config/' . $configFolder);
        }
    }

    public static function createDir($dir)
    {
        if (is_dir($dir)) {
            return;
        }

        $success = mkdir($dir);

        if (!$success) {
            echo "\n [ERROR][UPDATE] Can not create directory: " . $dir;
            exit;
        } else {
            echo "\n [UPDATE] Directory [" . $dir . "] created successfully";
        }
    }

    public static function createConfigFiles($configs)
    {
        foreach ($configs as $config=>$info) {
            $configFilePath = 'config/' . $config . '/config.php';

            if (!array_key_exists('resource', $info)) {
                if (is_file($configFilePath)) {
                    echo "\n [INFO][UPDATE] No remote resource provided for config file, using local config instead";
                    $fileContent = file_get_contents($configFilePath);
                } else {
                    echo "\n [ERROR][UPDATE] No config file present for config: " . $config;
                    exit;
                }
            } else {
                $fileContent = file_get_contents($info['resource']['config-uri']);
            }

            if (strpos($fileContent, '<?php') === 0) {
                echo "\n [UPDATE] Replacing values in config from mikado.ini";

                if (array_key_exists('replace', $info)) {
                    foreach ($info['replace'] as $search => $replace) {
                        $fileContent = str_replace('%' . $search . '%', $replace, $fileContent);
                    }
                }

                echo "\n [UPDATE] Creating config file for [" . $config . "] config";
                file_put_contents($configFilePath, $fileContent);
            } else {
                echo "\n [ERROR][UPDATE] Remote file config: " . $info['resource']['config-uri'] . " is not a PHP file";
                exit;
            }
        }
    }

    public static function createQueryFiles($configs)
    {
        foreach ($configs as $config=>$info) {
            $queriesFilePath = 'config/' . $config . '/queries.php';

            if (!array_key_exists('resource', $info)) {
                if (is_file($queriesFilePath)) {
                    echo "\n [INFO][UPDATE] No remote resource provided for queries, using local config instead";
                    continue;
                } else {
                    echo "\n [ERROR][UPDATE] No queries file present for config: " . $config;
                    exit;
                }
            }
            $fileContent = file_get_contents($info['resource']['queries-uri']);
            if (strpos($fileContent, '<?php') === 0) {
                echo "\n [UPDATE] Creating queries file for [" . $config . "] config";
                file_put_contents('config/' . $config . '/queries.php', $fileContent);
            } else {
                echo "\n [ERROR][UPDATE] Remote queries: " . $info['resource']['queries-uri'] . " is not a PHP file";
                exit;
            }
        }
    }
}