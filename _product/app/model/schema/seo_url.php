<?php
$tables[] = array(
	'name' => 'seo_regex',
	'field' => array(
		array(
			'name' => 'seo_regex_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'name',
			'type' => 'varchar(64)',
			'not_null' => true
		),
		array(
			'name' => 'regex',
			'type' => 'varchar(255)',
			'not_null' => true
		),
		array(
			'name' => 'sort_order',
			'type' => 'smallint',
			'not_null' => true,
			'default' => 0
		)
	),
	'primary' => array(
		'seo_regex_id'
	),
	'index' => array(
		array(
			'name' => 'regex',
			'key' => array(
				'regex'
			)
		)
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'seo_url',
	'field' => array(
		array(
			'name' => 'seo_url_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'store_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'language_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'query',
			'type' => 'varchar(255)',
			'not_null' => true
		),
		array(
			'name' => 'keyword',
			'type' => 'varchar(255)',
			'not_null' => true
		),
		array(
			'name' => 'push',
			'type' => 'varchar(255)',
			'not_null' => true
		)
	),
	'primary' => array(
		'seo_url_id'
	),
	'index' => array(
		array(
			'name' => 'query',
			'key' => array(
				'query'
			)
		),
		array(
			'name' => 'keyword',
			'key' => array(
				'keyword'
			)
		)
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);