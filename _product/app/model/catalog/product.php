<?php
namespace Model\Catalog;

class Product extends \Model {
	public function getProduct($product_id, $data = array()) {
		$filter = isset($data['filter']) && is_array($data['filter']) ? $data['filter'] : array();

		if (!isset($data['language_id']))
			$data['language_id'] = $this->config->get('config_language_id');

		$columns = array();
		$where = array();
		$join = array();

		$where[] = "p.product_id = '" . (int)$product_id . "'";

		if (isset($filter['status'])) {
			$where[] = "p.status = '" . (int)$filter['status'] . "'";
		}

		if (!empty($filter['date_available'])) {
			$where[] = "p.date_available <= NOW()";
		}

		if (!empty($data['language_id'])) {
			$columns[] = "pd.*";

			$join[] = "LEFT JOIN " . DB_PREFIX . "catalog_product_description pd ON (p.product_id = pd.product_id AND pd.language_id = '" . (int)$data['language_id'] . "')";
		}

		if (!empty($filter['language_id'])) {
			$join[] = "LEFT JOIN " . DB_PREFIX . "catalog_product_description pd_f ON (p.product_id = pd_f.product_id)";

			$where[] = "pd_f.language_id = '" . (int)$filter['language_id'] . "'";
		}

		if (isset($filter['store_id'])) {
			$join[] = "LEFT JOIN " . DB_PREFIX . "catalog_product_to_store p2s ON (p.product_id = p2s.product_id)";

			$where[] = "p2s.store_id = '" . (int)$filter['store_id'] . "'";
		}

		if (isset($filter['price_id'])) {
			$columns[] = "pr.*, pp.*";

			$join[] = "LEFT JOIN " . DB_PREFIX . "catalog_product_price pp ON (p.product_id = pp.product_id AND pp.price_id = '" . (int)$filter['price_id'] . "') LEFT JOIN " . DB_PREFIX . "catalog_price pr ON (pp.price_id = pr.price_id)";
		}

		$columns[] = "p.*";

		$this->event->trigger('model/catalog/product/getProduct/sql', array(&$data, &$join, &$where, &$columns));

		$sql = "SELECT DISTINCT " . implode(', ', $columns) . " FROM " . DB_PREFIX . "catalog_product p";

		if (!empty($join))
			$sql .= ' ' . implode(' ', $join);

		if (!empty($where))
			$sql .= ' WHERE ' . implode(' AND ', $where);

		$query = $this->db->query($sql);

		if (isset($query->row['price']))
			$query->row['price'] = (float)$query->row['price'];

		return $query->row;
	}

