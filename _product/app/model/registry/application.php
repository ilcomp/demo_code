<?php
namespace Model\Registry;

class Application {
	private $api_id;
	private $permission_id;
	private $login;

	public function __construct($registry) {
		$this->registry = $registry;

		if (isset($this->session->data['api_id'])) {
			$account_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "api WHERE api_id = '" . (int)$this->session->data['api_id'] . "' AND status = '1'");

			if ($account_query->num_rows) {
				$this->api_id = $account_query->row['api_id'];
				$this->permission_id = $account_query->row['permission_id'];
				$this->login = $account_query->row['login'];

				//$this->db->query("UPDATE " . DB_PREFIX . "api SET ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "' WHERE api_id = '" . (int)$this->session->data['api_id'] . "'");

				$this->setPermission();
			} else {
				$this->logout();
			}
		}
	}

	public function login($login, $password, $override = false) {
		$account_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "api WHERE login = '" . $this->db->escape((string)$login) . "' AND status = '1'");

		if (!$account_query->num_rows)
			return false;

		if (!$override) {
			if (password_verify($password, $account_query->row['password'])) {
				if (password_needs_rehash($account_query->row['password'], PASSWORD_DEFAULT)) {
					$new_password_hashed = password_hash($password, PASSWORD_DEFAULT);
				}
			} else {
				return false;
			}
		}

		$this->session->data['api_id'] = $account_query->row['api_id'];

		$this->api_id = $account_query->row['api_id'];
		$this->permission_id = $account_query->row['permission_id'];
		$this->login = $account_query->row['login'];

		//$this->db->query("UPDATE " . DB_PREFIX . "api SET ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "' WHERE api_id = '" . (int)$this->session->data['api_id'] . "'");

		$this->setPermission();

		return true;
	}

	public function logout() {
		unset($this->session->data['api_id']);

		$this->api_id = '';
		$this->permission_id = '';
		$this->login = '';
	}

	public function isLogged() {
		return $this->api_id;
	}

	public function getId() {
		return $this->api_id;
	}

	public function getPermissionId() {
		return $this->permission_id;
	}

	public function getLogin() {
		return $this->login;
	}

	protected function setPermission() {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "permission_access WHERE permission_id = '" . (int)$this->permission_id . "'");

		foreach ($query->rows as $row) {
			$this->permission->set($row['code'], $row['key']);
		}
	}
}
