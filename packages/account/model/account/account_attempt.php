<?php
namespace Model\Account;

class AccountAttempt extends \Model {
	public function addAccountAttempt($login, $forgotten = 0) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "account_attempt WHERE `login` = '" . $this->db->escape(utf8_strtolower((string)$login)) . "' AND `ip` = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "' AND forgotten = '" . (int)$forgotten . "'");

		if (!$query->num_rows) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "account_attempt SET `login` = '" . $this->db->escape(utf8_strtolower((string)$login)) . "', `ip` = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "', attempt = 1, forgotten = '" . (int)$forgotten . "', date_added = '" . $this->db->escape(date('Y-m-d H:i:s')) . "', date_modified = '" . $this->db->escape(date('Y-m-d H:i:s')) . "'");
		} else {
			$this->db->query("UPDATE " . DB_PREFIX . "account_attempt SET attempt = (attempt + 1), date_modified = '" . $this->db->escape(date('Y-m-d H:i:s')) . "' WHERE account_attempt_id = '" . (int)$query->row['account_attempt_id'] . "' AND forgotten = '" . (int)$forgotten . "'");
		}
	}

	public function deleteAccountAttempts($login, $forgotten = 0) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "account_attempt` WHERE `login` = '" . $this->db->escape(utf8_strtolower($login)) . "' AND forgotten = '" . (int)$forgotten . "'");
	}

	public function getAccountAttempt($login, $forgotten = 0) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "account_attempt` WHERE `login` LIKE '" . $this->db->escape((string)$login) . "' AND forgotten = '" . (int)$forgotten . "' LIMIT 1");

		return $query->row;
	}

	public function getAccountAttempts($data = array()) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "account_attempt` WHERE 1";

		if (!empty($data['filter_login'])) {
			$sql .= " AND `login` LIKE '" . $this->db->escape((string)$data['filter_login']) . "%'";
		}

		$sql .= " ORDER BY `ip` DESC";

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

	public function getTotalAccountAttempts($login) {
		$sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "account_attempt`";

		$implode = array();

		if (!empty($data['filter_login'])) {
			$implode[] = "`login` LIKE '" . $this->db->escape((string)$data['filter_login']) . "%'";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}
}