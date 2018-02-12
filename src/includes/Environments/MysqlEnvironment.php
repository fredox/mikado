<?php

include_once('Environment.php');

class MysqlEnvironment implements Environment
{
	public $name;
	public $host;
	public $port;
	public $dbname;
	public $user;
	public $password;
	public $operation = 'INSERT';
	public $socket = null;
	public $rawQueries = array();
	public static $cachedConnections = array();

	public $connection;

	public function __construct($name, $host, $port, $dbname, $user, $password, $socket=null)
	{
		$this->name = $name;
		$this->host = $host;
		$this->port = $port;
		$this->dbname = $dbname;
		$this->user = $user;
		$this->password = $password;
		$this->socket = $socket;

		echo "\n [MYSQL ENVIRONMENT] Connecting to [" . $name . "]";

        $cacheHash = $this->getCacheHash();

        if (in_array($cacheHash, array_keys(static::$cachedConnections))) {
            echo "[CACHED]";
            $connection = static::$cachedConnections[$cacheHash];
        } else {
            $errorMsg = "\n [ERROR] Couldn't make connection to " . $this->name . " environment";
            $connection = new mysqli(
                $host,
                $user,
                $password,
                $dbname,
                $port,
                $socket
            ) or die($errorMsg);

            if (!empty($connection->connect_error)) {
                echo "\n [ERROR][MYSQL][" . $this->name . "] ". $connection->connect_error . "\n\n";
                //print_r($connection);
                exit;
            }
            static::$cachedConnections[$cacheHash] = $connection;
        }

        $this->connection = $connection;
    }

    public function addRawQueries($descriptor, $queries)
    {
        $this->rawQueries[$descriptor] = $queries;
    }

    public function getCacheHash()
    {
        return md5($this->name . $this->host . $this->port . $this->dbname . $this->user);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getFields($table)
    {
    	$fields = array();

    	$resultSet = mysqli_query($this->connection, 'SHOW COLUMNS FROM ' . $table);

	    while ($record = mysqli_fetch_assoc($resultSet)) {
	        $fields[] = $record['Field'];
	    }

    	return $fields;
    }

    public function query($query, $fetch=false)
    {
	    $resultSet = mysqli_query($this->connection, $query);
	    $result    = array();

	    if ($resultSet === false) {
	        echo "\n [ERROR] " . mysqli_error($this->connection);
	        return false;
	    }

	    if ($fetch) {
	    	while (($row = mysqli_fetch_assoc($resultSet)) !== null) {
	    		$result[] = $row;
	    	}
	    }

	    return $result;
    }

    public function get($queries, $key)
    {
    	$finalResult = array();

    	foreach ($queries as $tableName => $query) {
            $query = str_replace('@KEY', $key, $query);
            list($tableName, $comment) = $this->getRealTableName($tableName);
            echo "\n [i][". $this->name ."] collecting data from [" . $tableName . $comment . "]";
    		$result = $this->query($query, true);
    		echo "  (". count($result) .") rows";

    		if (empty($result)) {
    		    echo "\n [i][". $this->name ."][WARNING] Not found results in table [" . $tableName . "]";
            } else {
    		    $finalResult[$tableName] = $result;
            }
    	}

    	return $finalResult;
    }

    public function getRealTableName($tableName)
    {
        if (strpos($tableName, ':') !== false) {
            list($tableName, $comment) = explode(':', $tableName);
            $comment = ' (' . $comment . ')';
        } else {
            $comment = '';
        }

        return array($tableName, $comment);
    }

    public function put($data)
    {
        if (empty($data)) {
            echo "\n [MYSQL ENVIRONMENT][" . $this->name . "] No regular data to execute";
        } else {
            foreach ($data as $tableName => $queries) {

                if (empty($queries)) {
                    echo "\n [" . $this->name . "][!] No data found for table [" . $tableName . "]";
                }

                echo "\n [" . $this->name . "][i] Exporting to table [" . $tableName . "] on [" . $this->name . "] environment";
                echo " [" . count($queries) . "] rows";

                foreach ($queries as $query) {

                    $this->query($query);
                }

            }
        }
    }

    public function executeRawQueries()
    {
        if (!empty($this->rawQueries)) {

            echo "\n [MYSQL ENVIRONMENT][" . $this->name . "] Executing raw queries ";
            foreach ($this->rawQueries as $descriptor => $queries) {
                echo "\n [MYSQL ENVIRONMENT][" . $this->name . "][RAW QUERIES][". $descriptor . "]";
                foreach ($queries as $query) {
                    $this->query($query);
                }
            }
        } else {
            echo "\n [MYSQL ENVIRONMENT] No Raw queries to execute";
        }

    }

    public function getType()
    {
        return 'Mysql';
    }

    public function describe($table)
    {
        $hashFields = array();
        $sql = 'DESCRIBE ' . $table;

        $fieldsInformation = $this->query($sql, true);

        foreach ($fieldsInformation as $fieldInformation) {
            $fieldName = $fieldInformation['Field'];
            $hashFields[$fieldName]['type']    = $fieldInformation['Type'];
            $hashFields[$fieldName]['null']    = $fieldInformation['Null'];
            $hashFields[$fieldName]['key']     = $fieldInformation['Key'];
            $hashFields[$fieldName]['default'] = $fieldInformation['Default'];
        }

        return $hashFields;
    }
}












