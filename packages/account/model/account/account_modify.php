<?php
namespace Model\Account;

class AccountModify extends \Model\Account\Account {
	public function addAccount($data) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "account` SET account_group_id = '" . (int)$data['account_group_id'] . "', language_id = '" . (int)$data['language_id'] . "', `name` = '" . $this->db->escape($data['name']) . "', `status` = '" . (int)$data['status'] . "', date_added = NOW()");

		$account_id = $this->db->getLastId();

		if (isset($data['custom_field'])) {
			foreach ($data['custom_field'] as $custom_field_id => $values) {
				foreach ($values as $language_id => $value) {
					if (!empty($value)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "account_custom_field SET account_id = '" . (int)$account_id . "', custom_field_id = '" . (int)$custom_field_id . "', language_id = '" . (int)$language_id . "', `value` = '" . $this->db->escape(is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value) . "'");
					}
				}
			}
		}

		return $account_id;
	}

	public function editAccount($account_id, $data) {
		$this->db->query("UPDATE `" . DB_PREFIX . "account` SET account_group_id = '" . (int)$data['account_group_id'] . "', language_id = '" . (int)$data['language_id'] . "', `name` = '" . $this->db->escape($data['name']) . "', `status` = '" . (int)$data['status'] . "' WHERE account_id = '" . (int)$account_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "account_custom_field WHERE account_id = '" . (int)$account_id . "'");

		if (isset($data['custom_field'])) {
			foreach ($data['custom_field'] as $custom_field_id => $values) {
				foreach ($values as $language_id => $value) {
					if (!empty($value)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "account_custom_field SET account_id = '" . (int)$account_id . "', custom_field_id = '" . (int)$custom_field_id . "', language_id = '" . (int)$language_id . "', `value` = '" . $this->db->escape(is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value) . "'");
					}
				}
			}
		}
	}

	public function deleteAccount($account_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "account_auth WHERE account_id = '" . (int)$account_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "account_approval WHERE account_id = '" . (int)$account_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "account_ip WHERE account_id = '" . (int)$account_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "account_custom_field WHERE account_id = '" . (int)$account_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "account` WHERE account_id = '" . (int)$account_id . "'");
	}

	public function getAccount($account_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "account WHERE account_id = '" . (int)$account_id . "'");

		return $query->row;
	}

	public function getAccountByCustomField($custom_field_id, $value) {
		$query = $this->db->query("SELECT account_id FROM " . DB_PREFIX . "account_custom_field WHERE custom_field_id = '" . (int)$custom_field_id . "' AND LCASE(`value`) = '" . $this->db->escape(utf8_strtolower($value)) . "' LIMIT 1");

		return $query->row;
	}
	
	public function getAccounts($data = array()) {
		$sql = "SELECT a.*, agd.name AS account_group, (SELECT GROUP_CONCAT(value separator ' ') FROM " . DB_PREFIX . "account_custom_field acf2 LEFT JOIN " . DB_PREFIX . "custom_field cf2 ON (cf2.custom_field_id = acf2.custom_field_id) WHERE acf2.account_id = a.account_id AND (cf2.code = 'firstname' OR cf2.code = 'lastname' OR cf2.code = 'name') GROUP BY acf2.account_id) as account FROM " . DB_PREFIX . "account a LEFT JOIN " . DB_PREFIX . "account_group_description agd ON (a.account_group_id = agd.account_group_id)";

		$sql .= " WHERE agd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_account_group_id'])) {
			$sql .= " AND a.account_group_id = '" . (int)$data['filter_account_group_id'] . "'";
		}

		if (!empty($data['filter_name'])) {
			$sql .= " AND a.name LIKE '" . $this->db->escape((string)$data['filter_name']) . "'";
		}

		if (!empty($data['filter_ip'])) {
			$sql .= " AND a.account_id IN (SELECT account_id FROM " . DB_PREFIX . "account_ip WHERE ip = '" . $this->db->escape((string)$data['filter_ip']) . "')";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND a.status = '" . (int)$data['filter_status'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= " AND DATE(a.date_added) = DATE('" . $this->db->escape((string)$data['filter_date_added']) . "')";
		}

		$sort_data = array(
			'account' => 'account',
			'account_group' => 'account_group',
			'status' => 'a.status',
			'ip' => 'a.ip',
			'date_added' => 'a.date_added'
		);

		if (isset($data['sort']) && isset($sort_data[$data['sort']])) {
			$sql .= " ORDER BY " . $sort_data[$data['sort']];
		} else {
			$sql .= " ORDER BY account";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getTotalAccounts($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "account";

		$implode = array();

		if (!empty($data['filter_account_group_id'])) {
			$implode[] = "account_group_id = '" . (int)$data['filter_account_group_id'] . "'";
		}

		if (!empty($data['filter_name'])) {
			$implode[] = "name LIKE '" . $this->db->escape((string)$data['filter_name']) . "'";
		}

		if (!empty($data['filter_ip'])) {
			$implode[] = "account_id IN (SELECT account_id FROM " . DB_PREFIX . "account_ip WHERE ip = '" . $this->db->escape((string)$data['filter_ip']) . "')";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$implode[] = "status = '" . (int)$data['filter_status'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$implode[] = "DATE(date_added) = DATE('" . $this->db->escape((string)$data['filter_date_added']) . "')";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getTotalAccountsByAccountGroupId($account_group_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "account WHERE account_group_id = '" . (int)$account_group_id . "'");

		return $query->row['total'];
	}

	public function getAccountCustomFields($account_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "account_custom_field WHERE account_id = '" . (int)$account_id . "'");

		$custom_field_value = array();

		foreach ($query->rows as $row) {
			$custom_field_value[$row['custom_field_id']][$row['language_id']] = $row['value'];
		}

		return $custom_field_value;
	}
}