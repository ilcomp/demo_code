<?
$tables[] = array(
	'name' => 'catalog_price',
	'field' => array(
		array(
			'name' => 'price_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'currency_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'main',
			'type' => 'tinyint',
			'not_null' => true,
			'default' => 0
		)
	),
	'primary' => array(
		'price_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'catalog_price_description',
	'field' => array(
		array(
			'name' => 'price_id',
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
		)
	),
	'primary' => array(
		'price_id',
		'language_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'currency',
	'field' => array(
		array(
			'name' => 'currency_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'name',
			'type' => 'varchar(32)',
			'not_null' => true
		),
		array(
			'name' => 'code',
			'type' => 'varchar(3)',
			'not_null' => true
		),
		array(
			'name' => 'symbol_left',
			'type' => 'varchar(12)',
			'not_null' => true
		),
		array(
			'name' => 'symbol_right',
			'type' => 'varchar(12)',
			'not_null' => true
		),
		array(
			'name' => 'decimal_place',
			'type' => 'char(1)',
			'not_null' => true
		),
		array(
			'name' => 'value',
			'type' => 'double(15,8)',
			'not_null' => true
		),
		array(
			'name' => 'status',
			'type' => 'tinyint',
			'not_null' => true
		),
		array(
			'name' => 'date_modified',
			'type' => 'datetime',
			'not_null' => true
		)
	),
	'primary' => array(
		'currency_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);