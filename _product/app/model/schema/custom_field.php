<?php
$tables[] = array(
	'name' => 'custom_field',
	'field' => array(
		array(
			'name' => 'custom_field_id',
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
			'type' => 'varchar(64)',
			'not_null' => true
		),
		array(
			'name' => 'setting',
			'type' => 'text',
			'not_null' => true
		),
		array(
			'name' => 'status',
			'type' => 'tinyint',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'multilanguage',
			'type' => 'tinyint',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'search',
			'type' => 'tinyint',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'sort_order',
			'type' => 'smallint',
			'not_null' => true,
			'default' => 0
		)
	),
	'primary' => array(
		'custom_field_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'custom_field_description',
	'field' => array(
		array(
			'name' => 'custom_field_id',
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
			'type' => 'varchar(128)',
			'not_null' => true
		),
		array(
			'name' => 'help',
			'type' => 'varchar(255)',
			'not_null' => true
		)
	),
	'primary' => array(
		'custom_field_id',
		'language_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'custom_field_location',
	'field' => array(
		array(
			'name' => 'custom_field_id',
			'type' => 'int',
			'not_null' => true
		),
		array(
			'name' => 'location',
			'type' => 'varchar(32)',
			'not_null' => true
		),
		array(
			'name' => 'required',
			'type' => 'tinyint',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'readonly',
			'type' => 'tinyint',
			'not_null' => true,
			'default' => 0
		),
	),
	'primary' => array(
		'custom_field_id',
		'location'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);