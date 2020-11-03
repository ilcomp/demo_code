<?php
namespace  Model\Tool;

class Image extends \Model {
	private $webp = 0;

	public function link($filename, $path_cache = '') {
		if (!is_file(DIR_IMAGE . $filename) || substr(str_replace('\\', '/', realpath(DIR_IMAGE . $filename)), 0, strlen(DIR_IMAGE)) != str_replace('\\', '/', DIR_IMAGE)) {
			return;
		}

		$path_placement = $path_cache ? DIR_CACHE . $path_cache : DIR_PUBLIC_IMAGE;

		$extension = pathinfo($filename, PATHINFO_EXTENSION);

		$image_old = $filename;
		$image_new = utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '.' . $extension;

		$path = dirname($image_new);

		if (!is_dir($path_placement . $path)) {
			@mkdir($path_placement . $path, 0777, true);
		}

		if (!is_file($path_placement . $image_new) || (filemtime(DIR_IMAGE . $image_old) > filemtime($path_placement . $image_new))) {
			copy(DIR_IMAGE . $image_old, $path_placement . $image_new);
		}

		return $this->getUrl($image_new, $path_cache);
	}

	public function compress($filename, $path_cache = '') {
		if (!is_file(DIR_IMAGE . $filename) || substr(str_replace('\\', '/', realpath(DIR_IMAGE . $filename)), 0, strlen(DIR_IMAGE)) != str_replace('\\', '/', DIR_IMAGE)) {
			return;
		}

		$path_placement = $path_cache ? DIR_CACHE . $path_cache : DIR_PUBLIC_IMAGE;

		$extension = pathinfo($filename, PATHINFO_EXTENSION);

		$extension = $this->setWEBP($extension);

		$image_old = $filename;

		$image_new = utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . (int)$width . 'x' . (int)$height . '.' . $extension;

		if (!is_file($path_placement . $image_new) || (filemtime(DIR_IMAGE . $image_old) > filemtime($path_placement . $image_new))) {
			list($width_orig, $height_orig, $image_type) = getimagesize(DIR_IMAGE . $image_old);

			if (!in_array($image_type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_WEBP)) || $width_orig == 0 || $height_orig == 0) {
				return $this->link($filename);
			}

			$path = dirname($image_new);

			if (!is_dir($path_placement . $path)) {
				@mkdir($path_placement . $path, 0777, true);
			}

			$image = new \Image(DIR_IMAGE . $image_old);
			$image->save($path_placement . $image_new);
		}