	public function getProducts($data = array()) {
		$filter = isset($data['filter']) && is_array($data['filter']) ? $data['filter'] : array();

		$join = array();
		$where = array();
		$order_by = array();

		if (!empty($filter['category_id'])) {
			if (!empty($filter['sub_category'])) {
				$where[] = "ccp.path_id = '" . (int)$filter['category_id'] . "' AND p2c.category_id IS NOT NULL";
			} else {
				$where[] = "p2c.category_id = '" . (int)$filter['category_id'] . "'";
			}
		}

		$join[] = "store";
		$join[] = "description";

		if (!empty($filter['language_id']))
			$where[] = "pd.language_id = '" . (int)$filter['language_id'] . "'";

		if (!empty($filter['status']))
			$where[] = "p.status = '" . (int)$filter['status'] . "'";

		if (!empty($filter['date_available']))
			$where[] = "p.date_available <= NOW()";

		if (!empty($filter['store_id']))
			$where[] = "p2s.store_id = '" . (int)$filter['store_id'] . "'";

		if (isset($filter['price']['from'])) {
			$join[] = "price";

			$where[] = "pp.price >= '" . (float)$filter['price']['from'] . "'";
		}

		if (isset($filter['price']['to'])) {
			$join[] = "price";

			$where[] = "pp.price <= '" . (float)$filter['price']['to'] . "'";
		}

		if (!empty($filter['name'])) {
			$where[] = "LCASE(pd.name) LIKE '" . $this->db->escape(utf8_strtolower($filter['name'])) . "%'";
		}

		if (isset($filter['search'])) {
			$join[] = "custom_field";

			$filter['search'] = utf8_strtolower(trim($filter['search']));

			$search = array();
			$search[] = "LCASE(pd.name) LIKE '%" . $this->db->escape($filter['search']) . "%'";
			$search[] = "LCASE(pd.title) LIKE '%" . $this->db->escape($filter['search']) . "%'";
			$search[] = "LCASE(pd.meta_title) LIKE '%" . $this->db->escape($filter['search']) . "%'";
			$search[] = "LCASE(pd.meta_description) LIKE '%" . $this->db->escape($filter['search']) . "%'";
			$search[] = "LCASE(pd.tag) LIKE '%" . $this->db->escape($filter['search']) . "%'";

			$this->load->model('core/custom_field');

			$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('info_article');

			foreach ($custom_fields as $custom_field) {
				if ((int)$custom_field['search'])
					$search[] = "(pcf.custom_field_id = '" . (int)$custom_field['custom_field_id'] . "' AND LCASE(pcf.value) LIKE '%" . $this->db->escape($filter['search']) . "%')";
			}

			$where[] = "(" . implode(' OR ', $search) . ")";
		}

		if (isset($data['order']) && $data['order'] == 'DESC') {
			$order = " DESC";
		} else {
			$order = " ASC";
		}

		if (isset($data['sort'])) {
			switch ($data['sort']) {
				case 'price':
					$join[] = 'price';

					$order_by[] = "pp.price" . $order; 
				break;
				case 'sort_order': $order_by[] = "p.sort_order" . $order; break;
				case 'date_added': $order_by[] = "p.date_added" . $order; break;
				case 'date_available': $order_by[] = "p.date_available" . $order; break;
			}
		}

		$join[] = 'description';

		$order_by[] = "LCASE(pd.name)" . $order;

		$this->event->trigger('model/catalog/product/getProducts/sql', array(&$data, &$join, &$where, &$order_by));

		$sql = "SELECT p.product_id";

		if (!empty($filter['category_id'])) {
			if (!empty($filter['sub_category'])) {
				$sql .= " FROM " . DB_PREFIX . "catalog_category_path ccp LEFT JOIN " . DB_PREFIX . "catalog_product_to_category p2c ON (ccp.category_id = p2c.category_id)";
			} else {
				$sql .= " FROM " . DB_PREFIX . "catalog_product_to_category p2c";
			}

			$sql .= " LEFT JOIN " . DB_PREFIX . "catalog_product p ON (p2c.product_id = p.product_id)";
		} else {
			$sql .= " FROM " . DB_PREFIX . "catalog_product p";
		}

		$join = array_unique($join);

		foreach ($join as &$value) {
			switch ($value) {
				case 'store':
					$value = "LEFT JOIN " . DB_PREFIX . "catalog_product_to_store p2s ON (p.product_id = p2s.product_id)";
					break;
				case 'price':
					$value = "LEFT JOIN " . DB_PREFIX . "catalog_product_price pp ON (p.product_id = pp.product_id)";
					break;
				case 'description':
					$value = "LEFT JOIN " . DB_PREFIX . "catalog_product_description pd ON (p.product_id = pd.product_id AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "')";
					break;
				case 'custom_field':
					$value = "LEFT JOIN " . DB_PREFIX . "catalog_product_custom_field pcf ON (p.product_id = pcf.product_id)";
					break;
			}
		}
		unset($value);

		if (!empty($join))
			$sql .= ' ' . implode(' ', $join);

		if (!empty($where))
			$sql .= ' WHERE ' . implode(' AND ', $where);

		$sql .= " GROUP BY p.product_id";

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

		$product_data = array();

		foreach (array_column($query->rows, 'product_id') as $product_id) {
			$product_data[$product_id] = $this->getProduct($product_id, $data);	
		}

		return $product_data;
	}

