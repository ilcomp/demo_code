<?
$tables[] = array(
	'name' => 'sms',
	'field' => array(
		array(
			'name' => 'sms_id',
			'type' => 'bigint(15)',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'number',
			'type' => 'bigint(11)',
			'not_null' => true,
			'default' => '0'
		),
		array(
			'name' => 'code',
			'type' => 'varchar(255)',
			'not_null' => true
		),
		array(
			'name' => 'status_id',
			'type' => 'tinyint(1)',
			'not_null' => true,
			'default' => '0'
		),
		array(
			'name' => 'account_id',
			'type' => 'int(11)',
			'not_null' => true,
			'default' => '0'
		),
		array(
			'name' => 'type',
			'type' => 'int(11)',
			'not_null' => true,
			'default' => '0'
		),
		array(
			'name' => 'date_added',
			'type' => 'datetime',
			'not_null' => true
		),
	),
	'primary' => array(
		'sms_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);
