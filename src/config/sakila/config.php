<?php

$config = array(
	'environments' => array(
        'sakila-a' => array(
            'type'   => 'mysql',
            'host'   => '127.0.0.1:8889',
    		'dbname' => 'sakila',
    		'usr'    => 'root',
    		'psw'    => 'root'
        ),
        'sakila-b' => array(
            'type'   => 'mysql',
            'host'   => '127.0.0.1:8889',
            'dbname' => 'sakila2',
            'usr'    => 'root',
            'psw'    => 'root'
        ),
        'actor.keys' => array(
            'type'       => 'keyfile',
            'fileAppend' => false
        ),
        'dry-run' => array(
            'type'        => 'dryrun',
            'filePath'    => 'io/sakila-dry-run.sql',
            'fileAppend'  => false
        ),
        'screen' => array(
            'type'        => 'dryrun',
            'filePath'    => 'io/sakila-dry-run.sql',
            'fileAppend'  => false,
            'output'      => 'screen'
        ),
        'raw' => array(
            'type'          => 'raw',
            'putOperation'  => 'printRaw'
        )
    ),
    'groups' => array(
        'test' => array('actor'),
        'create-tables' => array(
            'actor:create-table',
            'address:create-table'
        ),
        'basic' => array(
            'actor',
            'address',
            'category'
        )
    ),  
    'groups-to-import' => array('basic'),
    'queries-file'     => 'config/sakila/queries.php',
    'compact-mode'     => true
);
