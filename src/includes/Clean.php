<?php

Class Clean {

    public static $configDir;
    public static $queries;
    public static $config;

    public static function checkClean($cleanParam)
    {
        if ($cleanParam != '-clean') {
            return;
        }

        echo "\n [Clean] START";

        self::deleteIoFiles();
        self::endClean();
    }

    public static function deleteIoFiles()
    {
        $files = glob(Input::INPUT_OUTPUT_FOLDER . '/*'); // get all file names
        foreach($files as $file){ // iterate files
            if(is_file($file)) {
                echo "\n [Clean] Deleting file " . $file;
                unlink($file); // delete file
            }
        }

        echo "\n";
    }

    public static function endClean()
    {
        echo " [Clean] END\n\n";
        exit;
    }
}