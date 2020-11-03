<?php
namespace Model\Catalog;

class Category extends \Model {
	public function getCategory($category_id, $data = array()) {
		$filter = isset($data['filter']) && is_array($data['filter']) ? $data['filter'] : array();

		if (!isset($data['language_id']))
			$data['language_id'] = $this->config->get('config_language_id');

		$columns = array();
		$where = array();
		$join = array();

		$where[] = "cc.category_id = '" . (int)$category_id . "'";

		if (isset($filter['status'])) {
			$where[] = "cc.status = '" . (int)$filter['status'] . "'";
		}

		if (!empty($data['language_id'])) {
			$columns[] = "ccd.*";

			$join[] = "LEFT JOIN " . DB_PREFIX . "catalog_category_description ccd ON (cc.category_id = ccd.category_id AND ccd.language_id = '" . (int)$data['language_id'] . "')";
		}

		if (!empty($filter['language_id'])) {
			$join[] = "LEFT JOIN " . DB_PREFIX . "catalog_category_description ccd_f ON (cc.category_id = ccd_f.category_id)";

			$where[] = "ccd_f.language_id = '" . (int)$filter['language_id'] . "'";
		}

		if (isset($filter['store_id'])) {
			$join[] = "LEFT JOIN " . DB_PREFIX . "catalog_category_to_store cc2s ON (cc.category_id = cc2s.category_id)";

			$where[] = "cc2s.store_id = '" . (int)$filter['store_id'] . "'";
		}

		$columns[] = "cc.*";

		if (!empty($data['path']))
			$columns[] = "(SELECT GROUP_CONCAT(ccd1.name ORDER BY level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') FROM " . DB_PREFIX . "catalog_category_path ccp LEFT JOIN " . DB_PREFIX . "catalog_category_description ccd1 ON (ccp.path_id = ccd1.category_id AND ccp.category_id != ccp.path_id) WHERE ccp.category_id = cc.category_id AND ccd1.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY ccp.category_id) AS path";

		$this->event->trigger('model/catalog/category/getCategory/sql', array(&$data, &$join, &$where, &$columns));

		$sql = "SELECT DISTINCT " . implode(', ', $columns) . " FROM " . DB_PREFIX . "catalog_category cc";

		if (!empty($join))
			$sql .= ' ' . implode(' ', $join);

		if (!empty($where))
			$sql .= ' WHERE ' . implode(' AND ', $where);

		$query = $this->db->query($sql);
		
		return $query->row;
	}

	public function getCategoriesCatalog($parent_id = 0) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "catalog_category c LEFT JOIN " . DB_PREFIX . "catalog_category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "catalog_category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c.parent_id = '" . (int)$parent_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND c.status = '1' ORDER BY c.sort_order, LCASE(cd.name)");

