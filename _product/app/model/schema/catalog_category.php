<?
$tables[] = array(
	'name' => 'catalog_category',
	'field' => array(
		array(
			'name' => 'category_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'parent_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'sort_order',
			'type' => 'smallint',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'status',
			'type' => 'tinyint(1)',
			'not_null' => true,
			'default' => 0
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
		'category_id'
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

$tables[] = array(
	'name' => 'catalog_category_custom_field',
	'field' => array(
		array(
			'name' => 'category_id',
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
		'category_id',
		'custom_field_id',
		'language_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'catalog_category_description',
	'field' => array(
		array(
			'name' => 'category_id',
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
		'category_id',
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
	'name' => 'catalog_category_path',
	'field' => array(
		array(
			'name' => 'category_id',
			'type' => 'int',
			'not_null' => true
		),
		array(
			'name' => 'path_id',
			'type' => 'int',
			'not_null' => true
		),
		array(
			'name' => 'level',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		)
	),
	'primary' => array(
		'category_id',
		'path_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'catalog_category_to_store',
	'field' => array(
		array(
			'name' => 'category_id',
			'type' => 'int',
			'not_null' => true
		),
		array(
			'name' => 'store_id',
			'type' => 'int',
			'not_null' => true
		)
	),
	'primary' => array(
		'category_id',
		'store_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);