	public function getTotalProducts($data = array()) {
		$filter = isset($data['filter']) && is_array($data['filter']) ? $data['filter'] : array();

		$join = array();
		$where = array();
		$order_by = array();

		if (!empty($filter['category_id'])) {
			if (!empty($filter['sub_category'])) {
				$where[] = "ccp.path_id = '" . (int)$filter['category_id'] . "'";
			} else {
				$where[] = "p2c.category_id = '" . (int)$filter['category_id'] . "'";
			}
		}

		$join[] = "store";
		$join[] = "description";

		if (!empty($filter['language_id']))
			$where[] = "pd.language_id = '" . (int)$filter['language_id'] . "'";

		if (!empty($filter['status']))
			$where[] = "p.status = '" . (int)$filter['status'] . "'";

		if (!empty($filter['date_available']))
			$where[] = "p.date_available <= NOW()";

		if (!empty($filter['store_id']))
			$where[] = "p2s.store_id = '" . (int)$filter['store_id'] . "'";

		if (isset($filter['price']['from'])) {
			$join[] = "price";

			$where[] = "pp.price >= '" . (float)$filter['price']['from'] . "'";
		}

		if (isset($filter['price']['to'])) {
			$join[] = "price";

			$where[] = "pp.price <= '" . (float)$filter['price']['to'] . "'";
		}

		if (!empty($filter['name'])) {
			$where[] = "LCASE(pd.name) LIKE '" . $this->db->escape(utf8_strtolower($filter['name'])) . "%'";
		}

		if (isset($filter['search'])) {
			$join[] = "custom_field";

			$filter['search'] = utf8_strtolower(trim($filter['search']));

			$search = array();
			$search[] = "LCASE(pd.name) LIKE '%" . $this->db->escape($filter['search']) . "%'";
			$search[] = "LCASE(pd.title) LIKE '%" . $this->db->escape($filter['search']) . "%'";
			$search[] = "LCASE(pd.meta_title) LIKE '%" . $this->db->escape($filter['search']) . "%'";
			$search[] = "LCASE(pd.meta_description) LIKE '%" . $this->db->escape($filter['search']) . "%'";
			$search[] = "LCASE(pd.tag) LIKE '%" . $this->db->escape($filter['search']) . "%'";

			$this->load->model('core/custom_field');

			$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('info_article');

			foreach ($custom_fields as $custom_field) {
				if ((int)$custom_field['search'])
					$search[] = "(pcf.custom_field_id = '" . (int)$custom_field['custom_field_id'] . "' AND LCASE(pcf.value) LIKE '%" . $this->db->escape($filter['search']) . "%')";
			}

			$where[] = "(" . implode(' OR ', $search) . ")";
		}

		$this->event->trigger('model/catalog/product/getTotalProducts/sql', array(&$data, &$join, &$where, &$order_by));

		$sql = "SELECT COUNT(DISTINCT p.product_id) AS total";

		if (!empty($filter['category_id'])) {
			if (!empty($filter['sub_category'])) {
				$sql .= " FROM " . DB_PREFIX . "catalog_category_path ccp LEFT JOIN " . DB_PREFIX . "catalog_product_to_category p2c ON (ccp.category_id = p2c.category_id)";
			} else {
				$sql .= " FROM " . DB_PREFIX . "catalog_product_to_category p2c";
			}

			$sql .= " LEFT JOIN " . DB_PREFIX . "catalog_product p ON (p2c.product_id = p.product_id)";
		} else {
			$sql .= " FROM " . DB_PREFIX . "catalog_product p";
		}

		$join = array_unique($join);

		foreach ($join as &$value) {
			switch ($value) {
				case 'store':
					$value = "LEFT JOIN " . DB_PREFIX . "catalog_product_to_store p2s ON (p.product_id = p2s.product_id)";
					break;
				case 'price':
					$value = "LEFT JOIN " . DB_PREFIX . "catalog_product_price pp ON (p.product_id = pp.product_id)";
					break;
				case 'description':
					$value = "LEFT JOIN " . DB_PREFIX . "catalog_product_description pd ON (p.product_id = pd.product_id AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "')";
					break;
				case 'custom_field':
					$value = "LEFT JOIN " . DB_PREFIX . "catalog_product_custom_field pcf ON (p.product_id = pcf.product_id)";
					break;
			}
		}
		unset($value);

		if (!empty($join))
			$sql .= ' ' . implode(' ', $join);

		if (!empty($where))
			$sql .= ' WHERE ' . implode(' AND ', $where);

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getProductImageMain($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "catalog_product_image WHERE product_id = '" . (int)$product_id . "' AND `main` = 1 LIMIT 1");

		if (!$query->num_rows)
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "catalog_product_image WHERE product_id = '" . (int)$product_id . "' ORDER BY sort_order LIMIT 1");

