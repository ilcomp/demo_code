<?
$tables[] = array(
	'name' => 'catalog_product',
	'field' => array(
		array(
			'name' => 'product_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'sort_order',
			'type' => 'smallint',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'status',
			'type' => 'tinyint',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'date_available',
			'type' => 'datetime',
			'not_null' => true,
			'default' => '0000-00-00 00:00'
		),
		array(
			'name' => 'date_added',
			'type' => 'datetime',
			'not_null' => true,
			'default' => '0000-00-00 00:00'
		),
		array(
			'name' => 'date_modified',
			'type' => 'datetime',
			'not_null' => true,
			'default' => '0000-00-00 00:00'
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
	'name' => 'catalog_product_custom_field',
	'field' => array(
		array(
			'name' => 'product_id',
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
		'product_id',
		'custom_field_id',
		'language_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'catalog_product_description',
	'field' => array(
		array(
			'name' => 'product_id',
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
			'type' => 'varchar(255)',
			'not_null' => true
		),
		array(
			'name' => 'title',
			'type' => 'varchar(255)',
			'not_null' => true
		),
		array(
			'name' => 'meta_title',
			'type' => 'varchar(255)',
			'not_null' => true
		),
		array(
			'name' => 'meta_description',
			'type' => 'varchar(255)',
			'not_null' => true
		),
		array(
			'name' => 'tag',
			'type' => 'text',
			'not_null' => true
		)
	),
	'primary' => array(
		'product_id',
		'language_id'
	),
	'index' => array(
		array(
			'name' => 'name',
			'key' => array(
				'name'
			)
		)
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'catalog_product_image',
	'field' => array(
		array(
			'name' => 'product_image_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'product_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'image',
			'type' => 'varchar(255)',
			'not_null' => true
		),
		array(
			'name' => 'sort_order',
			'type' => 'smallint',
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
		'product_image_id'
	),
	'index' => array(
		array(
			'name' => 'product_id',
			'key' => array(
				'product_id'
			)
		)
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'catalog_product_price',
	'field' => array(
		array(
			'name' => 'product_id',
			'type' => 'int',
			'not_null' => true
		),
		array(
			'name' => 'price_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'price',
			'type' => 'decimal(15,4)',
			'not_null' => true,
			'default' => '0.0000'
		)
	),
	'primary' => array(
		'product_id',
		'price_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'catalog_product_related',
	'field' => array(
		array(
			'name' => 'product_id',
			'type' => 'int',
			'not_null' => true
		),
		array(
			'name' => 'related_id',
			'type' => 'int',
			'not_null' => true
		)
	),
	'primary' => array(
		'product_id',
		'related_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'catalog_product_to_category',
	'field' => array(
		array(
			'name' => 'product_id',
			'type' => 'int',
			'not_null' => true
		),
		array(
			'name' => 'category_id',
			'type' => 'int',
			'not_null' => true
		),
		array(
			'name' => 'main',
			'type' => 'tinyint',
			'not_null' => true,
			'default' => 0
		)
	),
	'primary' => array(
		'product_id',
		'category_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'catalog_product_to_store',
	'field' => array(
		array(
			'name' => 'product_id',
			'type' => 'int',
			'not_null' => true
		),
		array(
			'name' => 'store_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		)
	),
	'primary' => array(
		'product_id',
		'store_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);