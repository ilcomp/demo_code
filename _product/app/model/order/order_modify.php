<?php
namespace Model\Order;

class OrderModify extends \Model\Order\Order {
	public function getOrders($data = array()) {
		$filter = isset($data['filter']) && is_array($data['filter']) ? $data['filter'] : array();

		$sql = "SELECT o.*, li.value AS order_status_value, lid.name AS order_status, (SELECT GROUP_CONCAT(value separator ' ') FROM " . DB_PREFIX . "order_custom_field ocf2 LEFT JOIN " . DB_PREFIX . "custom_field cf2 ON (cf2.custom_field_id = ocf2.custom_field_id) WHERE ocf2.order_id = o.order_id AND (cf2.code = 'firstname' OR cf2.code = 'lastname') GROUP BY ocf2.order_id) as account FROM `" . DB_PREFIX . "order` o LEFT JOIN " . DB_PREFIX . "listing_item li ON (li.listing_item_id = o.order_status_id) LEFT JOIN " . DB_PREFIX . "listing_item_description lid ON (lid.listing_item_id = li.listing_item_id AND lid.language_id = o.language_id)";

		if (!empty($filter['order_status'])) {
			$implode = array();

			$order_statuses = explode(',', $filter['order_status']);

			foreach ($order_statuses as $order_status_id) {
				$implode[] = "o.order_status_id = '" . (int)$order_status_id . "'";
			}

			if ($implode) {
				$sql .= " WHERE (" . implode(" OR ", $implode) . ")";
			}
		} elseif (isset($filter['order_status_id']) && $filter['order_status_id'] !== '') {
			$sql .= " WHERE o.order_status_id = '" . (int)$filter['order_status_id'] . "'";
		} else {
			$sql .= " WHERE 1";
		}

		if (!empty($filter['order_id'])) {
			$sql .= " AND o.order_id = '" . (int)$filter['order_id'] . "'";
		}

		if (!empty($filter['store_id'])) {
			$sql .= " AND o.store_id = '" . (int)$filter['store_id'] . "'";
		}

		if (!empty($filter['language_id'])) {
			$sql .= " AND o.language_id = '" . (int)$filter['language_id'] . "'";
		}

		if (!empty($filter['currency_id'])) {
			$sql .= " AND o.currency_id = '" . (int)$filter['currency_id'] . "'";
		}

		if (isset($filter['order_status_id_exclude'])) {
			$sql .= " AND o.order_status_id <> '" . (int)$filter['order_status_id_exclude'] . "'";
		}

		if (!empty($filter['order_status_id_excludes'])) {
			foreach ($filter['order_status_id_excludes'] as &$value) {
				$value = "'" . (int)$value . "'";
			}
			unset($value);

			$sql .= " AND o.order_status_id NOT IN (" . implode(',', $filter['order_status_id_excludes']) . ")";
		}

		if (!empty($filter['date_added'])) {
			$sql .= " AND DATE(o.date_added) = DATE('" . $this->db->escape((string)$filter['date_added']) . "')";
		}

		if (!empty($filter['date_modified'])) {
			$sql .= " AND DATE(o.date_modified) = DATE('" . $this->db->escape((string)$filter['date_modified']) . "')";
		}

		if (!empty($filter['total'])) {
			$sql .= " AND o.total = '" . (float)$filter['total'] . "'";
		}

		if (!empty($filter['account_id']) && $filter['account_id']) {
			$sql .= " AND o.account_id = '" . (float)$filter['account_id'] . "'";
		}

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

		return $query->rows;
	}

