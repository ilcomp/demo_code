<?php
namespace Model\Catalog;

class CategoryModify extends \Model\Catalog\Category {
	public function addCategory($data) {
		if (!isset($data['category_store']))
			$data['category_store'] = array(0);

		$this->db->query("INSERT INTO " . DB_PREFIX . "catalog_category SET parent_id = '" . (int)$data['parent_id'] . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "', date_modified = NOW(), date_added = NOW()");

		$category_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "catalog_category SET image = '" . $this->db->escape((string)$data['image']) . "' WHERE category_id = '" . (int)$category_id . "'");
		}

		foreach ($data['category_description'] as $language_id => $value) {
			if (!isset($value['tag']))
				$value['tag'] = '';

			$this->db->query("INSERT INTO " . DB_PREFIX . "catalog_category_description SET category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', title = '" . $this->db->escape($value['title']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', tag = '" . $this->db->escape($value['tag']) . "'");
		}

		if (isset($data['custom_field'])) {
			foreach ($data['custom_field'] as $custom_field_id => $values) {
				foreach ($values as $language_id => $value) {
					if (!empty($value)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "catalog_category_custom_field SET category_id = '" . (int)$category_id . "', custom_field_id = '" . (int)$custom_field_id . "', language_id = '" . (int)$language_id . "', value = '" . $this->db->escape(is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value) . "'");
					}
				}
			}
		}

		// MySQL Hierarchical Data Closure Table Pattern
		$this->db->query("SET @level = 0");

		$this->db->query("INSERT INTO " . DB_PREFIX . "catalog_category_path (category_id, path_id, `level`) SELECT '" . (int)$category_id . "', path_id, @level := (@level + 1) FROM (SELECT path_id, `level` FROM " . DB_PREFIX . "catalog_category_path WHERE category_id = '" . (int)$data['parent_id'] . "' UNION (SELECT '" . (int)$category_id . "', NULL AS `level`) ORDER BY `level` IS NULL, `level`) AS t ON DUPLICATE KEY UPDATE `level` = (@level := (@level + 1))");

		foreach ($data['category_store'] as $store_id) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "catalog_category_to_store SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "'");
		}

		if (isset($data['category_seo_url'])) {
			$this->load->model('design/seo_url');

			foreach ($data['category_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if ($keyword) {
						$this->model_design_seo_url->addSeoUrl(array(
							'store_id' => $store_id,
							'language_id' => $language_id,
							'query' => 'catalog_category_id=' . (int)$category_id,
							'keyword' => $keyword,
							'push' => 'route=catalog/category&catalog_category_id=' . (int)$category_id
						));
					}
				}
			}
		}

		return $category_id;
	}

	public function editCategory($category_id, $data) {
		if (!isset($data['category_store']))
			$data['category_store'] = array(0);

		$this->db->query("UPDATE " . DB_PREFIX . "catalog_category SET parent_id = '" . (int)$data['parent_id'] . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "', date_modified = NOW() WHERE category_id = '" . (int)$category_id . "'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "catalog_category SET image = '" . $this->db->escape((string)$data['image']) . "' WHERE category_id = '" . (int)$category_id . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_category_description WHERE category_id = '" . (int)$category_id . "'");

		foreach ($data['category_description'] as $language_id => $value) {
			if (!isset($value['tag']))
				$value['tag'] = '';

			$this->db->query("INSERT INTO " . DB_PREFIX . "catalog_category_description SET category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', title = '" . $this->db->escape($value['title']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', tag = '" . $this->db->escape($value['tag']) . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_category_custom_field WHERE category_id = '" . (int)$category_id . "'");

		if (isset($data['custom_field'])) {
			foreach ($data['custom_field'] as $custom_field_id => $values) {
				foreach ($values as $language_id => $value) {
					if (!empty($value)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "catalog_category_custom_field SET category_id = '" . (int)$category_id . "', custom_field_id = '" . (int)$custom_field_id . "', language_id = '" . (int)$language_id . "', value = '" . $this->db->escape(is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value) . "'");
					}
				}
			}
		}

		// MySQL Hierarchical Data Closure Table Pattern
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "catalog_category_path WHERE path_id = '" . (int)$category_id . "' ORDER BY level ASC");

		if ($query->num_rows) {
			foreach ($query->rows as $category_path) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_category_path WHERE category_id = '" . (int)$category_path['category_id'] . "' AND level < '" . (int)$category_path['level'] . "'");

				$this->db->query("SET @level = 0");

				$this->db->query("INSERT INTO " . DB_PREFIX . "catalog_category_path (category_id, path_id, `level`) SELECT '" . (int)$category_path['category_id'] . "', path_id, @level := (@level + 1) FROM (SELECT path_id, `level` FROM " . DB_PREFIX . "catalog_category_path WHERE category_id = '" . (int)$data['parent_id'] . "' UNION SELECT path_id, `level` FROM " . DB_PREFIX . "catalog_category_path WHERE category_id = '" . (int)$category_path['category_id'] . "' ORDER BY `level`) AS t ON DUPLICATE KEY UPDATE `level` = (@level := (@level + 1))");
			}
		} else {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "catalog_category_path` WHERE category_id = '" . (int)$category_id . "'");

			$this->db->query("SET @level = 0");

			$this->db->query("INSERT INTO " . DB_PREFIX . "catalog_category_path (category_id, path_id, `level`) SELECT '" . (int)$category_id . "', path_id, @level := (@level + 1) FROM (SELECT path_id, `level` FROM " . DB_PREFIX . "catalog_category_path WHERE category_id = '" . (int)$data['parent_id'] . "' UNION (SELECT '" . (int)$category_id . "', NULL AS `level`) ORDER BY `level` IS NULL, `level`) AS t ON DUPLICATE KEY UPDATE `level` = (@level := (@level + 1))");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_category_to_store WHERE category_id = '" . (int)$category_id . "'");

		foreach ($data['category_store'] as $store_id) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "catalog_category_to_store SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "'");
		}

		// SEO URL
		$this->load->model('design/seo_url');

		$this->model_design_seo_url->deleteSeoUrlByQuery('catalog_category_id=' . (int)$category_id );

		if (isset($data['category_seo_url'])) {
			foreach ($data['category_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if ($keyword) {
						$this->model_design_seo_url->addSeoUrl(array(
							'store_id' => $store_id,
							'language_id' => $language_id,
							'query' => 'catalog_category_id=' . (int)$category_id,
							'keyword' => $keyword,
							'push' => 'route=catalog/category&catalog_category_id=' . (int)$category_id
						));
					}
				}
			}
		}
	}

	public function deleteCategory($category_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_category_path WHERE category_id = '" . (int)$category_id . "'");

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "catalog_category_path WHERE path_id = '" . (int)$category_id . "'");

		foreach ($query->rows as $result) {
			$this->deleteCategory($result['category_id']);
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_category WHERE category_id = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_category_description WHERE category_id = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_category_custom_field WHERE category_id = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_category_to_store WHERE category_id = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_product_to_category WHERE category_id = '" . (int)$category_id . "'");

		$this->load->model('design/seo_url');

		$this->model_design_seo_url->deleteSeoUrlByQuery('catalog_category_id=' . (int)$category_id);
	}

	public function copyCategory($category_id) {
		if ($this->permission->has('write', 'model\catalog\category')) {
			$data = $this->getCategory($category_id);

			if ($data) {
				$data['status'] = '0';

				$data['custom_field'] = $this->getCategoryCustomFields($category_id);
				$data['category_description'] = $this->getCategoryDescriptions($category_id);
				$data['category_store'] = $this->getCategoryStores($category_id);

				$this->load->model('catalog\category_write');

				$copy_category_id = $this->addCategory($category_id);

				return $copy_category_id;
			}
		}
	}

	public function repairCategories($parent_id = 0) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "catalog_category WHERE parent_id = '" . (int)$parent_id . "'");

		$this->db->query("DELETE ccp FROM " . DB_PREFIX . "catalog_category_path ccp LEFT JOIN " . DB_PREFIX . "catalog_category cc ON (ccp.category_id = cc.category_id) WHERE cc.parent_id = '" . (int)$parent_id . "'");

		foreach ($query->rows as $category) {
			// Fix for records with no paths
			$this->db->query("SET @level = 0");

			$this->db->query("INSERT INTO " . DB_PREFIX . "catalog_category_path (category_id, path_id, `level`) SELECT '" . (int)$category['category_id'] . "', path_id, @level := (@level + 1) FROM (SELECT path_id, `level` FROM " . DB_PREFIX . "catalog_category_path WHERE category_id = '" . (int)$parent_id . "' UNION (SELECT '" . (int)$category['category_id'] . "', NULL AS `level`) ORDER BY `level` IS NULL, `level`) AS t ON DUPLICATE KEY UPDATE `level` = (@level := (@level + 1))");

			$this->repairCategories($category['category_id']);
		}
	}
}