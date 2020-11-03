<?php
// Site
$_['site_url']           = HTTP_APPLICATION;

// Database
$_['db_autostart']       = true;
$_['db_engine']          = DB_DRIVER; // mpdo, mssql, mysql, mysqli or postgre
$_['db_hostname']        = DB_HOSTNAME;
$_['db_username']        = DB_USERNAME;
$_['db_password']        = DB_PASSWORD;
$_['db_database']        = DB_DATABASE;
$_['db_port']            = DB_PORT;

// Session
$_['session_engine']     = 'db';
$_['session_autostart']  = false;

// Template
$_['template_cache']     = true;
$_['theme_default']      = 'client_default';
$_['theme_client_default_status'] = true;

// Error
$_['error_display']      = false;

// Actions
$_['action_pre_action']  = array(
	'startup/startup',
	'startup/error',
	'startup/event',
	//'startup/login'
);

// Action Events
$_['action_event'] = array(
	'controller/*/before' => array(
		'event/language/before'
	),
	'controller/*/after' => array(
		'event/language/after'
	),
	'view/*/before' => array(
		500 => 'event/theme/override',
		501 => 'event/theme',
		998 => 'event/language',
	),
	// 'language/*/after' => array(
	// 	'event/translation'
	// ),
	// 'controller/*/before' => array(
	// 	1000  => 'event/debug/before'
	// ),
	// 'controller/*/after'  => array(
	// 	1000  => 'event/debug/after'
	// )
);