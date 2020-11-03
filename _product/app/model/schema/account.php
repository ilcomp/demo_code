<?php
$tables[] = array(
	'name' => 'account',
	'field' => array(
		array(
			'name' => 'account_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'account_group_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
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
			'name' => 'password',
			'type' => 'varchar(255)',
			'not_null' => true
		),
		array(
			'name' => 'status',
			'type' => 'tinyint',
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
		'account_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'account_address',
	'field' => array(
		array(
			'name' => 'account_address_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'account_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'address',
			'type' => 'text',
			'not_null' => true
		)
	),
	'primary' => array(
		'account_address_id'
	),
	'index' => array(
		array(
			'name' => 'account_id',
			'key' => array(
				'account_id'
			)
		)
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'account_approval',
	'field' => array(
		array(
			'name' => 'account_approval_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'account_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'type',
			'type' => 'varchar(9)',
			'not_null' => true
		),
		array(
			'name' => 'date_added',
			'type' => 'datetime',
			'not_null' => true
		)
	),
	'primary' => array(
		'account_approval_id'
	),
	'index' => array(
		array(
			'name' => 'account_id',
			'key' => array(
				'account_id',
				'type'
			)
		)
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'account_attempt',
	'field' => array(
		array(
			'name' => 'account_attempt_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'login',
			'type' => 'varchar(96)',
			'not_null' => true
		),
		array(
			'name' => 'ip',
			'type' => 'varchar(40)',
			'not_null' => true
		),
		array(
			'name' => 'attempt',
			'type' => 'tinyint',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'forgotten',
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
		'account_attempt_id'
	),
	'index' => array(
		array(
			'name' => 'login',
			'key' => array(
				'login'
			)
		),
		array(
			'name' => 'ip',
			'key' => array(
				'ip'
			)
		)
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'account_auth',
	'field' => array(
		array(
			'name' => 'account_auth_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'account_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'type',
			'type' => 'varchar(32)',
			'not_null' => true
		),
		array(
			'name' => 'login',
			'type' => 'varchar(255)',
			'not_null' => true
		),
		array(
			'name' => 'token',
			'type' => 'varchar(255)',
			'not_null' => true
		),
		array(
			'name' => 'ip',
			'type' => 'varchar(40)',
			'not_null' => true
		),
		array(
			'name' => 'status',
			'type' => 'tinyint',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'date_added',
			'type' => 'datetime',
			'not_null' => true
		),
		array(
			'name' => 'expires_in',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		)
	),
	'primary' => array(
		'account_auth_id'
	),
	'index' => array(
		array(
			'name' => 'account_id',
			'key' => array(
				'account_id',
				'type'
			)
		),
		array(
			'name' => 'login',
			'key' => array(
				'login',
				'type'
			)
		)
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'account_custom_field',
	'field' => array(
		array(
			'name' => 'account_custom_field_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'account_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'language_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'custom_field_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'value',
			'type' => 'text',
			'not_null' => true
		)
	),
	'primary' => array(
		'account_custom_field_id'
	),
	'index' => array(
		array(
			'name' => 'account_id',
			'key' => array(
				'account_id',
				'language_id',
				'custom_field_id'
			)
		)
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'account_group',
	'field' => array(
		array(
			'name' => 'account_group_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'approval',
			'type' => 'tinyint',
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
		'account_group_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'account_group_description',
	'field' => array(
		array(
			'name' => 'account_group_id',
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
			'name' => 'description',
			'type' => 'text',
			'not_null' => true
		)
	),
	'primary' => array(
		'account_group_id',
		'language_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'account_ip',
	'field' => array(
		array(
			'name' => 'account_ip_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'account_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'store_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'ip',
			'type' => 'varchar(40)',
			'not_null' => true
		),
		array(
			'name' => 'date_added',
			'type' => 'datetime',
			'not_null' => true
		)
	),
	'primary' => array(
		'account_ip_id'
	),
	'index' => array(
		array(
			'name' => 'ip',
			'key' => array(
				'ip'
			)
		)
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);