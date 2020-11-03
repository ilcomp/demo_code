<?php
namespace Model\Info;

class Category extends \Model {
	public function getCategory($category_id, $data = array()) {
		$filter = isset($data['filter']) && is_array($data['filter']) ? $data['filter'] : array();

		if (!isset($data['language_id']))
			$data['language_id'] = $this->config->get('config_language_id');

		$columns = array();
		$where = array();
		$join = array();

		$where[] = "ic.category_id = '" . (int)$category_id . "'";

		if (isset($filter['status'])) {
			$where[] = "ic.status = '" . (int)$filter['status'] . "'";
		}

		if (!empty($data['language_id'])) {
			$columns[] = "icd.*";

			$join[] = "LEFT JOIN " . DB_PREFIX . "info_category_description icd ON (ic.category_id = icd.category_id AND icd.language_id = '" . (int)$data['language_id'] . "')";
		}

		if (!empty($filter['language_id'])) {
			$join[] = "LEFT JOIN " . DB_PREFIX . "info_category_description icd_f ON (ic.category_id = icd_f.category_id)";

			$where[] = "icd_f.language_id = '" . (int)$filter['language_id'] . "'";
		}

		if (isset($filter['store_id'])) {
			$join[] = "LEFT JOIN " . DB_PREFIX . "info_category_to_store ic2s ON (ic.category_id = ic2s.category_id)";

			$where[] = "ic2s.store_id = '" . (int)$filter['store_id'] . "'";
		}

		$columns[] = "ic.*";

		if (!empty($data['path']))
			$columns[] = "(SELECT GROUP_CONCAT(icd1.name ORDER BY level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') FROM " . DB_PREFIX . "info_category_path icp LEFT JOIN " . DB_PREFIX . "info_category_description icd1 ON (icp.path_id = icd1.category_id AND icp.category_id != icp.path_id) WHERE icp.category_id = ic.category_id AND icd1.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY icp.category_id) AS path";

		$this->event->trigger('model/info/category/getCategory/sql', array(&$data, &$join, &$where, &$columns));

		$sql = "SELECT DISTINCT " . implode(', ', $columns) . " FROM " . DB_PREFIX . "info_category ic";

		if (!empty($join))
			$sql .= ' ' . implode(' ', $join);

		if (!empty($where))
			$sql .= ' WHERE ' . implode(' AND ', $where);

		$query = $this->db->query($sql);

		if ($query->num_rows) {
			$query->row['setting'] = json_decode($query->row['setting'], true);
		}

		return $query->row;
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

		$sql .= " FROM " . DB_PREFIX . "info_category_path cp LEFT JOIN " . DB_PREFIX . "info_category c ON (cp.category_id = c.category_id) LEFT JOIN " . DB_PREFIX . "info_category c_p ON (cp.path_id = c_p.category_id) LEFT JOIN " . DB_PREFIX . "info_category_description cd_p ON (cp.path_id = cd_p.category_id AND cd_p.language_id = '" . (int)$language_id . "') LEFT JOIN " . DB_PREFIX . "info_category_description cd ON (cp.category_id = cd.category_id AND cd.language_id = '" . (int)$language_id . "')";

		$where = array();

		if (isset($filter['status'])) {
			$where[] = "c.status = '" . (int)$filter['status'] . "'";
		}

		if (isset($filter['language_id'])) {
			$where[] .= " cd.language_id = '" . (int)$filter['language_id'] . "'";
		}

		if (isset($filter['parent_id'])) {
			$where[] .= " c.parent_id = '" . (int)$filter['parent_id'] . "'";
		}

		if (!empty($filter['name'])) {
			$where[] = "cd.name LIKE '%" . $this->db->escape((string)$filter['name']) . "%'";
		}

		if (!empty($where))
			$sql .= " WHERE " . implode(' AND ', $where);

		$sql .= " GROUP BY cp.category_id";

		if (!empty($filter['path_name'])) {
			$implode = array();

			$words = explode(' ', trim(preg_replace('/\s+/', ' ', $filter['path_name'])));

			foreach ($words as $word) {
				$implode[] = "path_name LIKE '%" . $this->db->escape((string)$word) . "%'";
			}

			if ($implode) {
				$sql .= " HAVING " . implode(" AND ", $implode) . "";
			}
		}

		$sort_data = array(
			'name' => 'cd.name',
			'path_name' => 'path_name',
			'sort_order' => 'c.sort_order'
		);

		if (isset($data['sort']) && isset($sort_data[$data['sort']])) {
			$sql .= " ORDER BY " . $sort_data[$data['sort']];
		} else {
			$sql .= " ORDER BY c.sort_order";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getTotalCategories($filter = array()) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "info_category c";

		if (isset($filter['store_id'])) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "info_category_to_store c2s ON (c.category_id = c2s.category_id)";
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
		$category_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "info_category_description WHERE category_id = '" . (int)$category_id . "'");

		foreach ($query->rows as $result) {
			$category_description_data[$result['language_id']] = $result;
		}

		return $category_description_data;
	}

