<?php
namespace Model\Setting;

class SMS extends \Model {
	// type: 1 - login, 2 - registration, 3 - forgotten, 0 - default
	public function addSmsApproval($sms_id, $number, $code, $status_id, $type = 0, $account_id = 0) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "sms SET sms_id = '" . (int)$sms_id . "', `number` = '" . (int)$number . "', code = '" . $this->db->escape(password_hash($code, PASSWORD_DEFAULT)) . "', status_id = '" . (int)$status_id . "', account_id = '" . (int)$account_id . "', date_added = NOW(), `type` = '" . (int)$type . "'");
	}

	public function updateStatusSms($sms_id, $status_id) {
		$this->db->query("UPDATE " . DB_PREFIX . "sms SET status_id = '" . (int)$status_id . "' WHERE sms_id = '" . (int)$sms_id . "'");
	}

	public function getSms($sms_id) {
		$query = $this->db->query("SELECT account_id, status_id, type, date_added FROM " . DB_PREFIX . "sms WHERE sms_id = '" . (int)$sms_id . "' LIMIT 1");

		return $query->row;
	}

	public function getValidateSmsByCode($sms_id, $code) {
		$query = $this->db->query("SELECT sms_id, code FROM " . DB_PREFIX . "sms WHERE (status_id != '9' AND `sms_id` = '" . (int)$sms_id . "' AND (TIME_TO_SEC(NOW()) - TIME_TO_SEC(date_added)) <= '" . (int)$this->config->get('sms_code_lifetime') . "') ORDER BY date_added DESC LIMIT 1");

		if ($query->num_rows) {
			if (password_verify($code, $query->row['code'])) {
				return $query->row['sms_id'];
			} else {
				return 0;
			}
		} else
			return -1;
	}

	public function clearSms() {
		$this->db->query("TRUNCATE " . DB_PREFIX . "sms");
	}
}