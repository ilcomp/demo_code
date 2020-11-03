<?php
namespace Model\Account;

class Account extends \Model {
	public function addAccount($data) {
		$data['password'] = !empty($data['password']) ? password_hash(html_entity_decode($data['password'], ENT_QUOTES, 'UTF-8'), PASSWORD_DEFAULT) : '';

		if (isset($data['account_group_id']) && is_array($this->config->get('account_group_display')) && in_array($data['account_group_id'], $this->config->get('account_group_display'))) {
			$account_group_id = $data['account_group_id'];
		} else {
			$account_group_id = $this->config->get('account_group_id');
		}

		$types = array('login', 'email', 'telephone');

		while (empty($data['name']) && !empty($types)) {
			$type = array_shift($types);

			if (!empty($data[$type]))
				$data['name'] = $data[$type];
		}

		if (!isset($data['name']))
			$data['name'] = '';

		$this->load->model('account/account_group');

		$account_group_info = $this->model_account_account_group->getAccountGroup($account_group_id);

		$this->db->query("INSERT INTO `" . DB_PREFIX . "account` SET account_group_id = '" . (int)$account_group_id . "', language_id = '" . (int)$this->config->get('config_language_id') . "', `name` = '" . $this->db->escape($data['name']) . "', `password` = '" . $this->db->escape($data['password']) . "', `status` = '" . (int)!$account_group_info['approval'] . "', date_added = NOW()");

		$account_id = $this->db->getLastId();

		if ($account_group_info['approval']) {
			$this->load->model('account/account_approval');

			$this->model_account_account_approval->addAccountApproval(array(
				'account_id' => $account_id,
				'type' => 'account'
			));
		}

		if (isset($data['address'])) {
			foreach ((array)$data['address'] as $address) {
				if ($address)
					$this->db->query("INSERT INTO " . DB_PREFIX . "account_address SET account_id = '" . (int)$account_id . "', address = '" . $this->db->escape((string)$address) . "'");
			}
		}

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
		$this->db->query("DELETE FROM " . DB_PREFIX . "account_address WHERE account_id = '" . (int)$account_id . "'");

		if (isset($data['address'])) {
			foreach ((array)$data['address'] as $address) {
				if ($address)
					$this->db->query("INSERT INTO " . DB_PREFIX . "account_address SET account_id = '" . (int)$account_id . "', address = '" . $this->db->escape((string)$address) . "'");
			}
		}

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

	public function editAccountName($account_id, $name) {
		$this->db->query("UPDATE `" . DB_PREFIX . "account` SET `name` = '" . $this->db->escape($name) . "'  WHERE account_id = '" . (int)$account_id . "'");
	}

	public function editAccountPassword($account_id, $password) {
		$password = $password ? password_hash(html_entity_decode($password, ENT_QUOTES, 'UTF-8'), PASSWORD_DEFAULT) : '';

		$this->db->query("UPDATE " . DB_PREFIX . "account SET `password` = '" . $this->db->escape($password) . "' WHERE account_id = '" . (int)$this->account->getId() . "'");
	}

	public function editAccountLoginPassword($password) {
		$password = $password ? password_hash(html_entity_decode($password, ENT_QUOTES, 'UTF-8'), PASSWORD_DEFAULT) : '';

		$this->db->query("UPDATE " . DB_PREFIX . "account SET `password` = '" . $this->db->escape($password) . "' WHERE account_id = '" . (int)$this->account->getId() . "'");
	}

	public function getAccount($account_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "account` WHERE account_id = '" . (int)$account_id . "'");

		if ($query->num_rows)
			unset($query->row['password']);

		return $query->row;
	}

	public function getAccountAddress($account_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "account_address WHERE account_id = '" . (int)$account_id . "'");

		return array_column($query->rows, 'address');
	}

	public function getAccountCustomFields($account_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "account_custom_field WHERE account_id = '" . (int)$account_id . "'");

		$custom_field_value = array();

		foreach ($query->rows as $row) {
			$custom_field_value[$row['custom_field_id']][$row['language_id']] = $row['value'];
		}

		return $custom_field_value;
	}

	public function getAccountWithName($account_id) {
		$query = $this->db->query("SELECT a.*, (SELECT GROUP_CONCAT(value separator ' ') FROM " . DB_PREFIX . "account_custom_field acf2 LEFT JOIN " . DB_PREFIX . "custom_field cf2 ON (cf2.custom_field_id = acf2.custom_field_id) WHERE acf2.account_id = a.account_id AND (cf2.code = 'firstname' OR cf2.code = 'lastname' OR cf2.code = 'name') GROUP BY acf2.account_id) as account FROM " . DB_PREFIX . "account a WHERE a.account_id = '" . (int)$account_id . "'");

		if ($query->num_rows)
			unset($query->row['password']);

		return $query->row;
	}

	public function getAccountAuthLoginPassword() {
		$query = $this->db->query("SELECT password FROM " . DB_PREFIX . "account WHERE account_id = '" . (int)$this->account->getId() . "' AND status = 1");

		return $query->num_rows ? $query->row['password'] : '';
	}
}