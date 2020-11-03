<?
$tables[] = array(
	'name' => 'cart',
	'field' => array(
		array(
			'name' => 'cart_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'api_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'account_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'session_id',
			'type' => 'varchar(32)',
			'not_null' => true
		),
		array(
			'name' => 'product_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'option',
			'type' => 'text',
			'not_null' => true
		),
		array(
			'name' => 'quantity',
			'type' => 'smallint',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'date_added',
			'type' => 'datetime',
			'not_null' => true
		)
	),
	'primary' => array(
		'cart_id'
	),
	'index' => array(
		array(
			'name' => 'cart_id',
			'key' => array(
				'api_id',
				'account_id',
				'session_id',
				'product_id'
			)
		)
	),
	'engine' => 'InnoDB',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'order',
	'field' => array(
		array(
			'name' => 'order_id',
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
			'name' => 'store_name',
			'type' => 'varchar(64)',
			'not_null' => true
		),
		array(
			'name' => 'store_url',
			'type' => 'varchar(255)',
			'not_null' => true
		),
		array(
			'name' => 'account_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'account_group_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'email',
			'type' => 'varchar(96)',
			'not_null' => true
		),
		array(
			'name' => 'telephone',
			'type' => 'varchar(32)',
			'not_null' => true
		),
		array(
			'name' => 'payment_method',
			'type' => 'varchar(128)',
			'not_null' => true
		),
		array(
			'name' => 'payment_code',
			'type' => 'varchar(128)',
			'not_null' => true
		),
		array(
			'name' => 'shipping_method',
			'type' => 'varchar(128)',
			'not_null' => true
		),
		array(
			'name' => 'shipping_code',
			'type' => 'varchar(128)',
			'not_null' => true
		),
		array(
			'name' => 'total',
			'type' => 'decimal(15,4)',
			'not_null' => true,
			'default' => '0.0000'
		),
		array(
			'name' => 'order_status_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'commission',
			'type' => 'decimal(15,4)',
			'not_null' => true
		),
		array(
			'name' => 'language_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'currency_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'currency_code',
			'type' => 'varchar(3)',
			'not_null' => true
		),
		array(
			'name' => 'currency_value',
			'type' => 'decimal(15,8)',
			'not_null' => true,
			'default' => '1.00000000'
		),
		array(
			'name' => 'ip',
			'type' => 'varchar(40)',
			'not_null' => true
		),
		array(
			'name' => 'forwarded_ip',
			'type' => 'varchar(40)',
			'not_null' => true
		),
		array(
			'name' => 'user_agent',
			'type' => 'varchar(255)',
			'not_null' => true
		),
		array(
			'name' => 'accept_language',
			'type' => 'varchar(255)',
			'not_null' => true
		),
		array(
			'name' => 'date_added',
			'type' => 'datetime',
			'not_null' => true
		),
		array(
			'name' => 'date_modified',
			'type' => 'datetime',
			'not_null' => true
		)
	),
	'primary' => array(
		'order_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'order_additionally',
	'field' => array(
		array(
			'name' => 'order_additionally_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'order_id',
			'type' => 'int',
			'not_null' => true
		),
		array(
			'name' => 'code',
			'type' => 'varchar(64)',
			'not_null' => true
		),
		array(
			'name' => 'value',
			'type' => 'text',
			'not_null' => true
		)
	),
	'primary' => array(
		'order_additionally_id'
	),
	'index' => array(
		array(
			'name' => 'order_id',
			'key' => array(
				'order_id'
			)
		)
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'order_custom_field',
	'field' => array(
		array(
			'name' => 'order_id',
			'type' => 'int',
			'not_null' => true
		),
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
			'name' => 'value',
			'type' => 'text',
			'not_null' => true
		)
	),
	'primary' => array(
		'order_id',
		'custom_field_id',
		'language_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'order_history',
	'field' => array(
		array(
			'name' => 'order_history_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'order_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'order_status_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'notify',
			'type' => 'tinyint',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'comment',
			'type' => 'text',
			'not_null' => true
		),
		array(
			'name' => 'date_added',
			'type' => 'datetime',
			'not_null' => true
		)
	),
	'primary' => array(
		'order_history_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'order_product',
	'field' => array(
		array(
			'name' => 'order_product_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'order_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'product_id',
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
			'name' => 'model',
			'type' => 'varchar(64)',
			'not_null' => true
		),
		array(
			'name' => 'sku',
			'type' => 'varchar(64)',
			'not_null' => true
		),
		array(
			'name' => 'quantity',
			'type' => 'smallint',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'price',
			'type' => 'decimal(15,4)',
			'not_null' => true,
			'default' => '0.0000'
		),
		array(
			'name' => 'total',
			'type' => 'decimal(15,4)',
			'not_null' => true,
			'default' => '0.0000'
		),
		array(
			'name' => 'option',
			'type' => 'text',
			'not_null' => true
		)
	),
	'primary' => array(
		'order_product_id'
	),
	'index' => array(
		array(
			'name' => 'order_id',
			'key' => array(
				'order_id'
			)
		)
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'order_option',
	'field' => array(
		array(
			'name' => 'order_option_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'order_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'order_product_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'option_code',
			'type' => 'varchar(255)',
			'not_null' => true
		),
		array(
			'name' => 'option_value',
			'type' => 'text',
			'not_null' => true
		),
		array(
			'name' => 'name',
			'type' => 'varchar(255)',
			'not_null' => true
		),
		array(
			'name' => 'value',
			'type' => 'text',
			'not_null' => true
		)
	),
	'primary' => array(
		'order_option_id'
	),
	'index' => array(
		array(
			'name' => 'order_id',
			'key' => array(
				'order_id'
			)
		),
		array(
			'name' => 'order_product_id',
			'key' => array(
				'order_product_id'
			)
		)
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'order_total',
	'field' => array(
		array(
			'name' => 'order_total_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'order_id',
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
			'name' => 'title',
			'type' => 'varchar(255)',
			'not_null' => true
		),
		array(
			'name' => 'value',
			'type' => 'decimal(15,4)',
			'not_null' => true,
			'default' => '0.0000'
		),
		array(
			'name' => 'sort_order',
			'type' => 'smallint',
			'not_null' => true,
			'default' => 0
		)
	),
	'primary' => array(
		'order_total_id'
	),
	'index' => array(
		array(
			'name' => 'order_id',
			'key' => array(
				'order_id'
			)
		)
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);