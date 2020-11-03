<?php
$tables[] = array(
	'name' => 'extension',
	'field' => array(
		array(
			'name' => 'extension_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'type',
			'type' => 'varchar(32)',
			'not_null' => true
		),
		array(
			'name' => 'code',
			'type' => 'varchar(32)',
			'not_null' => true
		)
	),
	'primary' => array(
		'extension_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'extension_install',
	'field' => array(
		array(
			'name' => 'extension_install_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'extension_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'extension_download_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'filename',
			'type' => 'varchar(255)',
			'not_null' => true
		),
		array(
			'name' => 'date_added',
			'type' => 'datetime',
			'not_null' => true
		)
	),
	'primary' => array(
		'extension_install_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'extension_path',
	'field' => array(
		array(
			'name' => 'extension_path_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'extension_install_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'path',
			'type' => 'varchar(255)',
			'not_null' => true
		),
		array(
			'name' => 'date_added',
			'type' => 'datetime',
			'not_null' => true
		)
	),
	'primary' => array(
		'extension_path_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'modification',
	'field' => array(
		array(
			'name' => 'modification_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'extension_install_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'extension_download_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'name',
			'type' => 'varchar(64)',
			'not_null' => true
		),
		array(
			'name' => 'code',
			'type' => 'varchar(64)',
			'not_null' => true
		),
		array(
			'name' => 'author',
			'type' => 'varchar(64)',
			'not_null' => true
		),
		array(
			'name' => 'version',
			'type' => 'varchar(32)',
			'not_null' => true
		),
		array(
			'name' => 'link',
			'type' => 'varchar(255)',
			'not_null' => true
		),
		array(
			'name' => 'xml',
			'type' => 'mediumtext',
			'not_null' => true
		),
		array(
			'name' => 'status',
			'type' => 'tinyint',
			'not_null' => true
		),
		array(
			'name' => 'date_added',
			'type' => 'datetime',
			'not_null' => true
		)
	),
	'primary' => array(
		'modification_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);