<?php
namespace Controller\Event;

class CatalogSpecial extends \Controller {
	public function startup() {
		if ($this->config->get('catalog_special_status')) {
			$this->event->register('model/catalog/product/getProducts/before', new \Action('event/catalog_special/getProducts'), 0);
			$this->event->register('model/catalog/product/getProduct/sql', new \Action('event/catalog_special/getProduct_sql'), 0);
			$this->event->register('model/catalog/product/getProducts/sql', new \Action('event/catalog_special/getProducts_sql'), 0);
			$this->event->register('model/catalog/product/getTotalProducts/sql', new \Action('event/catalog_special/getTotalProducts_sql'), 0);
			$this->event->register('model/registry/cart/getProducts', new \Action('event/catalog_special/cart'), 0);
		}
	}

	public function getProducts($route, &$data) {
		if (!empty($this->request->get['filter_special']))
			$data[0]['filter']['special'] = true;
	}

	public function getProduct_sql($data, &$join, &$where, &$columns) {
		$columns[] = "(SELECT cs.value FROM " . DB_PREFIX . "catalog_special cs WHERE cs.product_id = p.product_id AND ((cs.date_start = '0000-00-00' OR cs.date_start < NOW()) AND (cs.date_end = '0000-00-00' OR cs.date_end > NOW())) ORDER BY priority, value LIMIT 1) as special";
	}

	public function getProducts_sql($data, &$join, &$where, &$order_by) {
		$filter = isset($data['filter']) && is_array($data['filter']) ? $data['filter'] : array();

		if (!empty($filter['special']) || (isset($data['sort']) && $data['sort'] == 'price')) {
			$join[] = "LEFT JOIN " . DB_PREFIX . "catalog_special cs ON (cs.product_id = p.product_id AND (cs.date_start = '0000-00-00' OR cs.date_start < NOW()) AND (cs.date_end = '0000-00-00' OR cs.date_end > NOW()))";

			if (!empty($filter['special']))
				$where[] = "cs.value IS NOT NULL";

			if (isset($data['sort']) && $data['sort'] == 'price') {
				array_shift($order_by);

				array_unshift($order_by, 'IF(cs.value IS NOT NULL AND cs.value < pp.price, cs.value, pp.price)' . (isset($data['order']) && $data['order'] == 'DESC' ? ' DESC' : ' ASC'));
			}
		}
	}

	public function getTotalProducts_sql($data, &$join, &$where, &$order_by) {
		$filter = isset($data['filter']) && is_array($data['filter']) ? $data['filter'] : array();

		if (!empty($filter['special'])) {
			$join[] = "LEFT JOIN " . DB_PREFIX . "catalog_special cs ON (cs.product_id = p.product_id AND (cs.date_start = '0000-00-00' OR cs.date_start < NOW()) AND (cs.date_end = '0000-00-00' OR cs.date_end > NOW()))";

			$where[] = "cs.value IS NOT NULL";
		}
	}

	public function cart(&$data) {
		$this->load->model('extension/system/catalog_special');

		$data = $this->model_extension_system_catalog_special->getCartProducts($data);
	}
}