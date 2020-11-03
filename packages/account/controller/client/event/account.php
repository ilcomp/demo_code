<?php
namespace Controller\Event;

class Account extends \Controller {
	public function startup() {
		$this->config->set('account_auth_login', array('email', 'telephone'));

		// Accounts
		$account = new \Model\Registry\Account($this->registry);
		$this->registry->set('account', $account);

		// Account Group
		if (isset($this->session->data['account']) && isset($this->session->data['account']['account_group_id'])) {
			// For API calls
			$this->config->set('account_group_id', $this->session->data['account']['account_group_id']);
		} elseif ($this->account->isLogged()) {
			// Logged in accounts
			$this->config->set('account_group_id', $this->account->getGroupId());
		} elseif (isset($this->session->data['guest']) && isset($this->session->data['guest']['account_group_id'])) {
			$this->config->set('account_group_id', $this->session->data['guest']['account_group_id']);
		}

		$this->event->register('controller/common/template/before', new \Action('event/account/template'), 0);
		$this->event->register('controller/order/checkout/reset/after', new \Action('event/account/checkout_reset'), 0);
		$this->event->register('controller/account/account/login/after', new \Action('event/account/checkout_reset'), 0);

		$this->event->register('model/order/order/addOrder/before', new \Action('event/account/addOrder'), 0);
		$this->event->register('model/order/order_history/order_status_id/before', new \Action('event/account/order_status_id'), -100);

		$this->event->register('model/registry/cart/construct', new \Action('event/account/cart'), -1);

		$this->load->controller('event/account_permission');
	}

	public function cart(&$cart) {
		$cart->setAccount = function($registry) {
			$this->account = $registry->get('account');
			$this->session = $registry->get('session');

			if ($this->account->getId()) {
				// We want to change the session ID on all the old items in the accounts cart
				try {
					$this->db->query("UPDATE " . DB_PREFIX . "cart SET session_id = '" . $this->db->escape($this->session->getId()) . "' WHERE api_id = '0' AND account_id = '" . (int)$this->account->getId() . "'");
				} catch (Exception $e) {
					trigger_error($e->getMessage());

					$this->db->query("UPDATE " . DB_PREFIX . "cart SET session_id = '" . $this->db->escape($this->session->getId()) . "' WHERE api_id = '0' AND account_id = '" . (int)$this->account->getId() . "'");
				}

				// Once the account is logged in we want to update the accounts cart
				$cart_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "cart WHERE api_id = '0' AND account_id = '0' AND session_id = '" . $this->db->escape($this->session->getId()) . "'");

				foreach ($cart_query->rows as $cart) {
					$this->db->query("DELETE FROM " . DB_PREFIX . "cart WHERE cart_id = '" . (int)$cart['cart_id'] . "'");

					// The advantage of using $this->add is that it will check if the products already exist and increaser the quantity if necessary.
					$this->add($cart['product_id'], $cart['quantity'], json_decode($cart['option'], true));
				}
			}
		};

