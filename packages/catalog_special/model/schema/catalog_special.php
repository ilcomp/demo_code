<?
$tables[] = array(
	'name' => 'catalog_special',
	'field' => array(
		array(
			'name' => 'catalog_special_id',
			'type' => 'int(11)',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'product_id',
			'type' => 'int(11)',
			'not_null' => true
		),
		array(
			'name' => 'price_id',
			'type' => 'int(11)',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'account_group_id',
			'type' => 'int(11)',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'priority',
			'type' => 'int(5)',
			'not_null' => true,
			'default' => '1'
		),
		array(
			'name' => 'value',
			'type' => 'decimal(15,4)',
			'not_null' => true,
			'default' => '0.0000'
		),
		array(
			'name' => 'date_start',
			'type' => 'date',
			'not_null' => true,
			'default' => '0000-00-00'
		),
		array(
			'name' => 'date_end',
			'type' => 'date',
			'not_null' => true,
			'default' => '0000-00-00'
		)
	),
	'primary' => array(
		'catalog_special_id'
	),
	'index' => array(
		array(
			'name' => 'product_id',
			'key' => array(
				'product_id',
				'price_id'
			)
		)
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);