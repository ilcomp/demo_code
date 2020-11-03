<?php
namespace Model\Catalog;

class ProductModify extends \Model\Catalog\Product {
	public function addProduct($data) {
		if (!isset($data['product_store']))
			$data['product_store'] = array(0);

		$this->db->query("INSERT INTO " . DB_PREFIX . "catalog_product SET date_available = '" . $this->db->escape((string)$data['date_available']) . "', status = '" . (int)$data['status'] . "', sort_order = '" . (int)$data['sort_order'] . "', date_added = NOW(), date_modified = NOW()");

		$product_id = $this->db->getLastId();

		if (isset($data['product_price'])) {
			foreach ($data['product_price'] as $price_id => $price) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "catalog_product_price SET product_id = '" . (int)$product_id . "', price_id = '" . (int)$price_id . "', price = '" . (float)$price . "'");
			}
		}

		if (isset($data['product_image'])) {
			foreach ($data['product_image'] as $key => $product_image) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "catalog_product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape($product_image['image']) . "', `main` = '" . (int)!$key . "', sort_order = '" . (int)$product_image['sort_order'] . "'");
			}
		}

		foreach ($data['product_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "catalog_product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', tag = '" . $this->db->escape($value['tag']) . "', title = '" . $this->db->escape($value['title']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "'");
		}

		if (isset($data['custom_field'])) {
			foreach ($data['custom_field'] as $custom_field_id => $values) {
				foreach ($values as $language_id => $value) {
					if (!empty($value)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "catalog_product_custom_field SET product_id = '" . (int)$product_id . "', custom_field_id = '" . (int)$custom_field_id . "', language_id = '" . (int)$language_id . "', value = '" . $this->db->escape(is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value) . "'");
					}
				}
			}
		}

		foreach ($data['product_store'] as $store_id) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "catalog_product_to_store SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "'");
		}

		if (isset($data['product_category'])) {
			$categories = array();

			foreach ($data['product_category'] as $product_category) {
				if (!in_array((int)$product_category['category_id'], $categories)) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "catalog_product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$product_category['category_id'] . "', `main` = '" . (int)$product_category['main'] . "'");

					$categories[] = (int)$product_category['category_id'];
				}
			}
		}

		if (isset($data['product_related'])) {
			foreach ($data['product_related'] as $related_id) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_product_related WHERE product_id = '" . (int)$product_id . "' AND related_id = '" . (int)$related_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "catalog_product_related SET product_id = '" . (int)$product_id . "', related_id = '" . (int)$related_id . "'");
				$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_product_related WHERE product_id = '" . (int)$related_id . "' AND related_id = '" . (int)$product_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "catalog_product_related SET product_id = '" . (int)$related_id . "', related_id = '" . (int)$product_id . "'");
			}
		}

		// SEO URL
		if (isset($data['product_seo_url'])) {
			$this->load->model('design/seo_url');

			foreach ($data['product_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if ($keyword) {
						$this->model_design_seo_url->addSeoUrl(array(
							'store_id' => $store_id,
							'language_id' => $language_id,
							'query' => 'catalog_product_id=' . (int)$product_id,
							'keyword' => $keyword,
							'push' => 'route=catalog/product&catalog_product_id=' . (int)$product_id
						));
					}
				}
			}
		}

		return $product_id;
	}

	public function editProduct($product_id, $data) {
		if (!isset($data['product_store']))
			$data['product_store'] = array(0);

		$this->db->query("UPDATE " . DB_PREFIX . "catalog_product SET date_available = '" . $this->db->escape((string)$data['date_available']) . "', status = '" . (int)$data['status'] . "', sort_order = '" . (int)$data['sort_order'] . "', date_modified = NOW() WHERE product_id = '" . (int)$product_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_product_price WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_price'])) {
			foreach ($data['product_price'] as $price_id => $price) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "catalog_product_price SET product_id = '" . (int)$product_id . "', price_id = '" . (int)$price_id . "', price = '" . (float)$price . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_product_image WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_image'])) {
			foreach ($data['product_image'] as $key => $product_image) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "catalog_product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape($product_image['image']) . "', main = '" . (int)!$key . "', sort_order = '" . (int)$product_image['sort_order'] . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_product_description WHERE product_id = '" . (int)$product_id . "'");

		foreach ($data['product_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "catalog_product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', tag = '" . $this->db->escape($value['tag']) . "', title = '" . $this->db->escape($value['title']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_product_custom_field WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['custom_field'])) {
			foreach ($data['custom_field'] as $custom_field_id => $values) {
				foreach ($values as $language_id => $value) {
					if (!empty($value)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "catalog_product_custom_field SET product_id = '" . (int)$product_id . "', custom_field_id = '" . (int)$custom_field_id . "', language_id = '" . (int)$language_id . "', value = '" . $this->db->escape(is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value) . "'");
					}
				}
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_product_to_store WHERE product_id = '" . (int)$product_id . "'");

		foreach ($data['product_store'] as $store_id) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "catalog_product_to_store SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_product_to_category WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_category'])) {
			$categories = array();

			foreach ($data['product_category'] as $product_category) {
				if (!in_array((int)$product_category['category_id'], $categories)) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "catalog_product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$product_category['category_id'] . "', `main` = '" . (int)$product_category['main'] . "'");

					$categories[] = (int)$product_category['category_id'];
				}
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_product_related WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_product_related WHERE related_id = '" . (int)$product_id . "'");

		if (isset($data['product_related'])) {
			foreach ($data['product_related'] as $related_id) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_product_related WHERE product_id = '" . (int)$product_id . "' AND related_id = '" . (int)$related_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "catalog_product_related SET product_id = '" . (int)$product_id . "', related_id = '" . (int)$related_id . "'");
				$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_product_related WHERE product_id = '" . (int)$related_id . "' AND related_id = '" . (int)$product_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "catalog_product_related SET product_id = '" . (int)$related_id . "', related_id = '" . (int)$product_id . "'");
			}
		}

		// SEO URL
		$this->load->model('design/seo_url');

		$this->model_design_seo_url->deleteSeoUrlByQuery('catalog_product_id=' . (int)$product_id );

		if (isset($data['product_seo_url'])) {
			foreach ($data['product_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if ($keyword) {
						$this->model_design_seo_url->addSeoUrl(array(
							'store_id' => $store_id,
							'language_id' => $language_id,
							'query' => 'catalog_product_id=' . (int)$product_id,
							'keyword' => $keyword,
							'push' => 'route=catalog/product&catalog_product_id=' . (int)$product_id
						));
					}
				}
			}
		}
	}

	public function copyProduct($product_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "catalog_product p WHERE p.product_id = '" . (int)$product_id . "'");

		if ($query->num_rows) {
			$data = $query->row;

			$data['status'] = '0';
			$data['date_available'] = date('Y-m-d H:i:s');

			$data['product_description'] = $this->getProductDescriptions($product_id);
			$data['product_image'] = $this->getProductImages($product_id);
			$data['product_related'] = $this->getProductRelated($product_id);
			$data['product_category'] = $this->getProductCategories($product_id);
			$data['product_store'] = $this->getProductStores($product_id);
			$data['custom_field'] = $this->getProductCustomFields($product_id);

			$copy_id = $this->addProduct($data);
		}
	}

	public function deleteProduct($product_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_product WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_product_description WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "catalog_product_custom_field` WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_product_image WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_product_related WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_product_related WHERE related_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_product_to_category WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_product_to_store WHERE product_id = '" . (int)$product_id . "'");

		$this->load->model('design/seo_url');

		$this->model_design_seo_url->deleteSeoUrlByQuery('catalog_product_id=' . (int)$product_id);
	}

	public function getProductsByCategoryId($category_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "catalog_product p LEFT JOIN " . DB_PREFIX . "catalog_product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "catalog_product_to_category p2c ON (p.product_id = p2c.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p2c.category_id = '" . (int)$category_id . "' ORDER BY pd.name ASC");

		return $query->rows;
	}

	public function getProductDescriptions($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "catalog_product_description WHERE product_id = '" . (int)$product_id . "'");

		return array_column($query->rows, null, 'language_id');
	}

	public function getProductCategories($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "catalog_product_to_category WHERE product_id = '" . (int)$product_id . "'");

		return $query->rows;
	}

	public function getProductPrices($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "catalog_product_price WHERE product_id = '" . (int)$product_id . "'");

		return array_column($query->rows, 'price', 'price_id');
	}

	public function getProductStores($product_id) {
		$query = $this->db->query("SELECT store_id FROM " . DB_PREFIX . "catalog_product_to_store WHERE product_id = '" . (int)$product_id . "'");

		return array_column($query->rows, 'store_id');
	}

	public function getProductSeoUrls($product_id) {
		$this->load->model('design/seo_url');

		$results = $this->model_design_seo_url->getSeoUrlsByQuery('catalog_product_id=' . (int)$product_id);

		$seo_url_data = array();

		foreach ($results as $result) {
			$seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $seo_url_data;
	}

	public function getProductRelatedId($product_id) {
		$query = $this->db->query("SELECT related_id FROM " . DB_PREFIX . "catalog_product_related WHERE product_id = '" . (int)$product_id . "'");

		return array_column($query->rows, 'related_id');
	}
}