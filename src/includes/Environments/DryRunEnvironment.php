<?php

class DryRunEnvironment implements Environment
{
	public $name;
	public $filePath;
	public $fileAppend;
	public $rawQueries;
	public $output;

	const DRY_RUN_ENVIRONMENT_OUTPUT_FILE = 'file';
	const DRY_RUN_ENVIRONMENT_SCREEN = 'screen';

	public function __construct($name, $filePath, $fileAppend, $output)
	{
		$this->name       = $name;
		$this->filePath   = $filePath;
		$this->fileAppend = $fileAppend;
		$this->output     = $output;
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
            $this->saveInFile("", false);
        }

        echo "\n [DRY-RUN] Saving data transfer into file: " . $this->filePath;

        if (!empty($this->rawQueries)) {
            foreach ($this->rawQueries as $descriptor => $rQueries) {
                $msg = " [DRY-RUN][RAW QUERIES] " . $descriptor;
                echo "\n" . $msg;
                $this->saveInFile("\n" . '--' . $msg);
                foreach ($rQueries as $rQuery) {
                    echo "\n [DRY-RUN][RAW QUERIES] Saving raw queries in " . $this->filePath;
                    $this->saveInFile("\n" . $rQuery . ";");
                }
            }
        } else {
            echo "\n [DRY-RUN] Empty raw data queries";
        }

        if (empty($data)) {
            echo "\n [DRY-RUN][WARNING] Empty regular data set";
            $this->saveInFile("\n -- No Regular data found");
            return;
        }

        foreach ($data as $index => $queries) {
            $msgTable =  "\n -- [" . $index . "] to environment [" . $this->name . "]";
            echo $msgTable;
            $this->saveInFile("\n" . $msgTable);

            if (empty($queries)) {
                echo "\n [DRY-RUN][WARNING] Empty data set [" . $index . "]";
                continue;
            }

            foreach ($queries as $query) {
                $this->saveInFile("\n" . $query . ";");
            }

        }
	}

	public function saveInFile($data, $fileAppend=true)
    {
        if ($this->output == self::DRY_RUN_ENVIRONMENT_OUTPUT_FILE) {
            $fileAppend = ($fileAppend) ? FILE_APPEND : 0;
            file_put_contents($this->filePath, $data, $fileAppend);
        } else {
            echo "\n" . $data;
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