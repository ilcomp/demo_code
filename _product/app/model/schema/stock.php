<?
$tables[] = array(
	'name' => 'stock',
	'field' => array(
		array(
			'name' => 'stock_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'location_id',
			'type' => 'int',
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
		'stock_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'stock_description',
	'field' => array(
		array(
			'name' => 'stock_id',
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
		'stock_id',
		'language_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'option_variant_stock',
	'field' => array(
		array(
			'name' => 'variant_id',
			'type' => 'int',
			'not_null' => true
		),
		array(
			'name' => 'stock_id',
			'type' => 'int',
			'not_null' => true
		),
		array(
			'name' => 'quantity',
			'type' => 'smallint',
			'not_null' => true,
			'default' => 0
		)
	),
	'primary' => array(
		'variant_id',
		'stock_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'catalog_product_stock',
	'field' => array(
		array(
			'name' => 'product_id',
			'type' => 'int',
			'not_null' => true
		),
		array(
			'name' => 'stock_id',
			'type' => 'int',
			'not_null' => true
		),
		array(
			'name' => 'quantity',
			'type' => 'smallint',
			'not_null' => true,
			'default' => 0
		)
	),
	'primary' => array(
		'product_id',
		'stock_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'option_variant_stock_data',
	'field' => array(
		array(
			'name' => 'variant_id',
			'type' => 'int',
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
			'name' => 'weight',
			'type' => 'decimal(15,8)',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'length',
			'type' => 'decimal(15,8)',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'width',
			'type' => 'decimal(15,8)',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'height',
			'type' => 'decimal(15,8)',
			'not_null' => true,
			'default' => 0
		)
	),
	'primary' => array(
		'variant_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'catalog_product_stock_data',
	'field' => array(
		array(
			'name' => 'product_id',
			'type' => 'int',
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
			'name' => 'subtract',
			'type' => 'tinyint',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'minimum',
			'type' => 'int',
			'not_null' => true,
			'default' => 1
		),
		array(
			'name' => 'shipping',
			'type' => 'tinyint',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'weight',
			'type' => 'decimal(15,8)',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'weight_class_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'length',
			'type' => 'decimal(15,8)',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'width',
			'type' => 'decimal(15,8)',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'height',
			'type' => 'decimal(15,8)',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'length_class_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		)
	),
	'primary' => array(
		'product_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'weight_class',
	'field' => array(
		array(
			'name' => 'weight_class_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'value',
			'type' => 'decimal(15,8)',
			'not_null' => true,
			'default' => 0
		)
	),
	'primary' => array(
		'weight_class_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'weight_class_description',
	'field' => array(
		array(
			'name' => 'weight_class_id',
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
			'type' => 'varchar(32)',
			'not_null' => true
		),
		array(
			'name' => 'unit',
			'type' => 'varchar(4)',
			'not_null' => true
		)
	),
	'primary' => array(
		'weight_class_id',
		'language_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'length_class',
	'field' => array(
		array(
			'name' => 'length_class_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'value',
			'type' => 'decimal(15,8)',
			'not_null' => true
		)
	),
	'primary' => array(
		'length_class_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'length_class_description',
	'field' => array(
		array(
			'name' => 'length_class_id',
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
			'type' => 'varchar(32)',
			'not_null' => true
		),
		array(
			'name' => 'unit',
			'type' => 'varchar(4)',
			'not_null' => true
		)
	),
	'primary' => array(
		'length_class_id',
		'language_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);
