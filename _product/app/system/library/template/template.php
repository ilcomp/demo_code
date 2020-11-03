<?php
namespace Template;
final class Template {
	protected $code;
	protected $data = array();

	public function set($key, $value) {
		$this->data[$key] = $value;
	}

	public function render($filename, $cache = true) {
		$file = DIR_TEMPLATE . $filename . '.tpl';

		if (is_file($file)) {
			$this->code = file_get_contents($file);

			ob_start();

			if (!$cache && function_exists('eval')) {
				extract($this->data);

				echo eval('?>' . $this->code);
			} else {
				extract($this->data);

				include($this->compile($file, $this->code));
			}

			return ob_get_clean();
		} else {
			throw new \Exception('Error: Could not load template ' . $file . '!');
			exit();
		}
	}

	public function compile($file, $code) {
		$hash = hash('sha256', $file . __CLASS__);

		$file = DIR_CACHE . 'template/' . substr($hash, 0, 2) . '/' . $hash . '.php';

		if (!is_file($file)) {
			$directory = dirname($file);

			if (!is_dir($directory)) {
				if (!mkdir($directory, 0777, true)) {
					clearstatcache(true, $directory);
				}
			}

			$handle = fopen($file, 'w+');

			fwrite($handle, $code);

			fclose($handle);
		}

		return $file;
	}
}