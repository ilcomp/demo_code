<?
$tables[] = array(
	'name' => 'menu',
	'field' => array(
		array(
			'name' => 'menu_id',
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
			'name' => 'name',
			'type' => 'varchar(255)',
			'not_null' => true
		),
		array(
			'name' => 'position',
			'type' => 'varchar(14)',
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
		)
	),
	'primary' => array(
		'menu_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'menu_item',
	'field' => array(
		array(
			'name' => 'menu_item_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'menu_id',
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
			'name' => 'code',
			'type' => 'varchar(32)',
			'not_null' => true
		),
		array(
			'name' => 'sort_order',
			'type' => 'smallint',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'title',
			'type' => 'varchar(255)',
			'not_null' => true
		),
		array(
			'name' => 'link',
			'type' => 'varchar(255)',
			'not_null' => true
		),
		array(
			'name' => 'setting',
			'type' => 'text',
			'not_null' => true
		)
	),
	'primary' => array(
		'menu_item_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);