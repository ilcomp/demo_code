<?php
namespace Model\Info;

class Article extends \Model {
	public function getArticle($article_id, $data = array()) {
		$filter = isset($data['filter']) && is_array($data['filter']) ? $data['filter'] : array();

		if (!isset($data['language_id']))
			$data['language_id'] = $this->config->get('config_language_id');

		$columns = array();
		$where = array();
		$join = array();

		$where[] = "ia.article_id = '" . (int)$article_id . "'";

		if (isset($filter['status'])) {
			$where[] = "ia.status = '" . (int)$filter['status'] . "'";
		}

		if (!empty($filter['date_available'])) {
			$where[] = "ia.date_available <= NOW()";
		}

		if (!empty($data['language_id'])) {
			$columns[] = "iad.*";

			$join[] = "LEFT JOIN " . DB_PREFIX . "info_article_description iad ON (ia.article_id = iad.article_id AND iad.language_id = '" . (int)$data['language_id'] . "')";
		}

		if (!empty($filter['language_id'])) {
			$join[] = "LEFT JOIN " . DB_PREFIX . "info_article_description iad_f ON (ia.article_id = iad_f.article_id)";

			$where[] = "iad_f.language_id = '" . (int)$filter['language_id'] . "'";
		}

		if (isset($filter['store_id'])) {
			$join[] = "LEFT JOIN " . DB_PREFIX . "info_article_to_store ia2s ON (ia.article_id = ia2s.article_id)";

			$where[] = "ia2s.store_id = '" . (int)$filter['store_id'] . "'";
		}

		$columns[] = "ia.*";

		$this->event->trigger('model/info/article/getArticle/sql', array(&$data, &$join, &$where, &$columns));

		$sql = "SELECT DISTINCT " . implode(', ', $columns) . " FROM " . DB_PREFIX . "info_article ia";

		if (!empty($join))
			$sql .= ' ' . implode(' ', $join);

		if (!empty($where))
			$sql .= ' WHERE ' . implode(' AND ', $where);

		$query = $this->db->query($sql);

		return $query->row;
	}

	public function getArticles($data = array()) {
		$filter = isset($data['filter']) && is_array($data['filter']) ? $data['filter'] : array();

		$join = array();
		$where = array();
		$order_by = array();

		if (isset($filter['category_id'])) {
			if ($filter['category_id'] == 0) {
				$where[] = "ia2c.category_id IS NULL";
			} elseif (!empty($filter['sub_category'])) {
				$where[] = "icp.path_id = '" . (int)$filter['category_id'] . "' AND ia2c.category_id IS NOT NULL";
			} else {
				$where[] = "ia2c.category_id = '" . (int)$filter['category_id'] . "'";
			}
		}

		$join[] = "store";
		$join[] = "description";

		if (!empty($filter['language_id']))
			$where[] = "iad.language_id = '" . (int)$filter['language_id'] . "'";

		if (!empty($filter['status']))
			$where[] = "ia.status = '" . (int)$filter['status'] . "'";

		if (!empty($filter['date_available']))
			$where[] = "ia.date_available <= NOW()";

		if (!empty($filter['store_id']))
			$where[] = "ia2s.store_id = '" . (int)$filter['store_id'] . "'";

		if (!empty($filter['name'])) {
			$where[] = "LCASE(iad.name) LIKE '" . $this->db->escape(utf8_strtolower($filter['name'])) . "%'";
		}

		if (isset($filter['search'])) {
			$join[] = "custom_field";

			$filter['search'] = utf8_strtolower(trim($filter['search']));

			$search = array();
			$search[] = "LCASE(iad.name) LIKE '%" . $this->db->escape($filter['search']) . "%'";
			$search[] = "LCASE(iad.title) LIKE '%" . $this->db->escape($filter['search']) . "%'";
			$search[] = "LCASE(iad.meta_title) LIKE '%" . $this->db->escape($filter['search']) . "%'";
			$search[] = "LCASE(iad.meta_description) LIKE '%" . $this->db->escape($filter['search']) . "%'";
			$search[] = "LCASE(iad.tag) LIKE '%" . $this->db->escape($filter['search']) . "%'";

			$this->load->model('core/custom_field');

			$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('info_article');

			foreach ($custom_fields as $custom_field) {
				if ((int)$custom_field['search'])
					$search[] = "(iacf.custom_field_id = '" . (int)$custom_field['custom_field_id'] . "' AND LCASE(iacf.value) LIKE '%" . $this->db->escape($filter['search']) . "%')";
			}

			$where[] = "(" . implode(' OR ', $search) . ")";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$order = " DESC";
		} else {
			$order = " ASC";
		}

		if (isset($data['sort'])) {
			switch ($data['sort']) {
				// case 'category': $join[] = 'category'; $order_by[] = "ia2c.category" . $order; break;
				case 'sort_order': $order_by[] = "ia.sort_order" . $order; break;
				case 'date_added': $order_by[] = "ia.date_added" . $order; break;
				case 'date_modified': $order_by[] = "ia.date_modified" . $order; break;
				case 'date_available': $order_by[] = "ia.date_available" . $order; break;
			}
		}

		$join[] = 'description';

		$order_by[] = "LCASE(iad.name)" . $order;

		$this->event->trigger('model/info/article/getArticles/sql', array(&$data, &$join, &$where, &$order_by));

		$sql = "SELECT ia.article_id";

		if (isset($filter['category_id'])) {
			if ($filter['category_id'] == 0) {
				$sql .= " FROM " . DB_PREFIX . "info_article ia LEFT JOIN " . DB_PREFIX . "info_article_to_category ia2c ON (ia.article_id = ia2c.article_id)";
			} else {
				if (!empty($filter['sub_category'])) {
					$sql .= " FROM " . DB_PREFIX . "info_category_path icp LEFT JOIN " . DB_PREFIX . "info_article_to_category ia2c ON (icp.category_id = ia2c.category_id)";
				} else {
					$sql .= " FROM " . DB_PREFIX . "info_article_to_category ia2c";
				}

				$sql .= " LEFT JOIN " . DB_PREFIX . "info_article ia ON (ia2c.article_id = ia.article_id)";
			}
		} else {
			$sql .= " FROM " . DB_PREFIX . "info_article ia";
		}

		$join = array_unique($join);

		foreach ($join as &$value) {
			switch ($value) {
				case 'store':
					$value = "LEFT JOIN " . DB_PREFIX . "info_article_to_store ia2s ON (ia.article_id = ia2s.article_id)";
					break;
				case 'description':
					$value = "LEFT JOIN " . DB_PREFIX . "info_article_description iad ON (ia.article_id = iad.article_id AND iad.language_id = '" . (int)$this->config->get('config_language_id') . "')";
					break;
				case 'custom_field':
					$value = "LEFT JOIN " . DB_PREFIX . "info_article_custom_field iacf ON (ia.article_id = iacf.article_id)";
					break;
			}
		}
		unset($value);

		if (!empty($join))
			$sql .= ' ' . implode(' ', $join);

		if (!empty($where))
			$sql .= ' WHERE ' . implode(' AND ', $where);

		$sql .= " GROUP BY ia.article_id";

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

		$article_data = array();

		foreach (array_column($query->rows, 'article_id') as $article_id) {
			$article_data[$article_id] = $this->getArticle($article_id, $data);	
		}

		return $article_data;
	}