	public function getTotalOrders($data = array()) {
		$filter = isset($data['filter']) && is_array($data['filter']) ? $data['filter'] : array();

		$sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order`";

		if (!empty($filter['order_status'])) {
			$implode = array();

			$order_statuses = explode(',', $filter['order_status']);

			foreach ($order_statuses as $order_status_id) {
				$implode[] = "order_status_id = '" . (int)$order_status_id . "'";
			}

			if ($implode) {
				$sql .= " WHERE (" . implode(" OR ", $implode) . ")";
			}
		} elseif (isset($filter['order_status_id']) && $filter['order_status_id'] !== '') {
			$sql .= " WHERE order_status_id = '" . (int)$filter['order_status_id'] . "'";
		} else {
			$sql .= " WHERE 1";
		}

		if (!empty($filter['order_id'])) {
			$sql .= " AND order_id = '" . (int)$filter['order_id'] . "'";
		}

		if (!empty($filter['store_id'])) {
			$sql .= " AND store_id = '" . (int)$filter['store_id'] . "'";
		}

		if (!empty($filter['language_id'])) {
			$sql .= " AND language_id = '" . (int)$filter['language_id'] . "'";
		}

		if (!empty($filter['currency_id'])) {
			$sql .= " AND currency_id = '" . (int)$filter['currency_id'] . "'";
		}

		if (isset($filter['order_status_id_exclude'])) {
			$sql .= " AND order_status_id <> '" . (int)$filter['order_status_id_exclude'] . "'";
		}

		if (!empty($filter['order_status_id_excludes'])) {
			foreach ($filter['order_status_id_excludes'] as &$value) {
				$value = "'" . (int)$value . "'";
			}
			unset($value);

			$sql .= " AND o.order_status_id NOT IN (" . implode(',', $filter['order_status_id_excludes']) . ")";
		}

		if (!empty($filter['date_added'])) {
			$sql .= " AND DATE(date_added) = DATE('" . $this->db->escape((string)$filter['date_added']) . "')";
		}

		if (!empty($filter['date_modified'])) {
			$sql .= " AND DATE(date_modified) = DATE('" . $this->db->escape((string)$filter['date_modified']) . "')";
		}

		if (!empty($filter['total'])) {
			$sql .= " AND total = '" . (float)$filter['total'] . "'";
		}

		if (!empty($filter['account_id']) && $filter['account_id']) {
			$sql .= " AND account_id = '" . (float)$filter['account_id'] . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getTotalSales($data = array()) {
		$filter = isset($data['filter']) && is_array($data['filter']) ? $data['filter'] : array();

		$sql = "SELECT SUM(total) AS total FROM `" . DB_PREFIX . "order`";

		if (!empty($filter['order_status'])) {
			$implode = array();

			$order_statuses = explode(',', $filter['order_status']);

			foreach ($order_statuses as $order_status_id) {
				$implode[] = "order_status_id = '" . (int)$order_status_id . "'";
			}

			if ($implode) {
				$sql .= " WHERE (" . implode(" OR ", $implode) . ")";
			}
		} elseif (isset($filter['order_status_id']) && $filter['order_status_id'] !== '') {
			$sql .= " WHERE order_status_id = '" . (int)$filter['order_status_id'] . "'";
		} else {
			$sql .= " WHERE 1";
		}

		if (!empty($filter['order_id'])) {
			$sql .= " AND order_id = '" . (int)$filter['order_id'] . "'";
		}

		if (!empty($filter['account_id']) && $filter['account_id']) {
			$sql .= " AND account_id = '" . $this->db->escape((string)$filter['account_id']) . "'";
		}

		if (!empty($filter['date_added'])) {
			$sql .= " AND DATE(date_added) = DATE('" . $this->db->escape((string)$filter['date_added']) . "')";
		}

		if (!empty($filter['date_modified'])) {
			$sql .= " AND DATE(date_modified) = DATE('" . $this->db->escape((string)$filter['date_modified']) . "')";
		}

		if (!empty($filter['total'])) {
			$sql .= " AND total = '" . (float)$filter['total'] . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getEmailsByProductsOrdered($products, $start, $end) {
		$implode = array();

		foreach ($products as $product_id) {
			$implode[] = "op.product_id = '" . (int)$product_id . "'";
		}

		$query = $this->db->query("SELECT DISTINCT email FROM `" . DB_PREFIX . "order` o LEFT JOIN " . DB_PREFIX . "order_product op ON (o.order_id = op.order_id) WHERE (" . implode(" OR ", $implode) . ") AND o.order_status_id <> '0' LIMIT " . (int)$start . "," . (int)$end);

		return $query->rows;
	}

	public function getTotalEmailsByProductsOrdered($products) {
		$implode = array();

		foreach ($products as $product_id) {
			$implode[] = "op.product_id = '" . (int)$product_id . "'";
		}

		$query = $this->db->query("SELECT COUNT(DISTINCT email) AS total FROM `" . DB_PREFIX . "order` o LEFT JOIN " . DB_PREFIX . "order_product op ON (o.order_id = op.order_id) WHERE (" . implode(" OR ", $implode) . ") AND o.order_status_id <> '0'");

		return $query->row['total'];
	}
}
