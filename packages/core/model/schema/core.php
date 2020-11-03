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
			'name' => 'username',
			'type' => 'varchar(64)',
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
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'country',
	'field' => array(
		array(
			'name' => 'country_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'iso_code_2',
			'type' => 'varchar(2)',
			'not_null' => true
		),
		array(
			'name' => 'iso_code_3',
			'type' => 'varchar(3)',
			'not_null' => true
		),
		array(
			'name' => 'iso_number',
			'type' => 'int',
			'not_null' => true
		),
		array(
			'name' => 'status',
			'type' => 'tinyint',
			'not_null' => true,
			'default' => 1
		),
		array(
			'name' => 'sort_order',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		)
	),
	'primary' => array(
		'country_id'
	),
	'index' => array(
		array(
			'name' => 'iso_code_2',
			'key' => array(
				'iso_code_2'
			)
		),
		array(
			'name' => 'iso_code_3',
			'key' => array(
				'iso_code_3'
			)
		),
		array(
			'name' => 'iso_number',
			'key' => array(
				'iso_number'
			)
		)
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'country',
	'field' => array(
		array(
			'name' => 'country_id',
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
			'name' => 'full_name',
			'type' => 'varchar(255)',
			'not_null' => true
		)
	),
	'primary' => array(
		'country_id',
		'language_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'cron',
	'field' => array(
		array(
			'name' => 'cron_id',
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
			'name' => 'cycle',
			'type' => 'varchar(12)',
			'not_null' => true
		),
		array(
			'name' => 'action',
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
		'cron_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'event',
	'field' => array(
		array(
			'name' => 'event_id',
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
			'name' => 'trigger',
			'type' => 'text',
			'not_null' => true
		),
		array(
			'name' => 'action',
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
			'name' => 'sort_order',
			'type' => 'smallint',
			'not_null' => true
		)
	),
	'primary' => array(
		'event_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'geo_zone',
	'field' => array(
		array(
			'name' => 'geo_zone_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'name',
			'type' => 'varchar(32)',
			'not_null' => true
		),
		array(
			'name' => 'description',
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
		'geo_zone_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'language',
	'field' => array(
		array(
			'name' => 'language_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'name',
			'type' => 'varchar(32)',
			'not_null' => true
		),
		array(
			'name' => 'code',
			'type' => 'varchar(5)',
			'not_null' => true
		),
		array(
			'name' => 'locale',
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
			'name' => 'status',
			'type' => 'tinyint',
			'not_null' => true
		)
	),
	'primary' => array(
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
	'name' => 'location',
	'field' => array(
		array(
			'name' => 'location_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'geocode',
			'type' => 'varchar(32)',
			'not_null' => true
		),
		array(
			'name' => 'image',
			'type' => 'varchar(255)',
			'not_null' => true
		),
		array(
			'name' => 'issue',
			'type' => 'tinyint',
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
			'type' => 'tinyint',
			'not_null' => true,
			'default' => 0
		),
	),
	'primary' => array(
		'location_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);


$tables[] = array(
	'name' => 'location_description',
	'field' => array(
		array(
			'name' => 'location_id',
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
			'type' => 'varchar(32)',
			'not_null' => true
		),
		array(
			'name' => 'address',
			'type' => 'text',
			'not_null' => true
		),
		array(
			'name' => 'telephone',
			'type' => 'varchar(32)',
			'not_null' => true
		),
		array(
			'name' => 'open',
			'type' => 'text',
			'not_null' => true
		),
		array(
			'name' => 'comment',
			'type' => 'text',
			'not_null' => true
		)
	),
	'primary' => array(
		'location_id',
		'language_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'module',
	'field' => array(
		array(
			'name' => 'module_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'name',
			'type' => 'varchar(64)',
			'not_null' => true
		),
		array(
			'name' => 'code',
			'type' => 'varchar(32)',
			'not_null' => true
		),
		array(
			'name' => 'setting',
			'type' => 'text',
			'not_null' => true
		)
	),
	'primary' => array(
		'module_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'statistics',
	'field' => array(
		array(
			'name' => 'statistics_id',
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
			'name' => 'value',
			'type' => 'decimal(15,4)',
			'not_null' => true
		)
	),
	'primary' => array(
		'statistics_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'session',
	'field' => array(
		array(
			'name' => 'session_id',
			'type' => 'varchar(32)',
			'not_null' => true
		),
		array(
			'name' => 'data',
			'type' => 'text',
			'not_null' => true
		),
		array(
			'name' => 'expire',
			'type' => 'datetime',
			'not_null' => true
		)
	),
	'primary' => array(
		'session_id'
	),
	'engine' => 'InnoDB',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'setting',
	'field' => array(
		array(
			'name' => 'setting_id',
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
			'name' => 'code',
			'type' => 'varchar(128)',
			'not_null' => true
		),
		array(
			'name' => 'key',
			'type' => 'varchar(128)',
			'not_null' => true
		),
		array(
			'name' => 'value',
			'type' => 'text',
			'not_null' => true
		),
		array(
			'name' => 'serialized',
			'type' => 'tinyint',
			'not_null' => true
		)
	),
	'primary' => array(
		'setting_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'store',
	'field' => array(
		array(
			'name' => 'store_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'name',
			'type' => 'varchar(64)',
			'not_null' => true
		),
		array(
			'name' => 'url',
			'type' => 'varchar(255)',
			'not_null' => true
		),
		array(
			'name' => 'ssl',
			'type' => 'varchar(255)',
			'not_null' => true
		)
	),
	'primary' => array(
		'store_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'theme',
	'field' => array(
		array(
			'name' => 'theme_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'store_id',
			'type' => 'int',
			'not_null' => true
		),
		array(
			'name' => 'theme',
			'type' => 'varchar(64)',
			'not_null' => true
		),
		array(
			'name' => 'route',
			'type' => 'varchar(64)',
			'not_null' => true
		),
		array(
			'name' => 'code',
			'type' => 'mediumtext',
			'not_null' => true
		),
		array(
			'name' => 'addrow',
			'type' => 'varchar(255)',
			'not_null' => true
		),
		array(
			'name' => 'removerow',
			'type' => 'varchar(255)',
			'not_null' => true
		),
		array(
			'name' => 'date_added',
			'type' => 'datetime',
			'not_null' => true
		)
	),
	'primary' => array(
		'theme_id'
	),
	'index' => array(
		array(
			'name' => 'route',
			'key' => array(
				'route',
				'date_added',
			)
		)
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'translation',
	'field' => array(
		array(
			'name' => 'translation_id',
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
			'name' => 'language_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'route',
			'type' => 'varchar(64)',
			'not_null' => true
		),
		array(
			'name' => 'key',
			'type' => 'varchar(64)',
			'not_null' => true
		),
		array(
			'name' => 'value',
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
		'translation_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'upload',
	'field' => array(
		array(
			'name' => 'upload_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'name',
			'type' => 'varchar(255)',
			'not_null' => true
		),
		array(
			'name' => 'filename',
			'type' => 'varchar(255)',
			'not_null' => true
		),
		array(
			'name' => 'code',
			'type' => 'varchar(64)',
			'not_null' => true
		),
		array(
			'name' => 'date_added',
			'type' => 'datetime',
			'not_null' => true
		)
	),
	'primary' => array(
		'upload_id'
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
	'name' => 'user',
	'field' => array(
		array(
			'name' => 'user_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'user_group_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'username',
			'type' => 'varchar(20)',
			'not_null' => true
		),
		array(
			'name' => 'password',
			'type' => 'varchar(255)',
			'not_null' => true
		),
		array(
			'name' => 'salt',
			'type' => 'varchar(9)',
			'not_null' => true
		),
		array(
			'name' => 'firstname',
			'type' => 'varchar(32)',
			'not_null' => true
		),
		array(
			'name' => 'lastname',
			'type' => 'varchar(32)',
			'not_null' => true
		),
		array(
			'name' => 'email',
			'type' => 'varchar(96)',
			'not_null' => true
		),
		array(
			'name' => 'image',
			'type' => 'varchar(255)',
			'not_null' => true
		),
		array(
			'name' => 'code',
			'type' => 'varchar(40)',
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
		)
	),
	'primary' => array(
		'user_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'user_group',
	'field' => array(
		array(
			'name' => 'user_group_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'name',
			'type' => 'varchar(64)',
			'not_null' => true
		),
		array(
			'name' => 'permission',
			'type' => 'text',
			'not_null' => true
		),
		array(
			'name' => 'sort_order',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		)
	),
	'primary' => array(
		'user_group_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);

$tables[] = array(
	'name' => 'zone',
	'field' => array(
		array(
			'name' => 'zone_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'country_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'name',
			'type' => 'varchar(128)',
			'not_null' => true
		),
		array(
			'name' => 'code',
			'type' => 'varchar(32)',
			'not_null' => true
		),
		array(
			'name' => 'status',
			'type' => 'tinyint',
			'not_null' => true,
			'default' => 1
		)
	),
	'primary' => array(
		'zone_id'
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
	'name' => 'zone_to_geo_zone',
	'field' => array(
		array(
			'name' => 'zone_to_geo_zone_id',
			'type' => 'int',
			'not_null' => true,
			'auto_increment' => true
		),
		array(
			'name' => 'country_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'zone_id',
			'type' => 'int',
			'not_null' => true,
			'default' => 0
		),
		array(
			'name' => 'geo_zone_id',
			'type' => 'int',
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
		'zone_to_geo_zone_id'
	),
	'engine' => 'MyISAM',
	'charset' => 'utf8',
	'collate' => 'utf8_general_ci'
);