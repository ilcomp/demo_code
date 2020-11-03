<?php
namespace Model\Account;

class AccountAuth extends \Model {
	public function addAccountAuth($data) {
		$sql = "INSERT INTO " . DB_PREFIX . "account_auth SET account_id = '" . (int)$data['account_id'] . "', `type` = '" . $this->db->escape((string)$data['type']) . "', `login` = '" . $this->db->escape((string)$data['login']) . "', date_added = NOW()";

		if (!empty($data['status']))
			$sql .= ", `status` = '" . (int)$data['status'] . "'";

		if (!empty($data['ip']))
			$sql .= ", `ip` = '" . $this->db->escape(!isset($data['ip']) ? $data['ip'] : $this->request->server['REMOTE_ADDR']) . "'";

		if (!empty($data['token']))
			$sql .= ", token = '" . $this->db->escape($data['token']) . "'";

		if (!empty($data['expires_in']))
			$sql .= ", expires_in = '" . (int)$data['expires_in'] . "'";

		$this->db->query($sql);

		$account_id = $this->db->getLastId();

		return $account_id;
	}

	public function updateAccountAuthLoginLogin($type, $login) {
		$sql = "UPDATE " . DB_PREFIX . "account_auth SET `login` = '" . $this->db->escape((string)$login) . "'";

		if (isset($data['token']))
			$sql .= ", token = '" . $this->db->escape($data['token']) . "'";

		if (isset($data['expires_in']))
			$sql .= ", expires_in = '" . (int)$data['expires_in'] . "'";

		$sql = " WHERE account_id = '" . (int)$this->account->getId() . "' AND `type` = '" . $this->db->escape((string)$type) . "'";

		$this->db->query($sql);
	}

	public function deleteAccountAuth($account_auth_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "account_auth WHERE account_auth_id = '" . (int)$account_auth_id . "'");
	}

	public function deleteAccountAuthByData($data) {
		$where = array();

		if (isset($data['account_id']))
			$where[] = "account_id = '" . (int)$data['account_id'] . "'";

		if (isset($data['type']))
			$where[] = "type = '" . $this->db->escape((string)$data['type']) . "'";

		if (isset($data['status']))
			$where[] = "status = '" . (int)$data['status'] . "'";

		if (isset($data['login']))
			$where[] = "login = '" . (int)$data['login'] . "'";

		if (!empty($where))
			$this->db->query("DELETE FROM " . DB_PREFIX . "account_auth WHERE " . implode(' AND ', $where) . "");
	}

	public function deleteAccountAuthLoginByData($data) {
		$where = array();

		$where[] = "account_id = '" . (int)$this->account->getId() . "'";

		if (isset($data['type']))
			$where[] = "type = '" . $this->db->escape((string)$data['type']) . "'";

		if (isset($data['status']))
			$where[] = "status = '" . (int)$data['status'] . "'";

		if (isset($data['login']))
			$where[] = "login = '" . (int)$data['login'] . "'";

		if (!empty($where))
			$this->db->query("DELETE FROM " . DB_PREFIX . "account_auth WHERE " . implode(' AND ', $where) . "");
	}

	public function deleteAccountAuthLoginByType($type) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "account_auth WHERE account_id = '" . (int)$this->account->getId() . "' AND `type` = '" . $this->db->escape((string)$type) . "'");
	}

	public function getAccountAuths($account_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "account_auth WHERE account_id = '" . (int)$account_id . "'");

		return $query->rows;
	}

	public function getAccountAuthByData($data) {
		$where = array();

		if (isset($data['account_id']))
			$where[] = "account_id = '" . (int)$data['account_id'] . "'";

		if (isset($data['exclude_login']))
			$where[] = "account_id <> '" . (int)$this->account->getId() . "'";

		if (isset($data['type']))
			$where[] = "type = '" . $this->db->escape((string)$data['type']) . "'";

		if (isset($data['status']))
			$where[] = "status = '" . (int)$data['status'] . "'";

		if (isset($data['login']))
			$where[] = "login = '" . $this->db->escape((string)$data['login']) . "'";

		if (!empty($where)) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "account_auth WHERE " . implode(' AND ', $where) . " LIMIT 1");

			return $query->row;
		}
	}

	public function getAccountAuthLoginByData($data) {
		$where = array();

		$where[] = "account_id = '" . (int)$this->account->getId() . "'";

		if (isset($data['type']))
			$where[] = "type = '" . $this->db->escape((string)$data['type']) . "'";

		if (isset($data['status']))
			$where[] = "status = '" . (int)$data['status'] . "'";

		if (isset($data['login']))
			$where[] = "login = '" . $this->db->escape((string)$data['login']) . "'";

		if (!empty($where)) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "account_auth WHERE " . implode(' AND ', $where) . " LIMIT 1");

			return $query->row;
		}
	}

	public function getAccountAuthLoginByLogin($login, $type = '') {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "account_auth WHERE account_id = '" . (int)$this->account->getId() . "' AND `login` = '" . $this->db->escape((string)$login) . "' AND `type` = '" . $this->db->escape((string)$type) . "' LIMIT 1");

		return $query->row;
	}

	public function getAccountAuthLogin($type = '') {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "account_auth WHERE account_id = '" . (int)$this->account->getId() . "' AND `status` = 1 AND `type` = '" . $this->db->escape((string)$type) . "' LIMIT 1");

		return $query->row;
	}

	public function getAccountAuthLoginFirst() {
		$query = $this->db->query("SELECT account_auth_id FROM " . DB_PREFIX . "account_auth WHERE account_id = '" . (int)$this->account->getId() . "' AND status = 1 LIMIT 1");

		return $query->row;
	}

	public function excludeAccountAuthLogin($login, $type = '') {
		$query = $this->db->query("SELECT 1 FROM " . DB_PREFIX . "account_auth WHERE account_id <> '" . (int)$this->account->getId() . "' AND `login` = '" . $this->db->escape((string)$login) . "' AND `type` = '" . $this->db->escape((string)$type) . "' LIMIT 1");

		return $query->num_rows;
	}
}