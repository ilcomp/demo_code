<?php
namespace  Model\Tool;

class Archive extends \Model {
	public function unpack_zip($file_zip, $path) {
		$path = rtrim($path, '/') . '/';

		$zip = new \ZipArchive;

		if ($zip->open(DIR_STORAGE . $file_zip) === TRUE) {
			if (!is_dir(DIR_STORAGE . $path)) {
				mkdir(DIR_STORAGE . $path, 0777, true);
				chmod(DIR_STORAGE . $path, 0777);
			}

			$zip->extractTo(DIR_STORAGE . $path);

			$zip->close();

			return true;
		} else {
			$this->log->write('Failed zip archive: ' . $file_zip);

			return false;
		}
	}

	public function unpack_rar($file_rar, $path) {
		$path = rtrim($path, '/') . '/';

		$rar = new \RarArchive;

		$rar_arch = $rar->open(DIR_STORAGE . $file_rar);

		if ($rar_arch === TRUE) {
			if (!is_dir(DIR_STORAGE . $path)) {
				mkdir(DIR_STORAGE . $path, 0777, true);
				chmod(DIR_STORAGE . $path, 0777);
			}

			foreach ($rar_arch->getEntries() as $entry) {
				$file = $rar_arch->getEntry($entry);

				$file->extract(DIR_STORAGE . $path);
			}

			$rar->close();

			return true;
		} else {
			$this->log->write('Failed rar archive: ' . $file_zip);

			return false;
		}
	}
}