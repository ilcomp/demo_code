<?php
namespace Model\Order;

class Order extends \Model {
	public function addOrder($data) {
		$sql = "INSERT INTO `" . DB_PREFIX . "order` SET store_id = '" . (int)$this->config->get('config_store_id') . "', store_name = '" . $this->db->escape((string)$this->config->get('config_name')) . "', store_url = '" . $this->db->escape((string)($this->config->get('config_store_id') ? $this->config->get('config_url') : HTTP_SERVER)) . "', total = '" . (float)$data['total'] . "', language_id = '" . (int)$this->config->get('config_language_id') . "', ip = '" . $this->db->escape((string)$this->request->server['REMOTE_ADDR']) . "', date_added = NOW(), date_modified = NOW()";

		if (isset($data['account_id'])) {
			$sql .= ", account_id = '" . (int)$data['account_id'] . "'";
		}

		if (isset($data['account_group_id'])) {
			$sql .= ", account_group_id = '" . (int)$data['account_group_id'] . "'";
		}

		if (isset($data['email'])) {
			$sql .= ", email = '" . $this->db->escape((string)$data['email']) . "'";
		}

		if (isset($data['telephone'])) {
			$sql .= ", telephone = '" . $this->db->escape((string)$data['telephone']) . "'";
		}

		if (isset($data['payment_method'])) {
			$sql .= ", payment_method = '" . $this->db->escape((string)$data['payment_method']) . "'";
		}

		if (isset($data['payment_code'])) {
			$sql .= ", payment_code = '" . $this->db->escape((string)$data['payment_code']) . "'";
		}

		if (isset($data['shipping_method'])) {
			$sql .= ", shipping_method = '" . $this->db->escape((string)$data['shipping_method']) . "'";
		}

		if (isset($data['shipping_code'])) {
			$sql .= ", shipping_code = '" . $this->db->escape((string)$data['shipping_code']) . "'";
		}

		if (isset($data['commission'])) {
			$sql .= ", commission = '" . (float)$data['commission'] . "'";
		}

		$currency = $this->currency->get($this->session->data['currency']);
		if ($currency) {
			$sql .= ", currency_code = '" . $this->db->escape((string)$currency['code']) . "', currency_id = '" . (int)$currency['currency_id'] . "', currency_value = '" . (float)$currency['value'] . "'";
		}

		if (isset($this->request->server['HTTP_X_FORWARDED_FOR'])) {
			$sql .= ", forwarded_ip = '" . $this->db->escape((string)$this->request->server['HTTP_X_FORWARDED_FOR']) . "'";
		} elseif (isset($this->request->server['HTTP_CLIENT_IP'])) {
			$sql .= ", forwarded_ip = '" . $this->db->escape((string)$this->request->server['HTTP_CLIENT_IP']) . "'";
		}

		if (isset($this->request->server['HTTP_USER_AGENT'])) {
			$sql .= ", user_agent = '" . $this->db->escape((string)$this->request->server['HTTP_USER_AGENT']) . "'";
		}

		if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
			$sql .= ", accept_language = '" . $this->db->escape((string)$this->request->server['HTTP_ACCEPT_LANGUAGE']) . "'";
		}

		$this->db->query($sql);

		$order_id = $this->db->getLastId();

		// Custom Field
		if (isset($data['custom_field'])) {
			foreach ($data['custom_field'] as $custom_field_id => $custom_field) {
				foreach ($custom_field as $language_id => $value) {
					if (!empty($value))
						$this->db->query("INSERT INTO " . DB_PREFIX . "order_custom_field SET order_id = '" . (int)$order_id . "', custom_field_id = '" . (int)$custom_field_id . "', language_id = '" . (int)$language_id . "', value = '" . $this->db->escape(is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value) . "'");
				}
			}
		}

