<?php
namespace Model\Account;

class AccountApproval extends \Model {
	public function addAccountApproval($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "account_approval SET account_id = '" . (int)$data['account_id'] . "', `type` = '" . $this->db->escape((string)$data['type']) . "', date_added = NOW()");

		$account_approval_id = $this->db->getLastId();

		return $account_approval_id;
	}

	public function getAccountApprovals($data = array()) {
		$sql = "SELECT a.*, agd.`name` AS account_group, aa.`type`, (SELECT GROUP_CONCAT(value separator ' ') FROM " . DB_PREFIX . "account_custom_field acf2 LEFT JOIN " . DB_PREFIX . "custom_field cf2 ON (cf2.custom_field_id = acf2.custom_field_id) WHERE acf2.account_id = a.account_id AND (cf2.code = 'firstname' OR cf2.code = 'lastname' OR cf2.code = 'name') GROUP BY acf2.account_id) as account FROM `" . DB_PREFIX . "account_approval` aa LEFT JOIN `" . DB_PREFIX . "account` a ON (aa.`account_id` = a.`account_id`) LEFT JOIN `" . DB_PREFIX . "account_group_description` agd ON (a.`account_group_id` = agd.`account_group_id`) WHERE agd.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_login'])) {
			$sql .= " AND a.`login` LIKE '" . $this->db->escape((string)$data['filter_login']) . "%'";
		}

		if (!empty($data['filter_email'])) {
			$sql .= " AND a.`email` LIKE '" . $this->db->escape((string)$data['filter_email']) . "%'";
		}
		
		if (!empty($data['filter_account_group_id'])) {
			$sql .= " AND a.`account_group_id` = '" . (int)$data['filter_account_group_id'] . "'";
		}
		
		if (!empty($data['filter_type'])) {
			$sql .= " AND aa.`type` = '" . $this->db->escape((string)$data['filter_type']) . "'";
		}
		
		if (!empty($data['filter_date_added'])) {
			$sql .= " AND DATE(a.`date_added`) = DATE('" . $this->db->escape((string)$data['filter_date_added']) . "')";
		}

		$sql .= " ORDER BY a.`date_added` DESC";

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
	
	public function getAccountApproval($account_approval_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "account_approval` WHERE `account_approval_id` = '" . (int)$account_approval_id . "'");
		
		return $query->row;
	}
	
	public function getTotalAccountApprovals($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "account_approval` aa LEFT JOIN `" . DB_PREFIX . "account` a ON (aa.`account_id` = a.`account_id`)";

		$implode = array();

		if (!empty($data['filter_login'])) {
			$implode[] = "a.`login` LIKE '" . $this->db->escape((string)$data['filter_login']) . "%'";
		}

		if (!empty($data['filter_email'])) {
			$implode[] = "a.`email` LIKE '" . $this->db->escape((string)$data['filter_email']) . "%'";
		}

		if (!empty($data['filter_account_group_id'])) {
			$implode[] = "a.`account_group_id` = '" . (int)$data['filter_account_group_id'] . "'";
		}
		
		if (!empty($data['filter_type'])) {
			$implode[] = "aa.`type` = '" . $this->db->escape((string)$data['filter_type']) . "'";
		}
		
		if (!empty($data['filter_date_added'])) {
			$implode[] = "DATE(aa.`date_added`) = DATE('" . $this->db->escape((string)$data['filter_date_added']) . "')";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}
	
	public function approveAccount($account_id, $type = '') {
		if ($type)
			$this->db->query("UPDATE `" . DB_PREFIX . "account_auth` SET status = '1' WHERE account_id = '" . (int)$account_id . "' AND `type` = '" . $this->db->escape((string)$type) . "'");
		else
			$this->db->query("UPDATE `" . DB_PREFIX . "account` SET status = '1' WHERE account_id = '" . (int)$account_id . "'");

		$this->db->query("DELETE FROM `" . DB_PREFIX . "account_approval` WHERE account_id = '" . (int)$account_id . "' AND `type` = 'account'");
	}

	public function denyAccount($account_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "account_approval` WHERE account_id = '" . (int)$account_id . "' AND `type` = 'account'");
	}
}