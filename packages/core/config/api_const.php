<?php
// HTTP
define('HTTP_APPLICATION', HTTP_SERVER . 'api/v1/');
define('HTTP_APPLICATION_ADMIN', HTTP_SERVER . 'admin/');
define('HTTP_APPLICATION_CLIENT', HTTP_SERVER);

// DIR
define('DIR_CONTROLLER', DIR_APP . 'controller/api/');
define('DIR_LANGUAGE', DIR_APP . 'language/api/');
define('DIR_MODEL', DIR_APP . 'model/');
define('DIR_TEMPLATE', DIR_APP . 'view/');

define('DIR_SYSTEM', DIR_APP . 'system/');
define('DIR_CONFIG', DIR_APP . 'config/');

define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');

// Security
define('VALID_IP', array());

// Marketplace API
define('MARKETPLACE_SERVER', 'https://www.opencart.com/');