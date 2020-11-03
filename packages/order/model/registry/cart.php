<?php
namespace Model\Registry;

class Cart {
	private $data = array();

	public function __construct($registry) {
		$this->log = $registry->get('log');
		$this->config = $registry->get('config');
		$this->event = $registry->get('event');
		$this->session = $registry->get('session');
		$this->db = $registry->get('db');

		$this->event->trigger('model/registry/cart/construct', array(&$this));

		if (!$this->account) {
			$this->account = new class() {
				public function getId() {
					return null;
				}
			};
		}

		// Remove all the expired carts with no account ID
		$this->db->query("DELETE c FROM " . DB_PREFIX . "cart c LEFT JOIN " . DB_PREFIX . "session s ON (c.session_id = s.session_id) WHERE (c.api_id > '0' OR c.account_id = '0') AND (c.date_added < DATE_SUB(NOW(), INTERVAL 1 DAY) OR s.session_id IS NULL)");
	}

	public function getProducts() {
		if (!$this->data) {
			$cart_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "cart WHERE api_id = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "' AND account_id = '" . (int)$this->account->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "'");

			foreach ($cart_query->rows as $cart) {
				if ($cart['quantity'] > 0) {
					$product_query = $this->db->query("SELECT p.product_id, pd.name, pd.title, pp.price, pp.price_id, cp.currency_id FROM " . DB_PREFIX . "catalog_product_to_store p2s LEFT JOIN " . DB_PREFIX . "catalog_product p ON (p2s.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "catalog_product_description pd ON (p.product_id = pd.product_id AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "') LEFT JOIN " . DB_PREFIX . "catalog_product_price pp ON (p.product_id = pp.product_id AND pp.price_id = '" . (int)$this->config->get('catalog_price_id') . "') LEFT JOIN " . DB_PREFIX . "catalog_price cp ON (pp.price_id = cp.price_id) WHERE p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND p2s.product_id = '" . (int)$cart['product_id'] . "' AND p.status = '1' AND p.date_available <= NOW()");

					if ($product_query->num_rows) {
						$product_query->row['cart_id'] = $cart['cart_id'];
						$product_query->row['quantity'] = $cart['quantity'];
						$product_query->row['total'] = $product_query->row['price'] * $cart['quantity'];
						$product_query->row['option'] = json_decode($cart['option'], true);

						$this->data[] = $product_query->row;
					} else {
						$this->remove($cart['cart_id']);
					}
				} else {
					$this->remove($cart['cart_id']);
				}
			}

			unset($this->session->data['error_cart']);

			$result = $this->event->trigger('model/registry/cart/getProducts', array(&$this->data));
		}

		return $this->data;
	}

	public function add($product_id, $quantity = 1, $option = array()) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "cart WHERE api_id = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "' AND account_id = '" . (int)$this->account->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "' AND product_id = '" . (int)$product_id . "' AND `option` = '" . $this->db->escape(json_encode($option)) . "'");

		if (!(int)$query->row['total']) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "cart` SET api_id = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "', account_id = '" . (int)$this->account->getId() . "', session_id = '" . $this->db->escape($this->session->getId()) . "', product_id = '" . (int)$product_id . "', `option` = '" . $this->db->escape(json_encode($option)) . "', quantity = '" . (int)$quantity . "', date_added = NOW()");

		} else {
			$this->db->query("UPDATE `" . DB_PREFIX . "cart` SET quantity = (quantity + " . (int)$quantity . ") WHERE api_id = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "' AND account_id = '" . (int)$this->account->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "' AND product_id = '" . (int)$product_id . "' AND `option` = '" . $this->db->escape(json_encode($option)) . "'");
		}

		$this->data = array();
	}

	public function update($cart_id, $quantity) {
		$this->db->query("UPDATE " . DB_PREFIX . "cart SET quantity = '" . (int)$quantity . "' WHERE cart_id = '" . (int)$cart_id . "' AND api_id = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "' AND account_id = '" . (int)$this->account->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "'");

		$this->data = array();
	}

	public function remove($cart_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "cart WHERE cart_id = '" . (int)$cart_id . "' AND api_id = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "' AND account_id = '" . (int)$this->account->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "'");

		$this->data = array();
	}

	public function clear() {
		$this->db->query("DELETE FROM " . DB_PREFIX . "cart WHERE api_id = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "' AND account_id = '" . (int)$this->account->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "'");

		$this->data = array();
	}

	public function getSubTotal() {
		$total = 0;

		foreach ($this->getProducts() as $product) {
			$total += $product['total'];
		}

		return $total;
	}

	public function getTotal() {
		$total = 0;

		foreach ($this->getProducts() as $product) {
			$total += $product['price'] * $product['quantity'];
		}

		return $total;
	}

	public function countProducts() {
		$product_total = 0;

		foreach ($this->getProducts() as $product) {
			$product_total += $product['quantity'];
		}

		return $product_total;
	}

	public function hasProducts() {
		return !empty($this->getProducts());
	}

	public function &__get($key) {
		return $this->{$key};
	}

	public function __set($key, $value) {
		$this->{$key} = $value;
	}

	public function __call($method, $args) {
		if (isset($this->{$method})) {
			return call_user_func_array($this->{$method}->bindTo($this), $args);
		} else {
			$trace = debug_backtrace();

			throw new \Exception('<b>Notice</b>:  Undefined property: Cart::' . $method . ' in <b>' . $trace[1]['file'] . '</b> on line <b>' . $trace[1]['line'] . '</b>');
		}
	}
}
