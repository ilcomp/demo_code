<?php
$tables[] = array(
	'name' => 'download',
	'field' => array(
		array(
			'name' => 'download_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'filename',
			'type' => 'varchar(160)',
			'not_null' => true
		),
		array(
			'name' => 'mask',
			'type' => 'varchar(128)',
			'not_null' => true
		),
		array(
			'name' => 'date_added',
			'type' => 'datetime',
			'not_null' => true
		)
	),
	'primary' => array(
		'download_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'download_description',
	'field' => array(
		array(
			'name' => 'download_id',
			'type' => 'int',
			'not_null' => true
		),
		array(
			'name' => 'language_id',
			'type' => 'int',
			'not_null' => true
		),
		array(
			'name' => 'name',
			'type' => 'varchar(64)',
			'not_null' => true
		)
	),
	'primary' => array(
		'download_id',
		'language_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'download_report',
	'field' => array(
		array(
			'name' => 'download_report_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'download_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'store_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'ip',
			'type' => 'varchar(40)',
			'not_null' => true
		),
		array(
			'name' => 'country',
			'type' => 'varchar(2)',
			'not_null' => true
		),
		array(
			'name' => 'date_added',
			'type' => 'datetime',
			'not_null' => true
		)
	),
	'primary' => array(
		'download_report_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);