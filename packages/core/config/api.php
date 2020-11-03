<?php
// Site
$_['site_url']              = HTTP_APPLICATION;

// Database
$_['db_autostart']          = true;
$_['db_engine']             = DB_DRIVER; // mpdo, mysqli or postgre
$_['db_hostname']           = DB_HOSTNAME;
$_['db_username']           = DB_USERNAME;
$_['db_password']           = DB_PASSWORD;
$_['db_database']           = DB_DATABASE;
$_['db_port']               = DB_PORT;

// Session
$_['session_engine']        = 'db';
$_['session_autostart']     = true;

// Error
$_['error_display']         = false;

// Response
$_['response_content_type'] = 'Content-Type: application/json; charset=utf-8';

// Actions
$_['action_pre_action']     = array(
	'startup/startup',
	'startup/error',
	'startup/session',
	'startup/event',
	'startup/login',
	'startup/permission'
);

// Action Events
$_['action_event'] = array(
	'controller/*/before' => array(
		'event/language/before'
	),
	'controller/*/after' => array(
		'event/language/after'
	),
	// 'language/*/after' => array(
	// 	'event/translation'
	// ),
	// 'model/*/before' => array(
	// 	1000  => 'event/debug/before'
	// ),
	// 'model/*/after'  => array(
	// 	1000  => 'event/debug/after'
	// )
);