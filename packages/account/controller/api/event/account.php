<?php
namespace Controller\Event;

class Account extends \Controller {
	public function startup() {
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

		$this->event->register('model/registry/cart/construct', new \Action('event/account/cart'), -1);
	}

	public function cart(&$cart) {
		$cart->setAccount = function($registry) {
			$this->account = $registry->get('account');
			$this->session = $registry->get('session');

			if ($this->account->getId()) {
				// We want to change the session ID on all the old items in the accounts cart
				$this->db->query("UPDATE " . DB_PREFIX . "cart SET session_id = '" . $this->db->escape($this->session->getId()) . "' WHERE api_id = '0' AND account_id = '" . (int)$this->account->getId() . "'");

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
}