<?php
namespace Model\Catalog;

class Stock extends \Model {
	public function addStock($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "stock SET location_id = '" . (int)$data['location_id'] . "'");

		$stock_id = $this->db->getLastId();

		foreach ($data['stock_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "stock_description SET stock_id = '" . (int)$stock_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
		}
		
		return $stock_id;
	}

	public function editStock($stock_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "stock SET location_id = '" . (int)$data['location_id'] . "' WHERE stock_id = '" . (int)$stock_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "stock_description WHERE stock_id = '" . (int)$stock_id . "'");

		foreach ($data['stock_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "stock_description SET stock_id = '" . (int)$stock_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
		}
	}

	public function deleteStock($stock_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "option_variant_stock WHERE stock_id = '" . (int)$stock_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_product_stock WHERE stock_id = '" . (int)$stock_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "stock_description WHERE stock_id = '" . (int)$stock_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "stock WHERE stock_id = '" . (int)$stock_id . "'");
	}

	public function getStock($stock_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "stock s LEFT JOIN " . DB_PREFIX . "stock_description sd ON (s.stock_id = sd.stock_id AND sd.language_id = '" . (int)$this->config->get('config_language_id') . "') WHERE s.stock_id = '" . (int)$stock_id . "' LIMIT 1");

		return $query->row;
	}

	public function getStocks($data = array()) {
		$sql = "SELECT ld.name AS `location`, sd.*, s.* FROM " . DB_PREFIX . "stock s LEFT JOIN " . DB_PREFIX . "stock_description sd ON (s.stock_id = sd.stock_id AND sd.language_id = '" . (int)$this->config->get('config_language_id') . "') LEFT JOIN " . DB_PREFIX . "location_description ld ON (s.location_id = ld.location_id AND ld.language_id = '" . (int)$this->config->get('config_language_id') . "')";

		$sql .= " ORDER BY sd.name, ld.location_id";

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

	public function getTotalStocks() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "stock");

		return $query->row['total'];
	}

	public function getStockDescriptions($stock_id) {
		$query = $this->db->query("SELECT name, language_id FROM " . DB_PREFIX . "stock_description WHERE stock_id = '" . (int)$stock_id . "'");

		return array_column($query->rows, null, 'language_id');
	}
}