		return $this->getUrl($image_new, $path_cache);
	}

	public function resize($filename, $width, $height, $path_cache = '') {
		if (!is_file(DIR_IMAGE . $filename) || substr(str_replace('\\', '/', realpath(DIR_IMAGE . $filename)), 0, strlen(DIR_IMAGE)) != str_replace('\\', '/', DIR_IMAGE)) {
			return;
		}

		$path_placement = $path_cache ? DIR_CACHE . $path_cache : DIR_PUBLIC_IMAGE;

		$extension = pathinfo($filename, PATHINFO_EXTENSION);

		$extension = $this->setWEBP($extension);

		$image_old = $filename;

		$image_new = utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . (int)$width . 'x' . (int)$height . '.' . $extension;

		if (!is_file($path_placement . $image_new) || (filemtime(DIR_IMAGE . $image_old) > filemtime($path_placement . $image_new))) {
			list($width_orig, $height_orig, $image_type) = getimagesize(DIR_IMAGE . $image_old);

			if (!in_array($image_type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_WEBP)) || $width_orig == 0 || $height_orig == 0 || ($width == 0 && $height == 0)) {
				return $this->link($filename);
			}

			if ($width == 0) {
				$width = $height * $width_orig / $height_orig;
			}

			if ($height == 0) {
				$height = $width * $height_orig / $width_orig;
			}

			$path = dirname($image_new);

			if (!is_dir($path_placement . $path)) {
				@mkdir($path_placement . $path, 0777, true);
			}

			if ($width_orig != $width || $height_orig != $height) {
				$image = new \Image(DIR_IMAGE . $image_old);
				$image->resize($width, $height);
				$image->save($path_placement . $image_new);
			} else {
				$image = new \Image(DIR_IMAGE . $image_old);
				$image->save($path_placement . $image_new);
			}
		}

		return $this->getUrl($image_new, $path_cache);
	}

	public function crop($filename, $width, $height, $path_cache = '') {
		if (!is_file(DIR_IMAGE . $filename) || substr(str_replace('\\', '/', realpath(DIR_IMAGE . $filename)), 0, strlen(DIR_IMAGE)) != str_replace('\\', '/', DIR_IMAGE)) {
			return;
		}

		$path_placement = $path_cache ? DIR_CACHE . $path_cache : DIR_PUBLIC_IMAGE;

		$extension = pathinfo($filename, PATHINFO_EXTENSION);

		$extension = $this->setWEBP($extension);

		$image_old = $filename;
		$image_new = utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . (int)$width . 'x' . (int)$height . '-crop.' . $extension;

		if (!is_file($path_placement . $image_new) || (filemtime(DIR_IMAGE . $image_old) > filemtime($path_placement . $image_new))) {
			list($width_orig, $height_orig, $image_type) = getimagesize(DIR_IMAGE . $image_old);
				 
			if (!in_array($image_type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_WEBP)) || $width_orig == 0 || $height_orig == 0 || ($width == 0 && $height == 0)) {
				return $this->link($filename);
			}

			if ($width == 0) {
				$width = $width_orig;
			}

			if ($height == 0) {
				$height = $height_orig;
			}

			$path = dirname($image_new);

			if (!is_dir($path_placement . $path)) {
				@mkdir($path_placement . $path, 0777, true);
			}

			if ($width_orig != $width || $height_orig != $height) {
				$image = new \Image(DIR_IMAGE . $image_old);

				if ($width_orig / $width > $height_orig / $height) {
					$razn = ($width_orig - $height_orig * $width / $height) / 2;
					$image->crop($razn, 0, $width_orig - $razn, $height_orig);
				} elseif ($width_orig / $width < $height_orig / $height) {
					$razn = ($height_orig - $width_orig * $height / $width) / 2;
					$image->crop(0, $razn, $width_orig, $height_orig - $razn);
				}

				$image->resize($width, $height);

				$image->save($path_placement . $image_new);
			} else {
				$image = new \Image(DIR_IMAGE . $image_old);
				$image->save($path_placement . $image_new);
			}
		}

		return $this->getUrl($image_new, $path_cache);
	}

	public function getUrl($filename, $path_cache = '') {
		if ($path_cache)
			return $path_cache . $filename;
		else
			return str_replace(DIR_PUBLIC, '', DIR_PUBLIC_IMAGE) . str_replace(' ', '%20', $filename);
	}

	public function setWEBP($extension) {
		if (!in_array(strtolower($extension), array('jpg', 'png')))
			return $extension;

		if (!$this->webp) {
			$user_agent = $this->request->server['HTTP_USER_AGENT'];

			if (preg_match('/Firefox\/(\d+)/', $user_agent, $matches)) {
				$this->webp = $matches[1] >= 65 ? 2 : 1;
			} elseif (preg_match('/Edge\/(\d+)/', $user_agent, $matches)) {
				$this->webp = $matches[1] >= 14 ? 2 : 1;
			} elseif (preg_match('/Chrome\/(\d+)/', $user_agent, $matches)) {
				$this->webp = $matches[1] >= 32 ? 2 : 1;
			} elseif (preg_match('/(\d)_\d+ like Mac OS X).+Safari\//', $user_agent, $matches)) {
				$this->webp = $matches[1] >= 14 ? 2 : 1;
			} else {
				$this->webp = 1;
			}

// $log = new \Log('debug.log');$log->write(array($user_agent, $this->webp));
		}

		return ($this->webp > 1 ? 'webp' : $extension);
	}
}