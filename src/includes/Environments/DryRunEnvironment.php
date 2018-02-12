<?php

class DryRunEnvironment implements Environment
{
	public $name;
	public $filePath;
	public $fileAppend;
	public $rawQueries;

	public function __construct($name, $filePath, $fileAppend)
	{
		$this->name       = $name;
		$this->filePath   = $filePath;
		$this->fileAppend = $fileAppend;
	}

	public function getName()
    {
        return $this->name;
    }

    public function addRawQueries($descriptor, $queries)
    {
        if (!empty($queries)) {
            echo " \n [DRY-RUN] saving " . count($queries) . " queries in Raw queries";
            $this->rawQueries[$descriptor] = $queries;
        } else {
            echo "\n [DRY-RUN] No Raw queries to save.";
        }
    }

	public function get($queries, $key)
	{
	    echo "\n [DRY-RUN] Getting data from " . $this->filePath;

		$queries = array();

        $handle = fopen($this->filePath, "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $queries[] = $line;
            }

            fclose($handle);
        } else {
            echo "\n [DRY-RUN][ERROR] Can not open dry run file: " . $this->filePath;
        }

        $this->addRawQueries('DRY-RUN-FILE-QUERIES: ' . $this->filePath . '', $queries);

        return array();
	}

	public function put($data)
	{
	    if (!$this->fileAppend) {
            echo "\n [DRY-RUN] Cleaning dry run file: " . $this->filePath;
            file_put_contents($this->filePath, "");
        }

        echo "\n [DRY-RUN] Saving data transfer into file: " . $this->filePath;

        if (!empty($this->rawQueries)) {
            foreach ($this->rawQueries as $descriptor => $rQueries) {
                $msg = " [DRY-RUN][RAW QUERIES] " . $descriptor;
                echo "\n" . $msg;
                file_put_contents($this->filePath, "\n" . '--' . $msg, FILE_APPEND);
                foreach ($rQueries as $rQuery) {
                    file_put_contents($this->filePath, "\n" . $rQuery, FILE_APPEND);
                }
            }
        } else {
            echo "\n [DRY-RUN] Empty raw data queries";
        }

        if (empty($data)) {
            echo "\n [DRY-RUN][WARNING] Empty data set";
            file_put_contents($this->filePath, " -- No data found");
            return;
        }

        foreach ($data as $index => $queries) {
            $msgTable =  "\n -- [" . $index . "] to environment [" . $this->name . "]";
            echo $msgTable;
            file_put_contents($this->filePath, "\n" . $msgTable, FILE_APPEND);

            if (empty($queries)) {
                echo "\n [DRY-RUN][WARNING] Empty data set [" . $index . "]";
                continue;
            }

            foreach ($queries as $query) {
                file_put_contents($this->filePath, "\n" . $query, FILE_APPEND);
            }

        }
	}

	public function describe($dataIndex)
    {
        return false;
    }

	public function getType()
    {
        return 'DryRun';
    }

}