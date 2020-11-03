<?
$tables[] = array(
	'name' => 'form',
	'field' => array(
		array(
			'name' => 'form_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'setting',
			'type' => 'text',
			'not_null' => true
		),
		array(
			'name' => 'email',
			'type' => 'varchar(255)',
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
		'form_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'form_description',
	'field' => array(
		array(
			'name' => 'form_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
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
		)
	),
	'primary' => array(
		'form_id',
		'language_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'form_field',
	'field' => array(
		array(
			'name' => 'form_field_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'form_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'type',
			'type' => 'varchar(14)',
			'not_null' => true
		),
		array(
			'name' => 'code',
			'type' => 'varchar(32)',
			'not_null' => true
		),
		array(
			'name' => 'default',
			'type' => 'text',
			'not_null' => true
		),
		array(
			'name' => 'required',
			'type' => 'tinyint(1)',
			'not_null' => true
		),
		array(
			'name' => 'sort_order',
			'type' => 'smallint',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'setting',
			'type' => 'text',
			'not_null' => true
		)
	),
	'primary' => array(
		'form_field_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'form_field_description',
	'field' => array(
		array(
			'name' => 'form_field_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
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
			'name' => 'help',
			'type' => 'varchar(255)',
			'not_null' => true
		),
		array(
			'name' => 'error',
			'type' => 'varchar(255)',
			'not_null' => true
		)
	),
	'primary' => array(
		'form_field_id',
		'language_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);