	public function getTotalArticles($data = array()) {
		$filter = isset($data['filter']) && is_array($data['filter']) ? $data['filter'] : array();

		$join = array();
		$where = array();
		$order_by = array();

		if (isset($filter['category_id'])) {
			if ($filter['category_id'] == 0) {
				$where[] = "ia2c.category_id IS NULL";
			} elseif (!empty($filter['sub_category'])) {
				$where[] = "icp.path_id = '" . (int)$filter['category_id'] . "' AND ia2c.category_id IS NOT NULL";
			} else {
				$where[] = "ia2c.category_id = '" . (int)$filter['category_id'] . "'";
			}
		}

		$join[] = "store";
		$join[] = "description";

		if (!empty($filter['language_id']))
			$where[] = "iad.language_id = '" . (int)$filter['language_id'] . "'";

		if (!empty($filter['status']))
			$where[] = "ia.status = '" . (int)$filter['status'] . "'";

		if (!empty($filter['date_available']))
			$where[] = "ia.date_available <= NOW()";

		if (!empty($filter['store_id']))
			$where[] = "ia2s.store_id = '" . (int)$filter['store_id'] . "'";

		if (!empty($filter['name'])) {
			$where[] = "LCASE(iad.name) LIKE '" . $this->db->escape(utf8_strtolower($filter['name'])) . "%'";
		}

		if (isset($filter['search'])) {
			$join[] = "custom_field";

			$filter['search'] = utf8_strtolower(trim($filter['search']));

			$search = array();
			$search[] = "LCASE(iad.name) LIKE '%" . $this->db->escape($filter['search']) . "%'";
			$search[] = "LCASE(iad.title) LIKE '%" . $this->db->escape($filter['search']) . "%'";
			$search[] = "LCASE(iad.meta_title) LIKE '%" . $this->db->escape($filter['search']) . "%'";
			$search[] = "LCASE(iad.meta_description) LIKE '%" . $this->db->escape($filter['search']) . "%'";
			$search[] = "LCASE(iad.tag) LIKE '%" . $this->db->escape($filter['search']) . "%'";

			$this->load->model('core/custom_field');

			$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('info_article');

			foreach ($custom_fields as $custom_field) {
				if ((int)$custom_field['search'])
					$search[] = "(iacf.custom_field_id = '" . (int)$custom_field['custom_field_id'] . "' AND LCASE(iacf.value) LIKE '%" . $this->db->escape($filter['search']) . "%')";
			}

			$where[] = "(" . implode(' OR ', $search) . ")";
		}

		$this->event->trigger('model/info/article/getTotalArticles/sql', array(&$data, &$join, &$where, &$order_by));

		$sql = "SELECT COUNT(DISTINCT ia.article_id) AS total";

		if (isset($filter['category_id'])) {
			if ($filter['category_id'] == 0) {
				$sql .= " FROM " . DB_PREFIX . "info_article ia LEFT JOIN " . DB_PREFIX . "info_article_to_category ia2c ON (ia.article_id = ia2c.article_id)";
			} else {
				if (!empty($filter['sub_category'])) {
					$sql .= " FROM " . DB_PREFIX . "info_category_path icp LEFT JOIN " . DB_PREFIX . "info_article_to_category ia2c ON (icp.category_id = ia2c.category_id)";
				} else {
					$sql .= " FROM " . DB_PREFIX . "info_article_to_category ia2c";
				}

				$sql .= " LEFT JOIN " . DB_PREFIX . "info_article ia ON (ia2c.article_id = ia.article_id)";
			}
		} else {
			$sql .= " FROM " . DB_PREFIX . "info_article ia";
		}

		$join = array_unique($join);

		foreach ($join as &$value) {
			switch ($value) {
				case 'store':
					$value = "LEFT JOIN " . DB_PREFIX . "info_article_to_store ia2s ON (ia.article_id = ia2s.article_id)";
					break;
				case 'description':
					$value = "LEFT JOIN " . DB_PREFIX . "info_article_description iad ON (ia.article_id = iad.article_id AND iad.language_id = '" . (int)$this->config->get('config_language_id') . "')";
					break;
				case 'custom_field':
					$value = "LEFT JOIN " . DB_PREFIX . "info_article_custom_field iacf ON (ia.article_id = iacf.article_id)";
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

	public function getArticleDescriptions($article_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "info_article_description WHERE article_id = '" . (int)$article_id . "'");

		return array_column($query->rows, null, 'language_id');
	}

	public function getArticleCategories($article_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "info_article_to_category WHERE article_id = '" . (int)$article_id . "'");

		return $query->rows;
	}

	public function getArticleMainCategoryId($article_id) {
		$query = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "info_article_to_category WHERE article_id = '" . (int)$article_id . "' AND main = '1' LIMIT 1");

		return ($query->num_rows ? (int)$query->row['category_id'] : 0);
	}

