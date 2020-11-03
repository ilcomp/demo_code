<?
$tables[] = array(
	'name' => 'coupon',
	'field' => array(
		array(
			'name' => 'coupon_id',
			'type' => 'int(11)',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'name',
			'type' => 'varchar(128)',
			'not_null' => true
		),
		array(
			'name' => 'code',
			'type' => 'varchar(20)',
			'not_null' => true
		),
		array(
			'name' => 'type',
			'type' => 'char(1)',
			'not_null' => true
		),
		array(
			'name' => 'discount',
			'type' => 'decimal(15,4)',
			'not_null' => true
		),
		array(
			'name' => 'logged',
			'type' => 'tinyint(1)',
			'not_null' => true
		),
		array(
			'name' => 'shipping',
			'type' => 'tinyint(1)',
			'not_null' => true
		),
		array(
			'name' => 'total',
			'type' => 'decimal(15,4)',
			'not_null' => true
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
		),
		array(
			'name' => 'uses_total',
			'type' => 'int(11)',
			'not_null' => true
		),
		array(
			'name' => 'uses_account',
			'type' => 'varchar(11)',
			'not_null' => true
		),
		array(
			'name' => 'status',
			'type' => 'tinyint(1)',
			'not_null' => true
		),
		array(
			'name' => 'date_added',
			'type' => 'datetime',
			'not_null' => true
		)
	),
	'primary' => array(
		'coupon_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'coupon_category',
	'field' => array(
		array(
			'name' => 'coupon_id',
			'type' => 'int(11)',
			'not_null' => true
		),
		array(
			'name' => 'category_id',
			'type' => 'int(11)',
			'not_null' => true
		)
	),
	'primary' => array(
		'coupon_id',
		'category_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'coupon_history',
	'field' => array(
		array(
			'name' => 'coupon_history_id',
			'type' => 'int(11)',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'coupon_id',
			'type' => 'int(11)',
			'not_null' => true
		),
		array(
			'name' => 'order_id',
			'type' => 'int(11)',
			'not_null' => true
		),
		array(
			'name' => 'account_id',
			'type' => 'int(11)',
			'not_null' => true
		),
		array(
			'name' => 'amount',
			'type' => 'decimal(15,4)',
			'not_null' => true
		),
		array(
			'name' => 'date_added',
			'type' => 'datetime',
			'not_null' => true
		)
	),
	'primary' => array(
		'coupon_history_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'coupon_product',
	'field' => array(
		array(
			'name' => 'coupon_product_id',
			'type' => 'int(11)',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'coupon_id',
			'type' => 'int(11)',
			'not_null' => true
		),
		array(
			'name' => 'product_id',
			'type' => 'int(11)',
			'not_null' => true
		)
	),
	'primary' => array(
		'coupon_product_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);