<?php
if(!function_exists('token')) {
	function token($length = 32) {
		if (!isset($length) || intval($length) < 8) {
			$length = 32;
		}

		if (function_exists('openssl_random_pseudo_bytes')) {
			$token = bin2hex(openssl_random_pseudo_bytes($length));
		} elseif (function_exists('mcrypt_create_iv') && version_compare(phpversion(), '7.1', '<')) {
			$token = bin2hex(mcrypt_create_iv($length, MCRYPT_DEV_URANDOM));
		} elseif (function_exists('random_bytes')) {
			$token = bin2hex(random_bytes($length));
		}

		return substr($token, -$length, $length);
	}
}

if(!function_exists('copyr')) {
	function copyr($source, $dest) {
		// Simple copy for a file
		if (is_file($source)) {
			return copy($source, $dest);
		}

		if (!is_dir($source)) {
			return false;
		}

		// Make destination directory
		if (!is_dir($dest)) {
			mkdir($dest);
		}

		// Loop through the folder
		$dir = dir($source);
		while ($dir && false !== ($entry = $dir->read())) {
			// Skip pointers
			if ($entry == '.' || $entry == '..') {
				continue;
			}

			// Deep copy directories
			if ($dest !== "$source/$entry") {
				copyr("$source/$entry", "$dest/$entry");
			}
		}

		// Clean up
		$dir->close();
		return true;
	}
}