<?php
namespace Model\Account;

class AccountIp extends \Model {
	public function addAccountIp($account_id, $ip) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "account_ip SET account_id = '" . (int)$account_id . "', store_id = '" . (int)$this->config->get('config_store_id') . "', ip = '" . $this->db->escape($ip) . "', date_added = NOW()");
	}

	public function getAccountIps($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "account_ip";

		if (isset($data['filter_account_id'])) {
			$sql .= " WHERE account_id = '" . (int)$data['filter_account_id'] . "'";
		}

		$sql .= " ORDER BY date_added DESC";

		if (isset($data['start']) || isset($data['limit'])) {
			if (!isset($data['start']) || $data['start'] < 0) {
				$data['start'] = 0;
			}

			if (!isset($data['limit']) || $data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);
		
		return $query->rows;
	}

	public function getTotalAccountIps($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "account_ip";

		if (isset($data['filter_account_id'])) {
			$sql .= " WHERE account_id = '" . (int)$data['filter_account_id'] . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getTotalAccountsByIp($ip) {
		$query = $this->db->query("SELECT COUNT(DISTINCT account_id) AS total FROM " . DB_PREFIX . "account_ip WHERE ip = '" . $this->db->escape($ip) . "'");

		return $query->row['total'];
	}
}