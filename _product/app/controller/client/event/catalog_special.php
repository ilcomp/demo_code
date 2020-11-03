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

			$this->event->register('view/catalog/product/before', new \Action('event/catalog_special/product'), 0);
			$this->event->register('view/catalog/product_list/before', new \Action('event/catalog_special/product_list'), 0);
			$this->event->register('view/catalog/category/before', new \Action('event/catalog_special/category'), 0);
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

		if (!empty($data['filter']['special']) || (isset($data['sort']) && $data['sort'] == 'price')) {
			$join[] = "LEFT JOIN " . DB_PREFIX . "catalog_special cs ON (cs.product_id = p.product_id AND (cs.date_start = '0000-00-00' OR cs.date_start < NOW()) AND (cs.date_end = '0000-00-00' OR cs.date_end > NOW()))";

			if (!empty($data['filter']['special']))
				$where[] = "cs.value IS NOT NULL";

			if (isset($data['sort']) && $data['sort'] == 'price') {
				array_shift($order_by);

				if ($this->config->get('catalog_special_operator') == '%')
					array_unshift($order_by, 'IF(cs.value IS NOT NULL AND cs.value, pp.price * (1 + cs.value), pp.price)' . (isset($data['order']) && $data['order'] == 'DESC' ? ' DESC' : ' ASC'));
				else
					array_unshift($order_by, 'IF(cs.value IS NOT NULL AND cs.value AND cs.value < pp.price, cs.value, pp.price)' . (isset($data['order']) && $data['order'] == 'DESC' ? ' DESC' : ' ASC'));
			}
		}
	}

	public function getTotalProducts_sql($data, &$join, &$where, &$order_by) {
		$filter = isset($data['filter']) && is_array($data['filter']) ? $data['filter'] : array();

		if (!empty($data['filter']['special'])) {
			$join[] = "LEFT JOIN " . DB_PREFIX . "catalog_special cs ON (cs.product_id = p.product_id AND (cs.date_start = '0000-00-00' OR cs.date_start < NOW()) AND (cs.date_end = '0000-00-00' OR cs.date_end > NOW()))";

			$where[] = "cs.value IS NOT NULL";
		}
	}

	public function cart(&$data) {
		$this->load->model('extension/system/catalog_special');

		$data = $this->model_extension_system_catalog_special->getCartProducts($data);
	}

	public function product($route, &$data) {
		$this->load->language('extension/system/catalog_special');

		if ($this->config->get('catalog_special_operator') == '%') {
			$data['special_percent'] = (float)$data['special'];
			$data['special'] = (float)$data['special'] ? (1 - (float)$data['special'] / 100) * (float)$data['price'] : 0;
		} else {
			$data['special'] = (float)$data['special'];
			$data['special_percent'] = (float)$data['price'] ? (1 - (float)$data['special'] / (float)$data['price']) * 100 : 0;

			if ($data['special'] > (float)$data['price'])
				$data['special'] = 0;
		}

		if ($data['special']) {
			$data['special_format'] = $this->currency->format($data['special'], $this->session->data['currency']);

			if (!isset($data['attributes']['flags']) || !is_array($data['attributes']['flags']))
				$data['attributes']['flags'] = array();

			$data['attributes']['flags'][] = array('value' => 'discount', 'name' => sprintf($this->language->get('text_catalog_special'), (int)$data['special_percent']));
		}
	}

	public function product_list($route, &$data) {
		$this->load->language('extension/system/catalog_special');

		foreach ($data['products'] as &$product) {
			$product['special'] = (float)$product['special'];

			if ($product['special'] > $product['price'])
				$product['special'] = 0;

			if ($this->config->get('catalog_special_operator') == '%') {
				$product['special_percent'] = (float)$product['special'];
				$product['special'] = (float)$product['special'] ? (1 - (float)$product['special'] / 100) * (float)$product['price'] : 0;
			} else {
				$product['special'] = (float)$product['special'];
				$product['special_percent'] = (float)$product['price'] ? (1 - (float)$product['special'] / (float)$product['price']) * 100 : 0;

				if ($product['special'] > (float)$product['price'])
					$product['special'] = 0;
			}

			if ($product['special']) {
				$product['special_format'] = $this->currency->format($product['special'], $this->session->data['currency']);

				if (!isset($product['attributes']['flags']) || !is_array($product['attributes']['flags']))
					$product['attributes']['flags'] = array();

				$product['attributes']['flags'][] = array('value' => 'discount', 'name' => sprintf($this->language->get('text_catalog_special'), (int)$product['special_percent']));
			}
		}
		unset($product);
	}

	public function category($route, &$data) {
		$this->load->language('extension/system/catalog_special');

		$data['filter_catalog_special'] = !empty($this->request->get['filter_special']) ? 1 : 0;

		$url = $this->request->get;
		unset($url['route']);
		unset($url['_route_']);
		unset($url['page']);
		unset($url['filter_special']);

		if (empty($this->request->get['filter_special']))
			$url['filter_special'] = 1;

		$data['actions']['catalog_special'] = $this->url->link('catalog/category', $url);
	}
}