		return $query->row;
	}

	public function getProductImages($product_id, $data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "catalog_product_image WHERE product_id = '" . (int)$product_id . "' ORDER BY main DESC, sort_order ASC, product_image_id ASC";

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

	public function getProductRelated($product_id, $data = array()) {
		$query = $this->db->query("SELECT pr.related_id FROM " . DB_PREFIX . "catalog_product_related pr LEFT JOIN " . DB_PREFIX . "catalog_product p ON (pr.related_id = p.product_id) LEFT JOIN " . DB_PREFIX . "catalog_product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pr.product_id = '" . (int)$product_id . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");

		$product_data = array();

		foreach ($query->rows as $result) {
			$product_data[$result['related_id']] = $this->getProduct($result['related_id'], $data);
		}

		return $product_data;
	}

	public function getProductCategoryByParentId($product_id, $parent_id) {
		$query = $this->db->query("SELECT ccd.* FROM " . DB_PREFIX . "catalog_product_to_category cp2c LEFT JOIN " . DB_PREFIX . "catalog_category cc ON (cp2c.category_id = cc.category_id) LEFT JOIN " . DB_PREFIX . "catalog_category_description ccd ON (cc.category_id = ccd.category_id AND ccd.language_id = '" . (int)$this->config->get('config_language_id') . "') LEFT JOIN " . DB_PREFIX . "catalog_category_to_store cc2s ON (cc.category_id = cc2s.category_id) WHERE cp2c.product_id = '" . (int)$product_id . "' AND cc.parent_id = '" . (int)$parent_id . "' AND cc2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND cc.status = '1' LIMIT 1");

		return $query->row;
	}

	public function getProductCategoryIdMain($product_id) {
		$query = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "catalog_product_to_category WHERE product_id = '" . (int)$product_id . "' AND main = '1' LIMIT 1");

		return ($query->num_rows ? (int)$query->row['category_id'] : 0);
	}

	public function getCategories($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "catalog_product_to_category p2c LEFT JOIN " . DB_PREFIX . "catalog_category c ON (c.category_id = p2c.category_id) WHERE p2c.product_id = '" . (int)$product_id . "' AND c.status = '1' ORDER BY c.sort_order ASC");

		return $query->rows;
	}

	public function getProductCustomField($product_id, $custom_field_id, $language_id) {
		$query = $this->db->query("SELECT value FROM " . DB_PREFIX . "catalog_product_custom_field WHERE product_id = '" . (int)$product_id . "' AND custom_field_id = '" . (int)$custom_field_id . "' AND language_id = '" . (int)$language_id . "'");

		return ($query->num_rows ? $query->row['value'] : false);
	}

	public function getProductCustomFields($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "catalog_product_custom_field WHERE product_id = '" . (int)$product_id . "'");

		$custom_field_value = array();

		foreach ($query->rows as $row) {
			$custom_field_value[$row['custom_field_id']][$row['language_id']] = $row['value'];
		}

		return $custom_field_value;
	}

	public function getProductCustomFieldsValues($custom_field_id, $language_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "catalog_product_custom_field WHERE custom_field_id = '" . (int)$custom_field_id . "' AND language_id = '" . (int)$language_id . "' GROUP BY value");

		return $query->rows;
	}

	public function getLatestProducts($limit) {
		$product_data = $this->cache->get('product.latest.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('account_group_id') . '.' . (int)$limit);

		if (!$product_data) {
			$query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "catalog_product p LEFT JOIN " . DB_PREFIX . "catalog_product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' ORDER BY p.date_added DESC LIMIT " . (int)$limit);

			foreach ($query->rows as $result) {
				$product_data[$result['product_id']] = $this->getProduct($result['product_id']);
			}

			$this->cache->set('product.latest.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('account_group_id') . '.' . (int)$limit, $product_data);
		}

		return $product_data;
	}

	public function getPopularProducts($limit) {
		$product_data = $this->cache->get('product.popular.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('account_group_id') . '.' . (int)$limit);
	
		if (!$product_data) {
			$query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "catalog_product p LEFT JOIN " . DB_PREFIX . "catalog_product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' ORDER BY p.viewed DESC, p.date_added DESC LIMIT " . (int)$limit);
	
			foreach ($query->rows as $result) {
				$product_data[$result['product_id']] = $this->getProduct($result['product_id']);
			}
			
			$this->cache->set('product.popular.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('account_group_id') . '.' . (int)$limit, $product_data);
		}
		
		return $product_data;
	}

	public function getProductPrice($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "catalog_product_price cpp LEFT JOIN " . DB_PREFIX . "catalog_price cp ON (cpp.price_id = cp.price_id) WHERE cp.price_id = '" . (int)$this->config->get('catalog_price_id') . "' AND cpp.product_id = '" . (int)$product_id . "'");

		if ($query->row)
			$query->row['price'] = (float)$query->row['price'];

		return $query->row;
	}

	public function getPriceTotal($data) {
		$result = $this->getProductPrice($data['product_id']);

		if ($result) {
			if (!isset($data['quantity']))
				$data['quantity'] = 0;

			$result['price'] = $this->currency->convert($result['price'], $result['currency_id'], $this->session->data['currency']);

			$result['total'] = (float)$result['price'] * (int)$data['quantity'];

			$result['total'] = $this->currency->convert($result['total'], $result['currency_id'], $this->session->data['currency']);
		}

		return $result;
	}
}