<?
$tables[] = array(
	'name' => 'sxgeo',
	'field' => array(
		array(
			'name' => 'level',
			'type' => 'tinyint(1)',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'sxgeo_id',
			'type' => 'int(11)',
			'not_null' => true
		),
		array(
			'name' => 'parent_id',
			'type' => 'int(11)',
			'not_null' => true
		),
		array(
			'name' => 'name',
			'type' => 'varchar(255)',
			'not_null' => true
		),
		array(
			'name' => 'sort_order',
			'type' => 'int(5)',
			'not_null' => true,
			'default' => 100
		),
		array(
			'name' => 'auto_update',
			'type' => 'tinyint(1)',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'country',
			'type' => 'varchar(2)',
			'not_null' => true
		)
	),
	'primary' => array(
		'level',
		'sxgeo_id'
	),
	'index' => array(
		array(
			'name' => 'parent_id',
			'key' => array(
				'parent_id'
			)
		)
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);