	public function getCategoryPath($category_id) {
		$query = $this->db->query("SELECT cp.*, cd_p.name, cd_p.title FROM " . DB_PREFIX . "info_category_path cp LEFT JOIN " . DB_PREFIX . "info_category c ON (c.category_id = cp.path_id) LEFT JOIN " . DB_PREFIX . "info_category_description cd_p ON (cp.path_id = cd_p.category_id AND cd_p.language_id = '" . (int)$this->config->get('config_language_id') . "') WHERE cp.category_id = '" . (int)$category_id . "' AND c.status = '1' ORDER BY cp.level ASC");

		//$query = $this->db->query("SELECT category_id, path_id, level FROM " . DB_PREFIX . "info_category_path WHERE category_id = '" . (int)$category_id . "' ORDER BY level ASC");

		return $query->rows;
	}

	public function getCategoryPathId($category_id) {
		$query = $this->db->query("SELECT cp.path_id FROM " . DB_PREFIX . "info_category_path cp LEFT JOIN " . DB_PREFIX . "info_category c ON (c.category_id = cp.path_id) WHERE cp.category_id = '" . (int)$category_id . "' AND c.status = '1' ORDER BY cp.level ASC");

		return array_column($query->rows, 'path_id');
	}

	public function getPath($category_id) {
		return implode('_', array_column($this->getCategoryPath($category_id), 'path_id'));
	}

	public function getCategoryStores($category_id) {
		$category_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "info_category_to_store WHERE category_id = '" . (int)$category_id . "'");

		foreach ($query->rows as $result) {
			$category_store_data[] = $result['store_id'];
		}

		return $category_store_data;
	}

	public function getCategorySeoUrls($category_id) {
		$this->load->model('design/seo_url');

		$results = $this->model_design_seo_url->getSeoUrlsByQuery('info_category_id=' . (int)$category_id);

		$seo_url_data = array();

		foreach ($results as $result) {
			$seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $seo_url_data;
	}

	public function getAllCategories() {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "info_category c LEFT JOIN " . DB_PREFIX . "info_category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "info_category_to_store c2s ON (c.category_id = c2s.category_id) WHERE cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  ORDER BY c.parent_id, c.sort_order, cd.name");

		$category_data = array();

		foreach ($query->rows as $row) {
			$category_data[$row['parent_id']][$row['category_id']] = $row;
		}

		return $category_data;
	}

	public function getCategoryCustomField($category_id, $custom_field_id, $language_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "info_category_custom_field WHERE category_id = '" . (int)$category_id . "' AND custom_field_id = '" . (int)$custom_field_id . "' AND language_id = '" . (int)$language_id . "'");

		return ($query->num_rows ? $query->row['value'] : false);
	}

	public function getCategoryCustomFields($category_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "info_category_custom_field WHERE category_id = '" . (int)$category_id . "'");

		$custom_field_value = array();

		foreach ($query->rows as $row) {
			$custom_field_value[$row['custom_field_id']][$row['language_id']] = $row['value'];
		}

		return $custom_field_value;
	}

	public function copyCategory($category_id) {
		$data = $this->getCategory($category_id);

		if ($data) {
			$data['status'] = '0';

			$data['category_description'] = $this->getCategoryDescriptions($category_id);
			$data['category_layout'] = $this->getCategoryLayouts($category_id);
			$data['category_store'] = $this->getCategoryStores($category_id);
			$data['custom_field'] = $this->getCategoryCustomFields($category_id);

			$copy_category_id = $this->addCategory($data);

			return $copy_category_id;
		}
	}

	public function getCategoriesByParentId($parent_id = 0) {
		$query = $this->db->query("SELECT *, (SELECT COUNT(parent_id) FROM " . DB_PREFIX . "info_category WHERE parent_id = c.category_id) AS children FROM " . DB_PREFIX . "info_category c LEFT JOIN " . DB_PREFIX . "info_category_description cd ON (c.category_id = cd.category_id) WHERE c.parent_id = '" . (int)$parent_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY c.sort_order, cd.name");

		return $query->rows;
	}
}