		// Products
		if (isset($data['products'])) {
			foreach ($data['products'] as $product) {
				if (!isset($product['model']))
					$product['model'] = '';
				if (!isset($product['sku']))
					$product['sku'] = '';

				$product['option'] = isset($product['option']) ? json_encode($product['option']) : '';

				$this->db->query("INSERT INTO " . DB_PREFIX . "order_product SET order_id = '" . (int)$order_id . "', product_id = '" . (int)$product['product_id'] . "', name = '" . $this->db->escape($product['name']) . "', model = '" . $this->db->escape($product['model']) . "', sku = '" . $this->db->escape($product['sku']) . "', quantity = '" . (int)$product['quantity'] . "', price = '" . (float)$product['price'] . "', total = '" . (float)$product['total'] . "', option = '" . $this->db->escape($product['option']) . "'");

				$order_product_id = $this->db->getLastId();

				if (isset($product['option_data'])) {
					foreach ($product['option_data'] as $option) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "order_option SET order_id = '" . (int)$order_id . "', order_product_id = '" . (int)$order_product_id . "', option_code = '" . $this->db->escape($option['option_id']) . "', option_value = '" . $this->db->escape($option['listing_item_id']) . "', name = '" . $this->db->escape($option['name']) . "', `value` = '" . $this->db->escape($option['value']) . "'");
					}
				}
			}
		}

		// Totals
		if (isset($data['totals'])) {
			foreach ($data['totals'] as $total) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "order_total SET order_id = '" . (int)$order_id . "', code = '" . $this->db->escape($total['code']) . "', title = '" . $this->db->escape($total['title']) . "', `value` = '" . (float)$total['value'] . "', sort_order = '" . (int)$total['sort_order'] . "'");
			}
		}

		// Additionally
		if (isset($data['additionally'])) {
			foreach ($data['additionally'] as $code => $additionally) {
				if (is_array($additionally)) {
					foreach ($additionally as $key => $value) {
						if ($value)
							$this->db->query("INSERT INTO " . DB_PREFIX . "order_additionally SET order_id = '" . (int)$order_id . "', code = '" . $this->db->escape($code . '.' . $key) . "', value = '" . $this->db->escape($value) . "'");
					}
				} elseif ($additionally) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "order_additionally SET order_id = '" . (int)$order_id . "', code = '" . $this->db->escape($code) . "', value = '" . $this->db->escape($additionally) . "'");
				}
			}
		}

		return $order_id;
	}

	public function editOrder($order_id, $data) {
		$sql = "UPDATE `" . DB_PREFIX . "order` SET store_id = '" . (int)$this->config->get('config_store_id') . "', store_name = '" . $this->db->escape((string)$this->config->get('config_name')) . "', store_url = '" . $this->db->escape((string)($data['store_id'] ? $this->config->get('config_url') : HTTP_SERVER)) . "', total = '" . (float)$data['total'] . "', date_modified = NOW()";

		if (isset($data['account_id'])) {
			$sql .= ", account_id = '" . (int)$data['account_id'] . "'";
		}

		if (isset($data['account_group_id'])) {
			$sql .= ", account_group_id = '" . (int)$data['account_group_id'] . "'";
		}

		if (isset($data['email'])) {
			$sql .= ", email = '" . $this->db->escape((string)$data['email']) . "'";
		}

		if (isset($data['telephone'])) {
			$sql .= ", telephone = '" . $this->db->escape((string)$data['telephone']) . "'";
		}

		if (isset($data['payment_method'])) {
			$sql .= ", payment_method = '" . $this->db->escape((string)$data['payment_method']) . "'";
		}

		if (isset($data['payment_code'])) {
			$sql .= ", payment_code = '" . $this->db->escape((string)$data['payment_code']) . "'";
		}

		if (isset($data['shipping_method'])) {
			$sql .= ", shipping_method = '" . $this->db->escape((string)$data['shipping_method']) . "'";
		}

		if (isset($data['shipping_code'])) {
			$sql .= ", shipping_code = '" . $this->db->escape((string)$data['shipping_code']) . "'";
		}

		if (isset($data['commission'])) {
			$sql .= ", commission = '" . (float)$data['commission'] . "'";
		}

		$sql .= " WHERE order_id = '" . (int)$order_id . "'";

		$this->db->query($sql);

		$this->db->query("DELETE FROM " . DB_PREFIX . "order_custom_field WHERE order_id = '" . (int)$order_id . "'");

		// Custom Field
		if (isset($data['custom_field'])) {
			foreach ($data['custom_field'] as $custom_field_id => $custom_field) {
				foreach ($custom_field as $language_id => $value) {
					if (!empty($value))
						$this->db->query("INSERT INTO " . DB_PREFIX . "order_custom_field SET order_id = '" . (int)$order_id . "', custom_field_id = '" . (int)$custom_field_id . "', language_id = '" . (int)$language_id . "', value = '" . $this->db->escape(is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value) . "'");
				}
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "'");

		// Products
		if (isset($data['products'])) {
			foreach ($data['products'] as $product) {
				if (!isset($product['model']))
					$product['model'] = '';
				if (!isset($product['sku']))
					$product['sku'] = '';

				$product['option'] = isset($product['option']) ? json_encode($product['option']) : '';

				$this->db->query("INSERT INTO " . DB_PREFIX . "order_product SET order_id = '" . (int)$order_id . "', product_id = '" . (int)$product['product_id'] . "', name = '" . $this->db->escape($product['name']) . "', model = '" . $this->db->escape($product['model']) . "', sku = '" . $this->db->escape($product['sku']) . "', quantity = '" . (int)$product['quantity'] . "', price = '" . (float)$product['price'] . "', total = '" . (float)$product['total'] . "', option = '" . $this->db->escape($product['option']) . "'");

				$order_product_id = $this->db->getLastId();

				if (isset($product['option_data'])) {
					foreach ($product['option_data'] as $option) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "order_option SET order_id = '" . (int)$order_id . "', order_product_id = '" . (int)$order_product_id . "', option_code = '" . $this->db->escape($option['option_id']) . "', option_value = '" . $this->db->escape($option['listing_item_id']) . "', name = '" . $this->db->escape($option['name']) . "', `value` = '" . $this->db->escape($option['value']) . "'");
					}
				}
			}
		}

		// Totals
		$this->db->query("DELETE FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "'");

		if (isset($data['totals'])) {
			foreach ($data['totals'] as $total) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "order_total SET order_id = '" . (int)$order_id . "', code = '" . $this->db->escape($total['code']) . "', title = '" . $this->db->escape($total['title']) . "', `value` = '" . (float)$total['value'] . "', sort_order = '" . (int)$total['sort_order'] . "'");
			}
		}

		// Additionally
		if (isset($data['additionally'])) {
			$this->db->query("DELETE FROM " . DB_PREFIX . "order_additionally WHERE order_id = '" . (int)$order_id . "'");

			foreach ($data['additionally'] as $code => $additionally) {
				if (is_array($additionally)) {
					foreach ($additionally as $key => $value) {
						if ($value)
							$this->db->query("INSERT INTO " . DB_PREFIX . "order_additionally SET order_id = '" . (int)$order_id . "', code = '" . $this->db->escape($code . '.' . $key) . "', value = '" . $this->db->escape($value) . "'");
					}
				} elseif ($additionally) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "order_additionally SET order_id = '" . (int)$order_id . "', code = '" . $this->db->escape($code) . "', value = '" . $this->db->escape($additionally) . "'");
				}
			}
		}
	}

	public function deleteOrder($order_id) {
		// Void the order first
		$this->load->model('order/order_history');

		$this->model_order_order_history->addOrderHistory($order_id, 0);

		$this->db->query("DELETE FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "order_custom_field` WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "order_product` WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "order_option` WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "order_additionally` WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "order_total` WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "order_history` WHERE order_id = '" . (int)$order_id . "'");
	}

	public function getOrder($order_id, $data = array()) {
		$filter = isset($data['filter']) && is_array($data['filter']) ? $data['filter'] : array();

		$sql = "SELECT *, (SELECT lid.name FROM " . DB_PREFIX . "listing_item_description lid WHERE lid.listing_item_id = o.order_status_id AND lid.language_id = o.language_id) AS order_status FROM `" . DB_PREFIX . "order` o";

		$where = array();

		$where[] = "o.order_id = '" . (int)$order_id . "'";

		if (!empty($filter['store_id'])) {
			$where[] = "o.store_id = '" . (int)$filter['store_id'] . "'";
		}

		if (!empty($filter['account_id'])) {
			$where[] = "o.account_id = '" . (float)$filter['account_id'] . "'";
		}

		if (!empty($where))
			$sql .= ' WHERE ' . implode(' AND ', $where);

		$order_query = $this->db->query($sql);

		if ($order_query->num_rows) {
			$custom_field_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_custom_field` WHERE order_id = '" . (int)$order_query->row['order_id'] . "'");

			$order_query->row['custom_field'] = array();

			foreach ($custom_field_query->rows as $row) {
				$order_query->row['custom_field'][$row['custom_field_id']][$row['language_id']] = $row['value'];
			}

			$this->load->model('localisation/language');

			$language_info = $this->model_localisation_language->getLanguage($order_query->row['language_id']);

			$order_query->row['language_code'] = $language_info ? $language_info['code'] : $this->config->get('config_language');

			$order_query->row['total'] = (float)$order_query->row['total'];

			return $order_query->row;
		} else {
			return false;
		}
	}

	public function getOrderProducts($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

		foreach ($query->rows as &$row) {
			$row['price'] = (float)$row['price'];
			$row['total'] = (float)$row['total'];
			$row['option'] = json_decode($row['option'], true);
			$row['option_data'] = $this->getOrderOptionsWithListing($row['order_id'], $row['order_product_id']);

			foreach ($row['option_data'] as $option_data) {
				unset($row['option'][$option_data['option_code']]);
			}
		}
		unset($row);

		return $query->rows;
	}

	public function getOrderOptionsWithListing($order_id, $order_product_id) {
		$query = $this->db->query("SELECT oo.*, li.listing_item_id, li.value as value_value, li.image as value_image, lid.name as value_name FROM " . DB_PREFIX . "order_option oo LEFT JOIN " . DB_PREFIX . "listing_item li ON (oo.option_code = li.listing_item_id) LEFT JOIN " . DB_PREFIX . "listing_item_description lid ON (li.listing_item_id = lid.listing_item_id AND lid.language_id = '" . $this->db->escape((string)$this->config->get('config_language_id')) . "') WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product_id . "'");

		return $query->rows;
	}

	public function getOrderOptions($order_id, $order_product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product_id . "'");

		return $query->rows;
	}

	public function getOrderTotals($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' ORDER BY sort_order ASC");

		foreach ($query->rows as &$row) {
			$row['value'] = (float)$row['value'];
		}
		unset($row);

		return $query->rows;
	}

	public function getOrderAdditionallys($order_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_additionally` WHERE order_id = '" . (int)$order_id . "'");

		$data_additionally = array();

		foreach ($query->rows as $row) {
			$code = explode('.', $row['code']);

			if (isset($code[1])) {
				$data_additionally[$code[0]][$code[1]] = $row['value'];
			} else {
				$data_additionally[$code[0]] = $row['value'];
			}
		}
		
		return $data_additionally;
	}

	public function getTotalOrderProducts($order_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

		return (int)$query->row['total'];
	}

	public function getOrderCustomFields($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_custom_field WHERE order_id = '" . (int)$order_id . "'");

		$custom_field_value = array();

		foreach ($query->rows as $row) {
			$custom_field_value[$row['custom_field_id']][$row['language_id']] = $row['value'];
		}

		return $custom_field_value;
	}

	public function getOrderByAccount($account_id, $order_id) {
		$order_query = $this->db->query("SELECT o.*, lid.name as status FROM `" . DB_PREFIX . "order` o LEFT JOIN " . DB_PREFIX . "listing_item_description lid ON (o.order_status_id = lid.listing_item_id) WHERE o.order_id = '" . (int)$order_id . "' AND o.account_id = '" . (int)$account_id . "' AND o.account_id != '0' AND o.order_status_id > '0'");

		if ($order_query->num_rows) {
			$custom_field_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_custom_field` WHERE order_id = '" . (int)$order_query->row['order_id'] . "'");

			$order_query->row['custom_field'] = array();

			foreach ($custom_field_query->rows as $row) {
				$order_query->row['custom_field'][$row['custom_field_id']][$row['language_id']] = $row['value'];
			}

			$this->load->model('localisation/language');

			$language_info = $this->model_localisation_language->getLanguage($order_query->row['language_id']);

			$order_query->row['language_code'] = $language_info ? $language_info['code'] : $this->config->get('config_language');

			return $order_query->row;
		} else {
			return false;
		}
	}

	public function getOrdersByAccount($account_id, $start = 0, $limit = 20) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 1;
		}

		$query = $this->db->query("SELECT o.*, lid.name as status FROM `" . DB_PREFIX . "order` o LEFT JOIN " . DB_PREFIX . "listing_item_description lid ON (o.order_status_id = lid.listing_item_id) WHERE o.account_id = '" . (int)$account_id . "' AND o.order_status_id > '0' AND o.store_id = '" . (int)$this->config->get('config_store_id') . "' AND lid.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY o.order_id DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	public function getTotalOrdersByAccount($account_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` o WHERE o.account_id = '" . (int)$account_id . "' AND o.order_status_id > '0' AND o.store_id = '" . (int)$this->config->get('config_store_id') . "'");

		return (int)$query->row['total'];
	}

	public function getOrders($data = array()) {
		$filter = isset($data['filter']) && is_array($data['filter']) ? $data['filter'] : array();

		if (!isset($filter['store_id']) && isset($data['filter_store_id']))
			$filter['store_id'] = $data['filter_store_id'];

		if (!isset($filter['account_id']) && isset($data['filter_account_id']))
			$filter['store_id'] = $data['filter_account_id'];

		$sql = "SELECT o.*, li.value AS order_status_value, lid.name AS order_status FROM `" . DB_PREFIX . "order` o LEFT JOIN " . DB_PREFIX . "listing_item li ON (o.order_status_id = li.listing_item_id) LEFT JOIN " . DB_PREFIX . "listing_item_description lid ON (lid.listing_item_id = li.listing_item_id AND lid.language_id = o.language_id)";

		$where = array();

		if (!empty($filter['store_id'])) {
			$where[] = "o.store_id = '" . (int)$filter['store_id'] . "'";
		}

		if (!empty($filter['account_id'])) {
			$where[] = "o.account_id = '" . (float)$filter['account_id'] . "'";
		}

		if (!empty($where))
			$sql .= ' WHERE ' . implode(' AND ', $where);

		$sql .= " GROUP BY o.order_id";

		if (isset($data['order']) && $data['order'] == 'DESC') {
			$order = " DESC";
		} else {
			$order = " ASC";
		}

		if (isset($data['sort'])) {
			switch ($data['sort']) {
				case 'order_id': $order_by[] = "o.order_id" . $order; break;
				case 'order_status': $order_by[] = "order_status" . $order; break;
				case 'date_added': $order_by[] = "o.date_added" . $order; break;
				case 'date_modified': $order_by[] = "o.date_modified" . $order; break;
				case 'total': $order_by[] = "o.total" . $order; break;
				case 'account': $order_by[] = "account" . $order; break;
			}
		}

		$order_by[] = "o.order_id" . $order;

		if (!empty($order_by))
			$sql .= ' ORDER BY ' . implode(', ', $order_by);

		if (isset($data['start']) || isset($data['limit'])) {
			if (!isset($data['start']) || $data['start'] < 0) {
				$data['start'] = 0;
			}

			if (!isset($data['limit']) || $data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		foreach ($query->rows as &$row) {
			$row['total'] = (float)$row['total'];
		}
		unset($row);

		return $query->rows;
	}

	public function getTotalOrders($data = array()) {
		$filter = isset($data['filter']) && is_array($data['filter']) ? $data['filter'] : array();

		if (!isset($filter['store_id']) && isset($data['filter_store_id']))
			$filter['store_id'] = $data['filter_store_id'];

		if (!isset($filter['account_id']) && isset($data['filter_account_id']))
			$filter['store_id'] = $data['filter_account_id'];

		$sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order`";

		if (!empty($data['filter_order_status'])) {
			$implode = array();

			$order_statuses = explode(',', $data['filter_order_status']);

			foreach ($order_statuses as $order_status_id) {
				$implode[] = "order_status_id = '" . (int)$order_status_id . "'";
			}

			if ($implode) {
				$sql .= " WHERE (" . implode(" OR ", $implode) . ")";
			}
		} elseif (isset($data['filter_order_status_id']) && $data['filter_order_status_id'] !== '') {
			$sql .= " WHERE order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
		} else {
			$sql .= " WHERE 1";
		}

		if (!empty($data['filter_order_id'])) {
			$sql .= " AND order_id = '" . (int)$data['filter_order_id'] . "'";
		}

		if (!empty($filter['store_id'])) {
			$sql .= " AND store_id = '" . (int)$filter['store_id'] . "'";
		}

		if (!empty($filter['account_id'])) {
			$sql .= " AND account_id = '" . (float)$filter['account_id'] . "'";
		}

		if (!empty($data['filter_language_id'])) {
			$sql .= " AND language_id = '" . (int)$data['filter_language_id'] . "'";
		}

		if (!empty($data['filter_currency_id'])) {
			$sql .= " AND currency_id = '" . (int)$data['filter_currency_id'] . "'";
		}

		if (isset($data['filter_order_status_id_exclude'])) {
			$sql .= " AND order_status_id <> '" . (int)$data['filter_order_status_id_exclude'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= " AND DATE(date_added) = DATE('" . $this->db->escape((string)$data['filter_date_added']) . "')";
		}

		if (!empty($data['filter_date_modified'])) {
			$sql .= " AND DATE(date_modified) = DATE('" . $this->db->escape((string)$data['filter_date_modified']) . "')";
		}

		if (!empty($data['filter_total'])) {
			$sql .= " AND total = '" . (float)$data['filter_total'] . "'";
		}

		$query = $this->db->query($sql);

		return (int)$query->row['total'];
	}
}