		return $query->rows;
	}

	public function getCategories($data = array()) {
		$filter = isset($data['filter']) && is_array($data['filter']) ? $data['filter'] : array();

		$language_id = isset($data['language_id']) ? $data['language_id'] : $this->config->get('config_language_id');

		if (!isset($filter['parent_id']) && isset($data['parent_id'])) {
			$filter['parent_id'] = $data['parent_id'];
		}

		if (!isset($filter['path_name']) && !empty($data['filter_name'])) {
			$filter['path_name'] = $data['filter_name'];
		}

		$sql = "SELECT c.*, cd.*, cp.category_id";

		if (isset($filter['path_name']) || (isset($data['sort']) && $data['sort'] == 'path_name')) {
			$sql .= ", GROUP_CONCAT(cd_p.name ORDER BY cp.level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') AS path_name";
		}

		$sql .= " FROM " . DB_PREFIX . "catalog_category_path cp LEFT JOIN " . DB_PREFIX . "catalog_category c ON (cp.category_id = c.category_id) LEFT JOIN " . DB_PREFIX . "catalog_category c_p ON (cp.path_id = c_p.category_id) LEFT JOIN " . DB_PREFIX . "catalog_category_description cd_p ON (cp.path_id = cd_p.category_id AND cd_p.language_id = '" . (int)$language_id . "') LEFT JOIN " . DB_PREFIX . "catalog_category_description cd ON (cp.category_id = cd.category_id AND cd.language_id = '" . (int)$language_id . "')";

		if (!empty($filter['product'])) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "catalog_category_path cp_p ON (cp.category_id = cp_p.path_id) LEFT JOIN " . DB_PREFIX . "catalog_product_to_category cp2c ON (cp_p.category_id = cp2c.category_id)";
		}

		$where = array();

		if (isset($filter['status'])) {
			$where[] = "c.status = '" . (int)$filter['status'] . "'";
		}

		if (isset($filter['language_id'])) {
			$where[] = "cd.language_id = '" . (int)$filter['language_id'] . "'";
		}

		if (isset($filter['parent_id'])) {
			$where[] = "c.parent_id = '" . (int)$filter['parent_id'] . "'";
		}

		if (!empty($filter['product'])) {
			$where[] = "cp2c.category_id IS NOT NULL";
		}

		if (!empty($filter['name'])) {
			$where[] = "cd.name LIKE '%" . $this->db->escape((string)$filter['name']) . "%'";
		}

		if (!empty($where))
			$sql .= " WHERE " . implode(' AND ', $where);

		$sorts = array();

		$sql .= " GROUP BY cp.category_id";

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$order = " DESC";
		} else {
			$order = " ASC";
		}

		if (!empty($filter['path_name'])) {
			$implode = array();

			$words = explode(' ', trim(preg_replace('/\s+/', ' ', $filter['path_name'])));

			foreach ($words as $word) {
				$implode[] = "path_name LIKE '%" . $this->db->escape((string)$word) . "%'";
			}

			if ($implode) {
				$sql .= " HAVING " . implode(" AND ", $implode) . "";
			}

			$sorts[] = 'cp.level ASC';
		}

		$sort_data = array(
			'name' => 'cd.name',
			'path_name' => 'path_name'
		);

		if (isset($data['sort']) && isset($sort_data[$data['sort']])) {
			$sorts[] = $sort_data[$data['sort']] . " " . $order;
		}

		$sorts[] = "c.sort_order " . $order;

		if (!empty($sorts))
			$sql .= " ORDER BY " . implode(', ', $sorts);

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

	public function getTotalCategories($filter = array()) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "catalog_category c";

		if (isset($filter['store_id'])) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "catalog_category_to_store c2s ON (c.category_id = c2s.category_id)";
		}

		$sql .= " WHERE 1";

		if (isset($filter['parent_id'])) {
			$sql .= " AND c.parent_id = '" . (int)$filter['parent_id'] . "'";
		}

		if (isset($filter['status'])) {
			$sql .= " AND c.status = '" . (int)$filter['status'] . "'";
		}

		if (isset($filter['store_id'])) {
			$sql .= " AND c2s.status = '" . (int)$filter['store_id'] . "' GROUP BY c.category_id";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getCategoryDescriptions($category_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "catalog_category_description WHERE category_id = '" . (int)$category_id . "'");

		return array_column($query->rows, null, 'language_id');
	}

	public function getCategoryStores($category_id) {
		$query = $this->db->query("SELECT store_id FROM " . DB_PREFIX . "catalog_category_to_store WHERE category_id = '" . (int)$category_id . "'");

		return array_column($query->rows, 'store_id');
	}

	public function getCategoryCustomField($category_id, $custom_field_id, $language_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "catalog_category_custom_field WHERE category_id = '" . (int)$category_id . "' AND custom_field_id = '" . (int)$custom_field_id . "' AND language_id = '" . (int)$language_id . "'");

		return ($query->num_rows ? $query->row['value'] : false);
	}

	public function getCategoryCustomFields($category_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "catalog_category_custom_field WHERE category_id = '" . (int)$category_id . "'");

		$custom_field_value = array();

		foreach ($query->rows as $row) {
			$custom_field_value[$row['custom_field_id']][$row['language_id']] = $row['value'];
		}

		return $custom_field_value;
	}

	public function getCategoryPath($category_id) {
		$query = $this->db->query("SELECT cp.*, cd_p.name, cd_p.title FROM " . DB_PREFIX . "catalog_category_path cp LEFT JOIN " . DB_PREFIX . "catalog_category c ON (c.category_id = cp.path_id) LEFT JOIN " . DB_PREFIX . "catalog_category_description cd_p ON (cp.path_id = cd_p.category_id AND cd_p.language_id = '" . (int)$this->config->get('config_language_id') . "') WHERE cp.category_id = '" . (int)$category_id . "' AND c.status = '1' ORDER BY cp.level ASC");

		//SELECT GROUP_CONCAT(cd_p.name ORDER BY level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') FROM " . DB_PREFIX . "catalog_category_path cp LEFT JOIN " . DB_PREFIX . "catalog_category_description cd_p ON (cp.path_id = cd_p.category_id AND cp.category_id != cp.path_id) WHERE cp.category_id = c.category_id AND cd_p.language_id = '" . (int)$filter['language_id'] . "' GROUP BY cp.category_id

		return $query->rows;
	}

	public function getCategoryPathId($category_id) {
		$query = $this->db->query("SELECT cp.path_id FROM " . DB_PREFIX . "catalog_category_path cp LEFT JOIN " . DB_PREFIX . "catalog_category c ON (c.category_id = cp.path_id) WHERE cp.category_id = '" . (int)$category_id . "' AND c.status = '1' ORDER BY cp.level ASC");

		return array_column($query->rows, 'path_id');
	}

	public function getPath($category_id) {
		return implode('_', $this->getCategoryPathId($category_id));
	}

	public function getCategorySeoUrls($category_id) {
		$this->load->model('design/seo_url');

		$results = $this->model_design_seo_url->getSeoUrlsByQuery('catalog_category_id=' . (int)$category_id);

		$seo_url_data = array();

		foreach ($results as $result) {
			$seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $seo_url_data;
	}
}