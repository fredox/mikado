<?php


define("hello_world_hook_command", "hello");
define("hello_world_hook_info", "
		\n\t Show a relation of mysql functions related to carrier with the orders passed.");

require_once('includes/format.php');


function hello_world_hook_execute(Environment $sourceEnvironment, Environment $targetEnvironment, $queries, $keys, $config)
{
    echo "\n[HOOKS][HELLO WORLD] Hello World!";
    echo "\n[HOOKS][SOURCE ENV] " . $sourceEnvironment->getName();
    echo "\n[HOOKS][TARGET ENV] " . $targetEnvironment->getName();

    echo "\n\n";
}
