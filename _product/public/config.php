<?php
// Version
define('VERSION', '1.0.0.0a');

// HTTP
define('HTTP_SERVER', '');

// System
define('SYSTEM_LIST', array(
	'install' => '/^\/install[^\w]?/',
	'api' => '/^\/api[^\w]?/',
	'admin' => '/^\/admin[^\w]?/',
	'client' => '/^\//'
));
define('SYSTEM_DEFAULT', 'client');

// DIR
define('DIR_APP', '');
define('DIR_PUBLIC', '');
define('DIR_STORAGE', '');

define('DIR_ASSETS', DIR_PUBLIC . 'assets/');
define('DIR_THEME', DIR_PUBLIC . 'theme/');
define('DIR_PUBLIC_IMAGE', DIR_PUBLIC . 'image/');
define('DIR_PUBLIC_FILE', DIR_PUBLIC . 'file/');

define('DIR_IMAGE', DIR_STORAGE . 'image/');
define('DIR_FILE', DIR_STORAGE . 'file/');

// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', '');
define('DB_PASSWORD', '');
define('DB_DATABASE', '');
define('DB_PORT', '3306');
define('DB_PREFIX', '');