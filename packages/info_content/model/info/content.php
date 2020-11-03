<?php
namespace Model\Info;

class Content extends \Model {
	public function addContent($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "info_content SET article_id = '" . (int)$data['article_id'] . "', status = '" . (int)$data['status'] . "', sort_order = '" . (int)$data['sort_order'] . "'");

		$content_id = $this->db->getLastId();

		foreach ($data['content_description'] as $language_id => $description) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "info_content_description SET content_id = '" . (int)$content_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape((string)$description['name']) . "'");
		}

		if (isset($data['custom_field'])) {
			foreach ($data['custom_field'] as $custom_field_id => $values) {
				foreach ($values as $language_id => $value) {
					if (!empty($value)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "info_content_custom_field SET content_id = '" . (int)$content_id . "', custom_field_id = '" . (int)$custom_field_id . "', language_id = '" . (int)$language_id . "', value = '" . $this->db->escape(is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value) . "'");
					}
				}
			}
		}

		return $content_id;
	}

	public function editContent($content_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "info_content SET article_id = '" . (int)$data['article_id'] . "', status = '" . (int)$data['status'] . "', sort_order = '" . (int)$data['sort_order'] . "' WHERE content_id = '" . (int)$content_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "info_content_description WHERE content_id = '" . (int)$content_id . "'");

		foreach ($data['content_description'] as $language_id => $description) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "info_content_description SET content_id = '" . (int)$content_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape((string)$description['name']) . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "info_content_custom_field WHERE content_id = '" . (int)$content_id . "'");

		if (isset($data['custom_field'])) {
			foreach ($data['custom_field'] as $custom_field_id => $values) {
				foreach ($values as $language_id => $value) {
					if (!empty($value)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "info_content_custom_field SET content_id = '" . (int)$content_id . "', custom_field_id = '" . (int)$custom_field_id . "', language_id = '" . (int)$language_id . "', value = '" . $this->db->escape(is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value) . "'");
					}
				}
			}
		}
	}

	public function deleteContent($content_id) {
		$this->db->query("DELETE ic, icd, iccf FROM " . DB_PREFIX . "info_content ic LEFT JOIN " . DB_PREFIX . "info_content_description icd ON (ic.content_id = icd.content_id) LEFT JOIN " . DB_PREFIX . "info_content_custom_field iccf ON (ic.content_id = iccf.content_id) WHERE ic.content_id = '" . (int)$content_id . "'");
	}

	public function updateArticleContent($article_id, $data) {
		if (!isset($data['content_status']))
			$data['content_status'] = 0;

		$this->db->query("UPDATE " . DB_PREFIX . "info_article SET content_status = '" . (int)$data['content_status'] . "' WHERE article_id = '" . (int)$article_id . "'");
	}

	public function deleteArticleContent($article_id) {
		$this->db->query("DELETE ic, icd, iccf FROM " . DB_PREFIX . "info_content ic LEFT JOIN " . DB_PREFIX . "info_content_description icd ON (ic.content_id = icd.content_id) LEFT JOIN " . DB_PREFIX . "info_content_custom_field iccf ON (ic.content_id = iccf.content_id) WHERE ic.article_id = '" . (int)$article_id . "'");
	}

	public function getContent($content_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "info_content ic LEFT JOIN " . DB_PREFIX . "info_content_description icd ON (ic.content_id = icd.content_id) WHERE ic.content_id = '" . (int)$content_id . "' AND icd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getContents($data) {
		$filter = isset($data['filter']) && is_array($data['filter']) ? $data['filter'] : array();

		$sql = "SELECT icd.*, ic.* FROM " . DB_PREFIX . "info_content ic LEFT JOIN " . DB_PREFIX . "info_content_description icd ON (ic.content_id = icd.content_id AND icd.language_id = '" . (int)$this->config->get('config_language_id') . "')";

		if (!empty($filter['article_id']))
			$sql .= " WHERE ic.article_id = '" . (int)$filter['article_id'] . "'";

		$sql .= " ORDER BY ic.sort_order ASC, icd.name ASC";

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getArticleContent($article_id) {
		$query = $this->db->query("SELECT content_status FROM " . DB_PREFIX . "info_article WHERE article_id = '" . (int)$article_id . "'");

		return $query->num_rows ? $query->row['content_status'] : 0;
	}

	public function getArticleContents() {
		$query = $this->db->query("SELECT DISTINCT iad.* FROM " . DB_PREFIX . "info_article ia LEFT JOIN " . DB_PREFIX . "info_article_description iad ON (ia.article_id = iad.article_id AND iad.language_id = '" . (int)$this->config->get('config_language_id') . "') WHERE content_status = '1'");

		return $query->rows;
	}

	public function getContentDescriptions($content_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "info_content_description WHERE content_id = '" . (int)$content_id . "'");

		return array_column($query->rows, null, 'language_id');
	}

	public function getContentCustomFields($content_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "info_content_custom_field WHERE content_id = '" . (int)$content_id . "' ORDER BY language_id");

		$custom_field_value = array();

		foreach ($query->rows as $row) {
			$custom_field_value[$row['custom_field_id']][$row['language_id']] = $row['value'];
		}

		return $custom_field_value;
	}

	public function install() {
		$this->db->query("ALTER TABLE " . DB_PREFIX . "info_article ADD `content_status` TINYINT(1) NOT NULL DEFAULT '0'");
	}

	public function uninstall() {
		$this->db->query("ALTER TABLE " . DB_PREFIX . "info_article DROP COLUMN `content_status`");
	}
}