	public function getArtcileCategoryIdMain($article_id) {
		$query = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "info_article_to_category WHERE article_id = '" . (int)$article_id . "' AND main = '1' LIMIT 1");

		return ($query->num_rows ? (int)$query->row['category_id'] : 0);
	}

	public function getArticleStores($article_id) {
		$query = $this->db->query("SELECT store_id FROM " . DB_PREFIX . "info_article_to_store WHERE article_id = '" . (int)$article_id . "'");

		return array_column($query->rows, 'store_id');
	}

	public function getArticleSeoUrls($article_id) {
		$this->load->model('design/seo_url');

		$results = $this->model_design_seo_url->getSeoUrlsByQuery('info_article_id=' . (int)$article_id);

		$seo_url_data = array();

		foreach ($results as $result) {
			$seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $seo_url_data;
	}

	public function getArticleCustomField($article_id, $custom_field_id, $language_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "info_article_custom_field WHERE article_id = '" . (int)$article_id . "' AND custom_field_id = '" . (int)$custom_field_id . "' AND language_id = '" . (int)$language_id . "'");

		return ($query->num_rows ? $query->row['value'] : false);
	}

	public function getArticleCustomFields($article_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "info_article_custom_field WHERE article_id = '" . (int)$article_id . "' ORDER BY language_id");

		$custom_field_value = array();

		foreach ($query->rows as $row) {
			$custom_field_value[$row['custom_field_id']][$row['language_id']] = $row['value'];
		}

		return $custom_field_value;
	}

	public function getList($filter_data, $url = '') {
		if (!$this->permission->has('read', 'model\info\article')) {
			$data['infos'] = $this->getArticles($filter_data);

			$client_url = new Url(HTTP_APPLICATION_CLIENT);

			foreach ($data['infos'] as &$result) {
				$result['category'] = $this->getArticleCategories($result['article_id']);
				$result['status'] = $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled');

				if (!$this->permission->has('read', 'model\info\article')) {
					$result['view'] = $this->client_url->link('info/article', 'article_id=' . $result['article_id']);
					$result['edit'] = $this->url->link('info/article/edit', '&article_id=' . $result['article_id'] . $url);
				} else {
					$result['view'] = $this->url->link('info/article', 'article_id=' . $result['article_id']);
				}
			}
			unset($result);

			return $data;
		}
	}
}