<?php
namespace Model\Catalog;

class Price extends \Model {
	public function addPrice($data) {
		if ((int)$data['main']) {
			$this->db->query("UPDATE " . DB_PREFIX . "catalog_price SET `main` = 0");
		}

		$this->db->query("INSERT INTO " . DB_PREFIX . "catalog_price SET currency_id = '" . (int)$data['currency_id'] . "', `main` = '" . (int)$data['main'] . "'");

		$price_id = $this->db->getLastId();

		foreach ($data['price_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "catalog_price_description SET price_id = '" . (int)$price_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
		}
		
		return $price_id;
	}

	public function editPrice($price_id, $data) {
		if ((int)$data['main']) {
			$this->db->query("UPDATE " . DB_PREFIX . "catalog_price SET `main` = 0");
		}

		$this->db->query("UPDATE " . DB_PREFIX . "catalog_price SET currency_id = '" . (int)$data['currency_id'] . "', `main` = '" . (int)$data['main'] . "' WHERE price_id = '" . (int)$price_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_price_description WHERE price_id = '" . (int)$price_id . "'");

		foreach ($data['price_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "catalog_price_description SET price_id = '" . (int)$price_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
		}
	}

	public function deletePrice($price_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_product_price WHERE price_id = '" . (int)$price_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_price_description WHERE price_id = '" . (int)$price_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_price WHERE price_id = '" . (int)$price_id . "'");
	}

	public function getPrice($price_id) {
		$query = $this->db->query("SELECT DISTINCT cpd.*, cp.* FROM " . DB_PREFIX . "catalog_price cp LEFT JOIN " . DB_PREFIX . "catalog_price_description cpd ON (cp.price_id = cpd.price_id AND cpd.language_id = '" . (int)$this->config->get('config_language_id') . "') WHERE cp.price_id = '" . (int)$price_id . "'");

		return $query->row;
	}

	public function getPrices($data = array()) {
		$sql = "SELECT c.*, cpd.*, cp.* FROM " . DB_PREFIX . "catalog_price cp LEFT JOIN " . DB_PREFIX . "catalog_price_description cpd ON (cp.price_id = cpd.price_id AND cpd.language_id = '" . (int)$this->config->get('config_language_id') . "') LEFT JOIN " . DB_PREFIX . "currency c ON (cp.currency_id = c.currency_id)";

		$sql .= " ORDER BY cp.main, cp.currency_id";

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

	public function getTotalPrices() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "catalog_price");

		return $query->row['total'];
	}

	public function getPriceDescriptions($price_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "catalog_price_description WHERE price_id = '" . (int)$price_id . "'");

		return array_column($query->rows, null, 'language_id');
	}

	public function getPriceIdMain() {
		$query = $this->db->query("SELECT price_id FROM " . DB_PREFIX . "catalog_price WHERE `main` = '1' LIMIT 1");

		return ($query->num_rows ? $query->row['price_id'] : '');
	}
}