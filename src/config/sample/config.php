<?php

$config = array(
    'environments'            => array(
        'production' => array(
            'type' => 'mysql',
            'host' => '',
            'dbname' => '',
            'usr' => '',
            'psw' => ''
        ),
        'dry-run' => array(
            'type'        => 'dryrun',
            'filePath'    => 'io/dry-run.sql',
            'fileAppend'  => false,
            'targetEnvironment' => 'a'
        ),
        'serialized' => array(
            'type'     => 'serializeddatafile',
            'filePath' => 'io/serialized'
        )
    ),
    'groups'           => array(
        'basic'         => array(
            'query-index-a',
            'query-index-b'
        ),
        'advanced'      => array(
            'query-index-c',
            'query-index-d'
        )
    ),
    'groups-to-import' => array('basic', 'advanced'),
    'queries-file'     => 'config/default/queries.php',
    'compact-mode'     => true
);
