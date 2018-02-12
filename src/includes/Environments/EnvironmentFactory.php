<?php

include_once('MysqlEnvironment.php');
include_once('DryRunEnvironment.php');
include_once('KeyFileEnvironment.php');
include_once('RawEnvironment.php');

class EnvironmentFactory
{
	static function getEnvironment($environmentConfig)
	{
		$errorMsg = "\n [ERROR] Invalid or Unknown environment type";

		if (!is_array($environmentConfig) || !array_key_exists('type', $environmentConfig)) {
			die($errorMsg . "\n\n");
		}

		switch ($environmentConfig['type']) {
			case 'mysql':
			    list($host, $port) = self::getPortAndHost($environmentConfig['host']);
			    $socket = (array_key_exists('socket', $environmentConfig)) ? $environmentConfig['socket'] : null;
				return new MysqlEnvironment(
					$environmentConfig['name'],
					$host,
					$port,
					$environmentConfig['dbname'],
					$environmentConfig['usr'],
					$environmentConfig['psw'],
                    $socket
				);
				break;
			case 'dryrun':
			    $output = (array_key_exists('output', $environmentConfig)) ?
                    $environmentConfig['output']
                    : DryRunEnvironment::DRY_RUN_ENVIRONMENT_OUTPUT_FILE;
				return new DryRunEnvironment(
					$environmentConfig['name'],
					$environmentConfig['filePath'],
                    $environmentConfig['fileAppend'],
                    $output
				);
				break;
            case 'keyfile':
                $keyField = array_key_exists('keyField', $environmentConfig) ?
                    $environmentConfig['keyField']
                    : 'value';
                $fileAppend = array_key_exists('fileAppend', $environmentConfig) ?
                    $environmentConfig['fileAppend']
                    : false;
                return new KeyFileEnvironment(
                    $environmentConfig['name'],
                    $keyField,
                    $fileAppend
                );
            case 'raw':
                return new RawEnvironment(
                    $environmentConfig['name'],
                    $environmentConfig['putOperation']
                );
			default:
				die($errorMsg . ". Type: " . $environmentConfig['type'] . "\n\n");

		}
	}

	static public function getPortAndHost($hostAndPort)
    {
        if (strpos($hostAndPort, ':') === false) {
            $port = 3306;
            $host = $hostAndPort;
        } else {
            list($host, $port) = explode(':', $hostAndPort);
        }

        return array($host, $port);
    }
}