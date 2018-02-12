<?php


function hooks($sourceEnvironment, $targetEnvironment, $queries, $key, $config)
{
    if (!isset($config['execution']['hooks'])) {
        echo "\n [i] No applicable Hooks";
        return;
    }

    $hooksInMemory = HOOKS_init();

    $hooks = $config['execution']['hooks'];

    foreach ($hooks as $hook => $params) {
        if (in_array($hook, array_keys($hooksInMemory))) {
            $hookExecutionMethod = $hooksInMemory[$hook] . '_execute';
            echo "[HOOKS][" . $hook . "] BEGIN FOR KEY: " . $key;
            $sourceEnv = EnvironmentFactory::getEnvironment($config['environments'][$sourceEnvironment]);
            $targetEnv = EnvironmentFactory::getEnvironment($config['environments'][$targetEnvironment]);
            $hookExecutionMethod($sourceEnv, $targetEnv, $queries, $key, $config);

        } else {
            throw new Exception("\n[ERROR][HOOKS] Unknown hook: " . $hook . "\n\n");
        }
    }

    echo "\n[HOOKS] End of hooks";
}


function HOOKS_getAvailableHooks()
{
    $availableHooks = array();

    if ($handler = opendir('./hooks')) {
        while (false !== ($file = readdir($handler))) {
            if (preg_match('/.*_hook.php$/', $file)) {
                list($name, $extension) = explode('.', $file);
                $availableHooks[] = $name;

            }
        }

        return $availableHooks;
    }
}

function HOOKS_init()
{
    $availableHooks = HOOKS_getAvailableHooks();
    $inMemoryHooks = array();

    foreach ($availableHooks as $hook) {
        include_once('./hooks/' . $hook . '.php');
        $command = constant($hook . '_command');
        $inMemoryHooks[$command] = $hook;
    }

    return $inMemoryHooks;
}

function HOOKS_data()
{
    HOOKS_init();
    $hooksData = array();

    $availableHooks = HOOKS_getAvailableHooks();

    foreach ($availableHooks as $hook) {
        $command = constant($hook . '_command');

        $hooksData[$command] = array(
            'file' => $hook . '.php',
            'cmd' => constant($hook . '_command'),
            'info' => constant($hook . '_info')
        );
    }

    return $hooksData;
}
