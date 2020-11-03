<?php
namespace Model\Catalog;

class StockProduct extends \Model {
	public function updateProductStock($product_id, $data) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_product_stock_data WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_product_stock WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_stock_data'])) {
			$product_stock_data = $data['product_stock_data'];

			$sql = "INSERT INTO " . DB_PREFIX . "catalog_product_stock_data SET product_id = '" . (int)$product_id . "'";

			foreach (array('model', 'sku') as $key) {
				if (isset($product_stock_data[$key]))
					$sql .= ", " . (string)$key . " = '" . $this->db->escape((string)$product_stock_data[$key]) . "'";
			}

			foreach (array('subtract', 'minimum', 'shipping', 'weight_class_id', 'length_class_id') as $key) {
				if (isset($product_stock_data[$key]))
					$sql .= ", " . (string)$key . " = '" . (int)$product_stock_data[$key] . "'";
			}

			foreach (array('weight', 'length', 'width', 'height') as $key) {
				if (isset($product_stock_data[$key]))
					$sql .= ", " . (string)$key . " = '" . (float)$product_stock_data[$key] . "'";
			}

			$this->db->query($sql);
		}

		if (isset($data['catalog_product_stock'])) {
			foreach ($data['catalog_product_stock'] as $stock_id => $quantity) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "catalog_product_stock SET product_id = '" . (int)$product_id . "', stock_id = '" . (int)$stock_id . "', quantity = '" . (int)$quantity . "'");
			}
		}
	}

	public function updateOptionVariantsStock($option_variants) {
		$this->db->query("DELETE ovsd FROM " . DB_PREFIX . "option_variant_stock_data ovsd LEFT JOIN " . DB_PREFIX . "option_variant_product ovp ON (ovsd.variant_id = ovp.variant_id) WHERE ovp.variant_id IS NULL");
		$this->db->query("DELETE ovs FROM " . DB_PREFIX . "option_variant_stock ovs LEFT JOIN " . DB_PREFIX . "option_variant_product ovp ON (ovs.variant_id = ovp.variant_id) WHERE ovp.variant_id IS NULL");

		foreach ($option_variants as $variant_id => $option_variant) {
			$this->db->query("DELETE FROM " . DB_PREFIX . "option_variant_stock_data WHERE variant_id = '" . (int)$variant_id . "'");
			$this->db->query("DELETE FROM " . DB_PREFIX . "option_variant_stock WHERE variant_id = '" . (int)$variant_id . "'");

			if (!empty($option_variant['stock_data'])) {
				$stock_data = $option_variant['stock_data'];

				$sql = "INSERT INTO " . DB_PREFIX . "option_variant_stock_data SET variant_id = '" . (int)$variant_id . "'";

				foreach (array('model', 'sku') as $key) {
					if (isset($stock_data[$key]))
						$sql .= ", " . (string)$key . " = '" . $this->db->escape((string)$stock_data[$key]) . "'";
				}

				foreach (array('quantity') as $key) {
					if (isset($stock_data[$key]))
						$sql .= ", " . (string)$key . " = '" . (int)$stock_data[$key] . "'";
				}

				foreach (array('weight', 'length', 'width', 'height') as $key) {
					if (isset($stock_data[$key]))
						$sql .= ", " . (string)$key . " = '" . (float)$stock_data[$key] . "'";
				}

				$this->db->query($sql);
			}

			if (!empty($option_variant['stock'])) {
				foreach ($option_variant['stock'] as $stock) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "option_variant_stock SET variant_id = '" . (int)$variant_id . "', stock_id = '" . (int)$stock['stock_id'] . "', quantity = '" . (int)$stock['quantity'] . "'");
				}
			}
		}
	}

	public function deleteProductStock($product_id) {
		$this->db->query("DELETE ovsd FROM " . DB_PREFIX . "option_variant_stock_data ovsd LEFT JOIN " . DB_PREFIX . "option_variant_product ovp ON (ovsd.variant_id = ovp.variant_id) WHERE ovp.variant_id IS NULL");
		$this->db->query("DELETE ovs FROM " . DB_PREFIX . "option_variant_stock ovs LEFT JOIN " . DB_PREFIX . "option_variant_product ovp ON (ovs.variant_id = ovp.variant_id) WHERE ovp.variant_id IS NULL");

		$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_product_stock_data WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "catalog_product_stock WHERE product_id = '" . (int)$product_id . "'");
	}

	public function getProductStockData($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "catalog_product_stock_data WHERE product_id = '" . (int)$product_id . "'");

		return $query->row;
	}

	public function getProductStock($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "catalog_product_stock WHERE product_id = '" . (int)$product_id . "'");

		return $query->rows;
	}

	public function getOptionVariantsStock($option_variants) {
		foreach ($option_variants as &$option_variant) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "option_variant_stock_data WHERE variant_id = '" . (int)$option_variant['variant_id'] . "'");

			$option_variant['stock_data'] = $query->num_rows ? $query->row : '';

			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "option_variant_stock WHERE variant_id = '" . (int)$option_variant['variant_id'] . "'");

			$option_variant['stock'] = $query->rows;

			$option_variant['quantity'] = 0;

			foreach ($option_variant['stock'] as $stock) {
				$option_variant['quantity'] += $stock['quantity'];
			}
		}
		unset($option_variant);

		return $option_variants;
	}

	public function getCartProducts($data) {
		foreach ($data as &$cart) {
			$query = $this->db->query("SELECT cpsd.*, SUM(cps.quantity) as maximum FROM " . DB_PREFIX . "catalog_product_stock_data cpsd LEFT JOIN " . DB_PREFIX . "catalog_product_stock cps ON (cpsd.product_id = cps.product_id) WHERE cpsd.product_id = '" . (int)$cart['product_id'] . "'");

			if ($query->num_rows) {
				foreach ($query->row as $key => $value) {
					if ($key == 'weight')
						$value *= $cart['quantity'];

					if (!empty($value))
						$cart[$key] = $value;
				}
			}

			if (isset($cart['variant'])) {
				foreach($cart['variant'] as $variant_id) {
					$query = $this->db->query("SELECT ovsd.*, SUM(ovs.quantity) as maximum FROM " . DB_PREFIX . "option_variant_stock_data ovsd LEFT JOIN " . DB_PREFIX . "option_variant_stock ovs ON (ovsd.variant_id = ovs.variant_id) WHERE ovsd.variant_id = '" . (int)$variant_id . "'");

					if ($query->num_rows) {
						foreach ($query->row as $key => $value) {
							if ($key == 'weight')
								$value *= $cart['quantity'];

							if (!empty($value))
								$cart[$key] = $value;
						}
					}
				}
			}

			if (!isset($cart['maximum']))
				$cart['maximum'] = 0;

			if (empty($cart['minimum']))
				$cart['minimum'] = 1;

			$cart['availability'] = 0;
		}
		unset($cart);

		return $data;
	}

	public function removeProductQuantityByOrderID($order_id) {
		$this->db->query("UPDATE " . DB_PREFIX . "order_product op LEFT JOIN " . DB_PREFIX . "catalog_product_stock cps ON (op.sku = cps.sku) SET cps.quantity = GREATEST(0, cps.quantity - op.quantity) WHERE op.order_id = '" . (int)$order_id . "' AND op.sku <> '' AND cps.quantity > 0");
		$this->db->query("UPDATE " . DB_PREFIX . "order_product op LEFT JOIN " . DB_PREFIX . "option_variant_stock ovs ON (op.sku = ovs.sku) SET ovs.quantity = GREATEST(0, ovs.quantity - op.quantity) WHERE op.order_id = '" . (int)$order_id . "' AND op.sku <> '' AND ovs.quantity > 0");
	}
}