		$cart->setAccount($this->registry);
	}

	public function template($route, &$args) {
		if (isset($this->request->get['route']) && strpos($this->request->get['route'], 'account/') === 0)
			$this->document->addMeta('robots', 'noindex, nofollow');

		$args[0]['actions']['account'] = $this->url->link('account/account');
		$args[0]['actions']['login'] = $this->url->link('account/login');
		$args[0]['actions']['logout'] = $this->url->link('account/logout');
		$args[0]['logged'] = $this->account->isLogged();

		// if ($args[0]['logged']) {
		// 	$filename = 'system/account.js';

		// 	if (@filemtime(DIR_TEMPLATE . $filename) > @filemtime(DIR_THEME . $filename)) {
		// 		copy(DIR_TEMPLATE . $filename, DIR_THEME . $filename);
		// 	}

		// 	if (file_exists(DIR_THEME . $filename)) {
		// 		$this->document->addScript('theme/' . $filename . '?v=' . filemtime(DIR_THEME . $filename), 0, 'async');
		// 	}
		// }

		$api = new \Url(HTTP_APPLICATION_API);

		$data_view['action_block'] = $api->link('account/account/is_login');
		//$data_view['action_block'] = $this->url->link('block/account');
		$data_view['action_modal'] = $this->url->link('block/account/modal');

		$args[0]['blocks']['account'] = $this->load->view('block/account', $data_view);
		$args[0]['blocks']['account_login'] = $this->load->view('block/account_login', $data_view);
	}

	public function addOrder($route, &$arg) {
		$this->load->model('account/account');
		$this->load->model('account/account_auth');

		if ($this->account->isLogged()) {
			$account_info = $this->model_account_account->getAccount($this->account->getId());

			$arg[0]['account_id'] = $this->account->getId();
			$arg[0]['account_group_id'] = $account_info['account_group_id'];

			if (!isset($arg[0]['email'])) {
				$account_auth = $this->model_account_account_auth->getAccountAuthLoginByData(array('type' => 'email'));

				if ($account_auth)
					$arg[0]['email'] = $account_auth['login'];
			}

			if (!isset($arg[0]['telephone'])) {
				$account_auth = $this->model_account_account_auth->getAccountAuthLoginByData(array('type' => 'telephone'));

				if ($account_auth)
					$arg[0]['telephone'] = $account_auth['login'];
			}
		} else {
			if (isset($arg[0]['email'])) {
				$account_auth = $this->model_account_account_auth->getAccountAuthByData(array('type' => 'email', 'login' => $arg[0]['email']));

				if ($account_auth)
					$account_info = $this->model_account_account->getAccount($account_auth['account_id']);
			}

			if (!empty($account_info)) {
				$arg[0]['account_id'] = $account_info['account_id'];
				$arg[0]['account_group_id'] = $account_info['account_group_id'];
			} else {
				$arg[0]['account_group_id'] = $this->config->get('account_group_id');

				if ($this->config->get('account_create_on_create')) {
					$account = $arg[0];
					$account['password'] = token(8);

					$account['custom_field'] = array();

					$this->load->model('core/custom_field');

					$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('account_contact');

					foreach ($custom_fields as $custom_field) {
						if (isset($arg[0]['custom_field'][$custom_field['custom_field_id']]))
							$account['custom_field']['custom_field'][$custom_field['custom_field_id']] = $arg[0]['custom_field'][$custom_field['custom_field_id']];
					}

					$order_custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('account_address');

					foreach ($custom_fields as $custom_field) {
						if (isset($arg[0]['custom_field'][$custom_field['custom_field_id']]))
							$account['custom_field']['custom_field'][$custom_field['custom_field_id']] = $arg[0]['custom_field'][$custom_field['custom_field_id']];
					}

					$arg[0]['account_id'] = $this->model_account_account->addAccount($account);
				}
			}
		}
	}

	public function checkout_reset($route, $arg) {
		if ($this->account->isLogged()) {
			$this->load->model('account/account');
			$this->load->model('account/account_auth');

			if (!isset($this->session->data['contact']))
				$this->session->data['contact'] = array();

			if (!isset($this->session->data['address']))
				$this->session->data['address'] = array();

			if (empty($this->session->data['contact']['email'])) {
				$account_auth = $this->model_account_account_auth->getAccountAuthLoginByData(array('type' => 'email'));

				if ($account_auth)
					$this->session->data['contact']['email'] = $account_auth['login'];
			}

			if (empty($this->session->data['contact']['telephone'])) {
				$account_auth = $this->model_account_account_auth->getAccountAuthLoginByData(array('type' => 'telephone'));

				if ($account_auth)
					$this->session->data['contact']['telephone'] = $account_auth['login'];
			}

			if (!isset($this->session->data['contact']['custom_field']))
				$this->session->data['contact']['custom_field'] = array();

			if (!isset($this->session->data['address']['custom_field']))
				$this->session->data['address']['custom_field'] = array();

			$this->load->model('core/custom_field');

			$custom_field_values = $this->model_account_account->getAccountCustomFields($this->account->getId());

			$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('order_contact');

			foreach ($custom_fields as $custom_field) {
				if (isset($custom_field_values[$custom_field['custom_field_id']]))
					$this->session->data['contact']['custom_field'][$custom_field['custom_field_id']] = $custom_field_values[$custom_field['custom_field_id']];
			}

			$order_custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('order_address');

			foreach ($custom_fields as $custom_field) {
				if (isset($custom_field_values[$custom_field['custom_field_id']]))
					$this->session->data['address']['custom_field'][$custom_field['custom_field_id']] = $custom_field_values[$custom_field['custom_field_id']];
			}
		}
	}

	public function order_status_id($route, $data) {
		if ((!isset($data[4]) || !$data[4]) && !empty($data[0]['account_id'])) {
			// Fraud Detection
			$this->load->model('account/account');

			$account_info = $this->model_account_account->getAccount($data[0]['account_id']);

			$data[4] = !empty($account_info['safe']);
		}
	}
}