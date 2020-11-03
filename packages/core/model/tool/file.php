<?php
namespace  Model\Tool;

class File extends \Model {
	private $cache_status = true;
	private $dir_public_file;

	public function link($filename, $path_cache = '') {
		if (!is_file(DIR_FILE . $filename) || substr(str_replace('\\', '/', realpath(DIR_FILE . $filename)), 0, strlen(DIR_FILE)) != str_replace('\\', '/', DIR_FILE)) {
			return;
		}

		$path_placement = $path_cache ? DIR_CACHE . $path_cache : DIR_PUBLIC_FILE;

		$extension = pathinfo($filename, PATHINFO_EXTENSION);

		$file_old = $filename;
		$file_new = utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '.' . $extension;

		$path = dirname($file_new);

		if (!is_dir($path_placement . $path)) {
			@mkdir($path_placement . $path, 0777, true);
		}

		if (!$this->cache_status || !is_file($path_placement . $file_new) || (filemtime(DIR_FILE . $file_old) > filemtime($path_placement . $file_new))) {
			copy(DIR_FILE . $file_old, $path_placement . $file_new);
		}

		return $this->getUrl($file_new);
	}

	public function getUrl($filename, $path_cache = '') {
		if (!$this->dir_public_file)
			$this->dir_public_file = str_replace(DIR_PUBLIC, '', DIR_PUBLIC_FILE);

		return $this->dir_public_file . str_replace(' ', '%20', $filename);
	}
}