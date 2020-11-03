<?php
namespace Model\Extension\System;

class CatalogSpecial extends \Model {
	public function updateCatalogSpecials($product_id, $data = array()) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "catalog_special` WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['catalog_special'])) {
			foreach ($data['catalog_special'] as $catalog_special) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "catalog_special SET product_id = '" . (int)$product_id . "', priority = '" . (int)$catalog_special['priority'] . "', value = '" . (float)$catalog_special['value'] . "', date_start = '" . $this->db->escape($catalog_special['date_start']) . "', date_end = '" . $this->db->escape($catalog_special['date_end']) . "'");
			}
		}
	}

	public function getCatalogSpecials($product_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "catalog_special` WHERE product_id = '" . (int)$product_id . "' ORDER BY priority, value");

		return $query->rows;
	}

	public function getCartProducts($data) {
		foreach ($data as $key => &$cart) {
			$query = $this->db->query("SELECT cs.value FROM " . DB_PREFIX . "catalog_special cs WHERE cs.product_id = '" . (int)$cart['product_id'] . "' AND ((cs.date_start = '0000-00-00' OR cs.date_start < NOW()) AND (cs.date_end = '0000-00-00' OR cs.date_end > NOW())) ORDER BY priority, value LIMIT 1");

			if ($query->num_rows && (float)$query->row['value'] && (float)$query->row['value'] < (float)$cart['price']) {
				$cart['price_old'] = $cart['price'];
				$cart['price'] = $query->row['value'];

				$cart['total'] = $cart['price'] * $cart['quantity'];
			} else {
				$cart['price_old'] = '';
			}
		}
		unset($cart);

		return $data;
	}
}