<?
$tables[] = array(
	'name' => 'listing',
	'field' => array(
		array(
			'name' => 'listing_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'type',
			'type' => 'varchar(32)',
			'not_null' => true
		),
		array(
			'name' => 'readonly',
			'type' => 'tinyint',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'hidden',
			'type' => 'tinyint',
			'not_null' => true,
			'default' => 0
		)
	),
	'primary' => array(
		'listing_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'listing_description',
	'field' => array(
		array(
			'name' => 'listing_id',
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
		)
	),
	'primary' => array(
		'listing_id',
		'language_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'listing_item',
	'field' => array(
		array(
			'name' => 'listing_item_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'listing_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'value',
			'type' => 'varchar(255)',
			'not_null' => true
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
		)
	),
	'primary' => array(
		'listing_item_id'
	),
	'index' => array(
		array(
			'name' => 'listing_id',
			'key' => array(
				'listing_id'
			)
		)
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'listing_item_description',
	'field' => array(
		array(
			'name' => 'listing_item_id',
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
			'name' => 'description',
			'type' => 'varchar(255)',
			'not_null' => true
		)
	),
	'primary' => array(
		'listing_item_id',
		'language_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);