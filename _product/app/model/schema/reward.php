<?php
$tables[] = array(
	'name' => 'reward_account',
	'field' => array(
		array(
			'name' => 'reward_account_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'account_id',
			'type' => 'int',
			'not_null' => true,
			'default' => '0'
		),
		array(
			'name' => 'reward',
			'type' => 'int',
			'not_null' => true,
			'default' => '0'
		),
		array(
			'name' => 'date_added',
			'type' => 'datetime',
			'not_null' => true
		),
		array(
			'name' => 'comment',
			'type' => 'text',
			'not_null' => true
		)
	),
	'primary' => array(
		'reward_account_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'reward_product',
	'field' => array(
		array(
			'name' => 'product_id',
			'type' => 'int',
			'not_null' => true,
			'default' => '0'
		),
		array(
			'name' => 'reward',
			'type' => 'int',
			'not_null' => true,
			'default' => '0'
		)
	),
	'primary' => array(
		'product_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);