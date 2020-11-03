<?php
namespace Model\Info;

class ArticleModify extends \Model\Info\Article {
	public function addArticle($data) {
		if (!isset($data['article_store']))
			$data['article_store'] = array(0);

		$this->db->query("INSERT INTO " . DB_PREFIX . "info_article SET date_available = '" . $this->db->escape($data['date_available']) . "', status = '" . (int)$data['status'] . "', sort_order = '" . (int)$data['sort_order'] . "', date_added = NOW()");

		$article_id = $this->db->getLastId();

		foreach ($data['article_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "info_article_description SET article_id = '" . (int)$article_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', title = '" . $this->db->escape($value['title']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', tag = '" . $this->db->escape($value['tag']) . "'");
		}

		if (isset($data['custom_field'])) {
			foreach ($data['custom_field'] as $custom_field_id => $values) {
				foreach ($values as $language_id => $value) {
					if (!empty($value)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "info_article_custom_field SET article_id = '" . (int)$article_id . "', custom_field_id = '" . (int)$custom_field_id . "', language_id = '" . (int)$language_id . "', value = '" . $this->db->escape(is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value) . "'");
					}
				}
			}
		}

		if (isset($data['article_category'])) {
			$categories = array();

			foreach ($data['article_category'] as $article_category) {
				if ($article_category['category_id'] && !in_array((int)$article_category['category_id'], $categories)) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "info_article_to_category SET article_id = '" . (int)$article_id . "', category_id = '" . (int)$article_category['category_id'] . "', `main` = '" . (int)$article_category['main'] . "'");

					$categories[] = (int)$article_category['category_id'];
				}
			}
		}

		foreach ($data['article_store'] as $store_id) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "info_article_to_store SET article_id = '" . (int)$article_id . "', store_id = '" . (int)$store_id . "'");
		}

		// SEO URL
		if (isset($data['article_seo_url'])) {
			$this->load->model('design/seo_url');

			foreach ($data['article_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if ($keyword) {
						$this->model_design_seo_url->addSeoUrl(array(
							'store_id' => $store_id,
							'language_id' => $language_id,
							'query' => 'info_article_id=' . (int)$article_id,
							'keyword' => $keyword,
							'push' => 'route=info/article&info_article_id=' . (int)$article_id
						));
					}
				}
			}
		}

		return $article_id;
	}

	public function editArticle($article_id, $data) {
		if (!isset($data['article_store']))
			$data['article_store'] = array(0);

		$this->db->query("UPDATE " . DB_PREFIX . "info_article SET date_available = '" . $this->db->escape($data['date_available']) . "', status = '" . (int)$data['status'] . "', sort_order = '" . (int)$data['sort_order'] . "' WHERE article_id = '" . (int)$article_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "info_article_description WHERE article_id = '" . (int)$article_id . "'");

		foreach ($data['article_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "info_article_description SET article_id = '" . (int)$article_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', title = '" . $this->db->escape($value['title']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', tag = '" . $this->db->escape($value['tag']) . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "info_article_custom_field WHERE article_id = '" . (int)$article_id . "'");

		if (isset($data['custom_field'])) {
			foreach ($data['custom_field'] as $custom_field_id => $values) {
				foreach ($values as $language_id => $value) {
					if (!empty($value)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "info_article_custom_field SET article_id = '" . (int)$article_id . "', custom_field_id = '" . (int)$custom_field_id . "', language_id = '" . (int)$language_id . "', value = '" . $this->db->escape(is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value) . "'");
					}
				}
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "info_article_to_category WHERE article_id = '" . (int)$article_id . "'");

		if (isset($data['article_category'])) {
			$categories = array();

			foreach ($data['article_category'] as $article_category) {
				if ($article_category['category_id'] && !in_array((int)$article_category['category_id'], $categories)) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "info_article_to_category SET article_id = '" . (int)$article_id . "', category_id = '" . (int)$article_category['category_id'] . "', `main` = '" . (int)$article_category['main'] . "'");

					$categories[] = (int)$article_category['category_id'];
				}
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "info_article_to_store WHERE article_id = '" . (int)$article_id . "'");

		foreach ($data['article_store'] as $store_id) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "info_article_to_store SET article_id = '" . (int)$article_id . "', store_id = '" . (int)$store_id . "'");
		}

		// SEO URL
		$this->load->model('design/seo_url');

		$this->model_design_seo_url->deleteSeoUrlByQuery('info_article_id=' . (int)$article_id );

		if (isset($data['article_seo_url'])) {
			foreach ($data['article_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if ($keyword) {
						$this->model_design_seo_url->addSeoUrl(array(
							'store_id' => $store_id,
							'language_id' => $language_id,
							'query' => 'info_article_id=' . (int)$article_id,
							'keyword' => $keyword,
							'push' => 'route=info/article&info_article_id=' . (int)$article_id
						));
					}
				}
			}
		}
	}

	public function copyArticle($article_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "info_article WHERE article_id = '" . (int)$article_id . "'");

		if ($query->num_rows) {
			$data = $query->row;

			$data['status'] = '0';
			$data['date_available'] = date('Y-m-d H:i:s');

			$data['article_description'] = $this->getArticleDescriptions($article_id);
			$data['article_image'] = $this->getArticleImages($article_id);
			$data['article_category'] = $this->getArticleCategories($article_id);
			$data['article_store'] = $this->getArticleStores($article_id);
			$data['custom_field'] = $this->getArticleCustomFields($article_id);

			$this->addArticle($data);
		}
	}

	public function deleteArticle($article_id, $tables = array()) {
		if (empty($tables) || in_array('info_article', $tables)) {
			$tables = array(
				'seo_url',
				'info_article_description',
				'info_article_custom_field',
				'info_article_to_store',
				'info_article_to_category',
				'info_article'
			);
		}

		foreach ($tables as $table) {
			switch ($table) {
				case 'seo_url':
					$this->load->model('design/seo_url');

					$this->model_design_seo_url->deleteSeoUrlByQuery('info_article_id=' . (int)$article_id);

					break;
				default:
					$this->db->query("DELETE FROM `" . DB_PREFIX . $table .  "` WHERE article_id = '" . (int)$article_id . "'");
					break;
			}
		}
	}
}