<?php
$tables[] = array(
	'name' => 'api',
	'field' => array(
		array(
			'name' => 'api_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'permission_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'login',
			'type' => 'varchar(255)',
			'not_null' => true
		),
		array(
			'name' => 'password',
			'type' => 'varchar(255)',
			'not_null' => true
		),
		array(
			'name' => 'key',
			'type' => 'text',
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
			'name' => 'date_modified',
			'type' => 'datetime',
			'not_null' => true
		)
	),
	'primary' => array(
		'api_id'
	),
	'index' => array(
		array(
			'name' => 'login',
			'key' => array(
				'login'
			)
		)
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'api_ip',
	'field' => array(
		array(
			'name' => 'api_ip_id',
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
			'name' => 'ip',
			'type' => 'varchar(40)',
			'not_null' => true
		)
	),
	'primary' => array(
		'api_ip_id'
	),
	'index' => array(
		array(
			'name' => 'api_id',
			'key' => array(
				'api_id'
			)
		)
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'api_session',
	'field' => array(
		array(
			'name' => 'api_session_id',
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
			'name' => 'session_id',
			'type' => 'varchar(32)',
			'not_null' => true
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
		),
		array(
			'name' => 'date_modified',
			'type' => 'datetime',
			'not_null' => true
		)
	),
	'primary' => array(
		'api_session_id'
	),
	'index' => array(
		array(
			'name' => 'api_id',
			'key' => array(
				'api_id'
			)
		)
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'permission',
	'field' => array(
		array(
			'name' => 'permission_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'code',
			'type' => 'varchar(64)',
			'not_null' => true
		),
		array(
			'name' => 'priority',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		)
	),
	'primary' => array(
		'permission_id'
	),
	'index' => array(
		array(
			'name' => 'code',
			'key' => array(
				'code'
			)
		)
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'permission_access',
	'field' => array(
		array(
			'name' => 'permission_access_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'permission_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'code',
			'type' => 'varchar(64)',
			'not_null' => true
		),
		array(
			'name' => 'key',
			'type' => 'varchar(255)',
			'not_null' => true
		)
	),
	'primary' => array(
		'permission_access_id'
	),
	'index' => array(
		array(
			'name' => 'permission_id',
			'key' => array(
				'permission_id'
			)
		)
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);
