<?php

Class Alias {

    public static function checkAlias($params)
    {
        $aliases = parse_ini_file('alias.ini');
        $aliasRegexp = "/^(" . implode("|", array_keys($aliases)) . ")$/";

        if (!empty($params) && $params[0] == '-alias') {
            self::showAliasInfo($aliases);
        }

        if (!empty($params) && (preg_match($aliasRegexp, $params[0], $matches))) {
            echo "\n[ALIAS] Detected alias: [" . $matches[0] . "] ";
            $aliasIndex = $matches[0];
            echo "Replacing alias by: [" . $aliases[$aliasIndex]['slug'] . "]";
            $params = self::getParams($aliases[$aliasIndex]['slug'], $params);
        } else {
            echo "\n [ALIAS] No aliases detected";
        }

        return $params;
    }

    public static function getParams($aliasSlug, $originalParams)
    {
        // remove alias itself
        array_shift($originalParams);
        $newParams = explode(' ', $aliasSlug);

        foreach ($originalParams as $param) {
            $newParams[] = $param;
        }

        return $newParams;
    }

    public static function showAliasInfo($aliases)
    {
        echo "\n[ALIAS] Allowed alias:\n";
        echo "\n+---------";

        foreach ($aliases as $alias => $aliasInfo) {
            echo "\n| " . $alias . "\t" . $aliasInfo['help'];
            echo "\n|  \tReplaced command slug > " . $aliasInfo['slug'];
            echo "\n+ ---------";
        }

        echo "\n\n";
        exit;
    }
}