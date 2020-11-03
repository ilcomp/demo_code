<?php
namespace Controller\Cron;

class SxGeo extends \Controller {
	public function update_ip() {
		$url = 'https://sypexgeo.net/files/SxGeoCity_utf8.zip';  // Путь к скачиваемому файлу
		$path = 'sxgeo'; // Каталог в который сохранять dat-файл
		$temp_file = $path . '/SxGeoIPTmp.zip'; // Временный файл архива
		$types = array(
			'Country' =>  'SxGeo.dat',
			'City' =>  'SxGeoCity.dat',
			'Max' =>  'SxGeoMax.dat',
		);

		$log = new \Log('sxgeo.log');

		set_time_limit(600);

		if (!is_dir(DIR_STORAGE . $path)) {
			mkdir(DIR_STORAGE . $path, 0777, true);
			chmod(DIR_STORAGE . $path, 0777);
		}

		preg_match('/(Country|City|Max)/', pathinfo($url, PATHINFO_BASENAME), $match);
		$type = $match[1];
		$file = $types[$match[1]];

		$log->write("IP: Загрузка архива с сервера");
		$fp = fopen(DIR_STORAGE . $temp_file, 'wb');
		$ch = curl_init($url);
		curl_setopt_array($ch, array(
			CURLOPT_FILE => $fp,
			CURLOPT_HTTPHEADER => $this->cache->get('sxgeo.ip_last_update') ? array("If-Modified-Since: " . $this->cache->get('sxgeo.ip_last_update')) : array(),
		));
		if(!curl_exec($ch)) {
			$log->write("IP error: Ошибка скачивания архива с сервера");
			echo ("sxgeoIP error\n");
			return;
		}
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		fclose($fp);

		if ($code == 304) {
			@unlink(DIR_STORAGE . $temp_file);
			$log->write("IP: Архив не обновлялся, с момента предыдущего скачивания");
			echo ("sxgeoIP ok\n");
			return;
		}

		$log->write("IP: Архив с сервера загружен");
		$this->load->model('tool/archive');
		if (!$this->model_tool_archive->unpack_zip($temp_file, $path)) {
			$log->write("IP error: Ошибка открытия архива");
			echo ("sxgeoIP error\n");
			return;
		}

		@unlink(DIR_STORAGE . $temp_file);

		$this->cache->set('sxgeo.ip_last_update', gmdate('D, d M Y H:i:s') . ' GMT');

		$log->write("Успешное обновление файла {$path}{$file}");

		echo ("sxgeoIP ok\n");
		return;
	}

	public function update_db() {
		$url = 'https://sypexgeo.net/files/SxGeo_Info.zip';  // Путь к скачиваемому файлу
		$path = 'sxgeo'; // Каталог в который сохранять dat-файл

		$temp_file = $path . '/SxGeoDBTmp.zip'; // Временный файл архива

		$log = new \Log('sxgeo.log');
		$files = array('country.tsv', 'region.tsv', 'city.tsv');
		set_time_limit(600);

		if (!is_dir(DIR_STORAGE . $path)) {
			mkdir(DIR_STORAGE . $path, 0777, true);
			chmod(DIR_STORAGE . $path, 0777);
		}

		$log->write("IP: Загрузка архива с сервера");
		$fp = fopen(DIR_STORAGE . $temp_file, 'wb');
		$ch = curl_init($url);
		curl_setopt_array($ch, array(
			CURLOPT_FILE => $fp,
			CURLOPT_HTTPHEADER => $this->cache->get('sxgeo.db_last_update') ? array("If-Modified-Since: " . $this->cache->get('sxgeo.db_last_update')) : array(),
		));
		if(!curl_exec($ch)) {
			$log->write("IP error: Ошибка скачивания архива с сервера");
			echo ("sxgeoIP error\n");
			return;
		}
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		fclose($fp);

		if ($code == 304) {
			@unlink(DIR_STORAGE . $temp_file);
			$log->write("IP: Архив не обновлялся, с момента предыдущего скачивания");
			echo ("sxgeoIP ok\n");
			return;
		}
		$log->write("IP: Архив с сервера загружен");

		$this->load->model('tool/archive');

		if (!$this->model_tool_archive->unpack_zip($temp_file, $path)) {
			$log->write("IP error: Ошибка открытия архива");
			echo ("sxgeoIP error\n");
			return;
		}

		foreach ($files as $key => $file) {
			$data = $this->tsv_to_sxgeo(DIR_STORAGE . $path . '/' . $file, $key);

			if ($data) {
				$this->load->model('extension/system/sxgeo');

				$this->model_extension_system_sxgeo->update($data);

				@unlink(DIR_STORAGE . $path . '/' . $file);
			}
		}

		@unlink(DIR_STORAGE . $temp_file);

		$this->cache->set('sxgeo.db_last_update', gmdate('D, d M Y H:i:s') . ' GMT');
		$log->write("DB: БД обновлена");

		echo ("sxgeoDB ok\n");
		return;
	}

	protected function tsv_to_sxgeo($file, $level) {
		$log = new \Log('sxgeo.log');

		if (!is_file($file)) {
			$log->write('TSV error: File does not exist: ' . basename($file));
			echo ("sxgeoTSV error\n");
			return;
		}

		if (($handle = fopen($file,'r')) !== false) {
			$text = fread($handle, filesize($file));
			fclose($handle);
		} else {
			$log->write('TSV error: There was an error opening the file: ' . basename($file));
			echo ("sxgeoTSV error\n");
			return;
		}

		$data = array();
		foreach (explode("\n",$text) as $line) {
			$row = array('sxgeo_id' => '', 'parent_id' => 0, 'name' => '', 'level' => $level, 'country' => '');

			if ($level == 0) {
				foreach (explode("\t",$line) as $key => $value)
					switch ($key) {
						case 0: $row['sxgeo_id'] = (int)$value; break;
						case 1: $row['country'] = $value; break;
						case 3: $row['name'] = $value; break;
					}
			} elseif ($level == 1) {
				foreach (explode("\t",$line) as $key => $value)
					switch ($key) {
						case 0: $row['sxgeo_id'] = (int)$value; break;
						case 2: $row['country'] = $value; break;
						case 3: $row['name'] = $value; break;
					}
			} elseif ($level == 2) {
				foreach (explode("\t",$line) as $key => $value)
					switch ($key) {
						case 0: $row['sxgeo_id'] = (int)$value; break;
						case 1: $row['parent_id'] = (int)$value; break;
						case 3: $row['name'] = $value; break;
					}
			}

			$data[] = $row;
		}

		return $data;
	}
}