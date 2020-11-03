<?php
if ($_SERVER['HTTP_HOST'] !== $_SERVER['SERVER_NAME'] || empty($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != "on") {
	header("Location: https://" . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"], true, 301);

	exit;
}

// Configuration system
if (is_file('config.php')) {
	require_once('config.php');
}

// Get system
foreach (SYSTEM_LIST as $key => $value) {
	if (preg_match($value, $_SERVER['REQUEST_URI'], $match)) {
		$system = $key;

		$_SERVER['REQUEST_URI'] = '/' . substr($_SERVER['REQUEST_URI'], strlen($match[0]));

		break;
	}
}

if (empty($system)) {
	$system = SYSTEM_DEFAULT;
}

// Configuration application
if (is_file(DIR_PUBLIC .  $system . '/' .  $system . '_const.php')) {
	require_once(DIR_PUBLIC . $system . '/' .  $system . '_const.php');

	// Startup
	require_once(DIR_SYSTEM . 'startup.php');

	start($system);
} elseif (is_file(DIR_APP . 'config/' .  $system . '_const.php')) {
	require_once(DIR_APP . 'config/' .  $system . '_const.php');

	// Startup
	require_once(DIR_SYSTEM . 'startup.php');

	start($system);
} else {
	// 404
	header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true, 404);
	exit();
}