<?php
namespace  Model\Core;

class Setting extends \Model {
	public function editSetting($code, $data, $store_id = 0) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE store_id = '" . (int)$store_id . "' AND `code` = '" . $this->db->escape($code) . "'");

		foreach ($data as $key => $value) {
			if (substr($key, 0, strlen($code)) == $code) {
				if (!is_array($value)) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "'");
				} else {
					$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape(json_encode($value, JSON_UNESCAPED_UNICODE)) . "', serialized = '1'");
				}
			}
		}
	}

	public function deleteSetting($code, $store_id = 0) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `code` = '" . $this->db->escape($code) . "'");
	}

	public function getSetting($code, $store_id = 0) {
		$setting_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `code` = '" . $this->db->escape($code) . "'");

		foreach ($query->rows as $result) {
			if (!$result['serialized']) {
				$setting_data[$result['key']] = $result['value'];
			} else {
				$setting_data[$result['key']] = json_decode($result['value'], true);
			}
		}

		return $setting_data;
	}

	public function editSettingValue($code = '', $key = '', $value = '', $store_id = 0) {
		if (!is_array($value)) {
			$this->db->query("UPDATE " . DB_PREFIX . "setting SET `value` = '" . $this->db->escape($value) . "', serialized = '0'  WHERE `code` = '" . $this->db->escape($code) . "' AND `key` = '" . $this->db->escape($key) . "' AND store_id = '" . (int)$store_id . "'");
		} else {
			$this->db->query("UPDATE " . DB_PREFIX . "setting SET `value` = '" . $this->db->escape(json_encode($value, JSON_UNESCAPED_UNICODE)) . "', serialized = '1' WHERE `code` = '" . $this->db->escape($code) . "' AND `key` = '" . $this->db->escape($key) . "' AND store_id = '" . (int)$store_id . "'");
		}
	}

	public function getSettingValue($key, $store_id = 0) {
		$query = $this->db->query("SELECT value FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `key` = '" . $this->db->escape($key) . "'");

		if ($query->num_rows) {
			return $query->row['value'];
		} else {
			return null;
		}
	}

	public function getSettingInfo($request = array()) {
		$data_setting = array();

		// Store
		if (!empty($request['host'])) {
			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "store` WHERE REPLACE(`url`, 'www.', '') = '" . $this->db->escape(str_replace('www.', '', $request['host'])) . "'");

			if ($query->num_rows) {
				$data_setting['store_id'] = (int)$query->row['store_id'];
			} else {
				$data_setting['store_id'] = 0;
			}
		} else {
			$data_setting['store_id'] = 0;
		}

		// Settings
		$data_setting['setting'] = array();

		$sql = "SELECT * FROM " . DB_PREFIX . "setting";

		if ($data_setting['store_id']) {
			$sql .= " WHERE store_id = '0' OR store_id = '" . (int)$data_setting['store_id'] . "' GROUP BY `key` ORDER BY store_id DESC";
		} else {
			$sql .= " WHERE store_id = '0'";
		}

		$query = $this->db->query($sql);

		foreach ($query->rows as $result) {
			if (!$result['serialized']) {
				$data_setting['setting'][$result['key']] = $result['value'];
			} else {
				$data_setting['setting'][$result['key']] = json_decode($result['value'], true);
			}
		}

		// Set time zone
		if (!empty($data_setting['setting']['system_timezone'])) {
			date_default_timezone_set($data_setting['setting']['system_timezone']);
			// Sync PHP and DB time zones.
			$this->db->query("SET time_zone = '" . $this->db->escape(date('P')) . "'");
		}

		// Response output compression level
		if (!empty($data_setting['setting']['system_compression'])) {
			$this->response->setCompression($data_setting['setting']['system_compression']);
		}

		// Get languages
		$this->load->model('localisation/language');

		$data_setting['languages'] = $this->model_localisation_language->getLanguages();

		return $data_setting;
	}
}