<?php

class SerializedDataFileEnvironment implements Environment
{
    public $name;
    public $filePath;
    public $configTag;
    public $rawQueries = array('RAW'=> array('raw-queries-to-execute' => array()));
    public $selectedQueriesKeys = array();

    public function __construct($name, $filePath, $configTag = 'none')
    {
        $this->name       = $name;
        $this->filePath   = $filePath;
        $this->configTag  = $configTag;
    }

    public function getName()
    {
        return $this->name;
    }

    public function get($queries, $key)
    {
        $returnedData = array();

        echo "\n [SerializedDataFile] reading data from file [" . $this->filePath . "]";
        $this->checkFile();


        $data = $this->getUnserializedContent();

        $selectedKeys = array_keys($queries);

        $this->selectedQueriesKeys = $selectedKeys;

        if (!array_key_exists($this->configTag, $data)) {
            echo "[SerializedDataFile] No data for config: [" . $this->configTag . "]";
            return $returnedData;
        }

        foreach ($data[$this->configTag] as $tableName => $rows) {
            if (in_array($tableName, $selectedKeys)) {
                echo "\n [SerializedDataFile] Getting data from [" . $tableName . "]";
                $nRows = ($rows === false) ? 0 : count($rows);
                echo " (" . $nRows . ")";
                $returnedData[$tableName] = $rows;
            }
        }

        return $returnedData;
    }

    public function put($data)
    {
        echo "\n [SerializedDataFile] putting data to [" . $this->filePath . "] file";

        $currentContent = $this->getUnserializedContent();

        if (!is_array($currentContent)) {
            $currentContent = array();
        }

        if (!key_exists($this->configTag, $currentContent)) {
            $currentContent[$this->configTag] = array();
        }

        foreach ($data as $queryIndex => $rows) {
            $currentContent[$this->configTag][$queryIndex] = $rows;
        }

        $this->putSerializedContent($currentContent);
    }

    public function putSerializedContent($unSerializedContent)
    {
        $serializedData = serialize($unSerializedContent);
        file_put_contents($this->filePath, $serializedData);
    }

    public function getUnserializedContent()
    {
        if (!is_file($this->filePath)) {
            echo "\n [SerializedDataFile] Creating file for serialize data: " . $this->filePath;
            file_put_contents($this->filePath,'');
        }

        $serializedData = file_get_contents($this->filePath);
        $data           = unserialize($serializedData);

        if (empty($data)) {
            $data = array();
        }

        if (!key_exists('RAW', $data)) {
            $data['RAW'] = array();
        }

        if (!key_exists($this->configTag, $data['RAW'])) {
            $data['RAW'][$this->configTag] = array();
        }

        return $data;
    }

    private function checkFile()
    {
        if (!is_file($this->filePath)) {
            echo "\n [ERROR][SerializedDataFile] file not found: " . $this->filePath . "\n\n";
            exit(0);
        }
    }

    public function saveRawQueries()
    {
        $currentContent = $this->getUnserializedContent();

        if (!empty($this->rawQueries)) {
            foreach ($this->rawQueries['RAW'] as $queryIndex => $query) {
                $currentContent['RAW'][$this->configTag][$queryIndex] = $query;
            }
        }

        $this->putSerializedContent($currentContent);
    }

    public function getRawQueries()
    {
        $content = $this->getUnserializedContent();
        $result  = array();

        if (!empty($content) && is_array($content) && array_key_exists('RAW', $content)) {
            foreach ($content['RAW'][$this->configTag] as $queryIndex => $query) {
                if (empty($this->selectedQueriesKeys)) {
                    return array('RAW' => $content['RAW'][$this->configTag]);
                }

                if (in_array($queryIndex, $this->selectedQueriesKeys)) {
                    $result['RAW'][$queryIndex] = $query;
                }
            }
        }

        return $result;
    }

    public function describe($dataIndex)
    {
        return false;
    }

    public function getType()
    {
        return 'SerializedDataFile';
    }

}