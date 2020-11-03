<?php
namespace Model\Registry;

class Account {
	private $account_id;
	private $account_group_id;
	private $type;

	public function __construct($registry) {
		$this->db = $registry->get('db');
		$this->request = $registry->get('request');
		$this->session = $registry->get('session');
		$this->event = $registry->get('event');
		$this->config = $registry->get('config');

		if (isset($this->session->data['registry_account_auth_id'])) {
			$query = $this->db->query("SELECT aa.*, a.account_group_id FROM " . DB_PREFIX . "account_auth aa LEFT JOIN `" . DB_PREFIX . "account` a ON (aa.account_id = a.account_id) WHERE aa.account_auth_id = '" . (int)$this->session->data['registry_account_auth_id'] . "' AND aa.`status` = '1' AND a.`status` = '1'");

			if ($query->num_rows) {
				$this->account_id = $query->row['account_id'];
				$this->account_group_id = $query->row['account_group_id'];
				$this->type = $query->row['type'];

				$this->db->query("UPDATE " . DB_PREFIX . "account_auth SET `ip` = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "' WHERE account_auth_id = '" . (int)$query->row['account_auth_id'] . "'");
			} else {
				$this->logout();
			}
		} elseif (isset($this->session->data['registry_account_id'])) {
			$query = $this->db->query("SELECT account_id, account_group_id FROM `" . DB_PREFIX . "account` WHERE account_id = '" . (int)$this->session->data['registry_account_id'] . "' AND `status` = '1'");

			if ($query->num_rows) {
				$this->account_id = $query->row['account_id'];
				$this->account_group_id = $query->row['account_group_id'];
				$this->type = '';
			} else {
				$this->logout();
			}

		}
	}

	public function login($type, $account_auth_id, $account_id, $password, $override = false) {
		$query = $this->db->query("SELECT aa.*, a.* FROM " . DB_PREFIX . "account_auth aa LEFT JOIN `" . DB_PREFIX . "account` a ON (aa.account_id = a.account_id) WHERE aa.`account_auth_id` = '" . (int)$account_auth_id . "' AND aa.`status` = '1' AND a.`account_id` = '" . (int)$account_id . "' AND a.`status` = '1'");

		if ($query->num_rows) {
			if (!$override) {
				$error = empty($query->row['password']) || !password_verify($password, $query->row['password']);

				$this->event->trigger('model/registry/account/validate/after', array('registry/account/validate/after', $query->row, &$error));

				if ($error) {
					return false;
				}

				if (password_needs_rehash($query->row['password'], PASSWORD_DEFAULT)) {
					$new_password_hashed = password_hash($password, PASSWORD_DEFAULT);

					$this->db->query("UPDATE " . DB_PREFIX . "account SET password = '" . $this->db->escape($new_password_hashed) . "' WHERE account_id = '" . (int)$account_id . "'");
				}

				$this->db->query("INSERT INTO " . DB_PREFIX . "account_ip SET account_id = '" . (int)$account_id . "', store_id = '" . (int)$this->config->get('config_store_id') . "', ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "', date_added = NOW()");
			}

			$this->session->data['registry_account_id'] = $account_id;
			$this->session->data['registry_account_auth_id'] = $account_auth_id;

			$this->account_id = $account_id;
			$this->account_group_id = $query->row['account_group_id'];
			$this->type = $query->row['type'];

			$this->db->query("UPDATE " . DB_PREFIX . "account_auth SET ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "' WHERE account_auth_id = '" . (int)$account_auth_id . "'");

			return true;
		} else {
			return false;
		}
	}

	public function logout() {
		unset($this->session->data['registry_account_auth_id']);
		unset($this->session->data['registry_account_id']);

		$this->account_id = '';
		$this->account_group_id = '';
		$this->type = '';
	}

	public function isLogged() {
		return !!$this->account_id;
	}

	public function getId() {
		return $this->account_id;
	}

	public function getGroupId() {
		return $this->account_group_id;
	}

	public function getType() {
		return $this->type;
	}
}