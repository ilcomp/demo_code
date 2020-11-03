<?php
namespace Controller\Event;

class Stock extends \Controller {
	public function startup() {
		if (isset($this->request->get['route']) && (strpos($this->request->get['route'], 'catalog') !== false || strpos($this->request->get['route'], 'order') !== false || strpos($this->request->get['route'], 'cart') !== false || strpos($this->request->get['route'], 'shipping') !== false)) {
			$this->registry->set('weight', new \Model\Registry\Weight($this->registry));
			$this->registry->set('length', new \Model\Registry\Length($this->registry));
		}

		$this->event->register('model/registry/cart/getProducts', new \Action('event/stock/getProducts'), 0);
		$this->event->register('model/catalog/option_variant*/getOptionVariants/after', new \Action('event/stock/getOptionVariants'), 0);
		//$this->event->register('model/order/order_history/addOrderHistory/after', new \Action('event/stock/addOrderHistory'), 0);
		$this->event->register('model/catalog/product/getPriceTotal/after', new \Action('event/stock/getPriceTotal'), 100);

		$this->event->register('view/catalog/product_list/before', new \Action('event/stock/product_list'), 0);
		$this->event->register('view/catalog/product/before', new \Action('event/stock/product'), 0);


		$this->event->register('model/registry/cart/construct', new \Action('event/stock/cart'), 0);
	}

	public function cart(&$cart) {
		$cart->weight = $this->registry->get('weight');
		$cart->getWeight = function() {
			$weight = 0;

			foreach ($this->getProducts() as $product) {
				if (!empty($product['shipping'])) {
					$weight += $this->weight->convert($product['weight'], $product['weight_class_id'], $this->config->get('config_weight_class_id'));
				}
			}

			return $weight;
		};
		$cart->hasStock = function() {
			foreach ($this->getProducts() as $product) {
				if (isset($product['stock']) && !$product['stock']) {
					return false;
				}
			}

			return true;
		};
		$cart->hasShipping = function() {
			foreach ($this->getProducts() as $product) {
				if (!empty($product['shipping'])) {
					return true;
				}
			}

			return false;
		};
	}

	public function getProducts(&$data) {
		$this->load->model('catalog/stock_product');

		$data = $this->model_catalog_stock_product->getCartProducts($data);
	}

	public function getPriceTotal($route, $data, &$output) {
		$this->load->model('catalog/stock_product');

		if (empty($output)) {
			$output['product_id'] = isset($data[0]['product_id']) ? $data[0]['product_id'] : 0;
			$output['price'] = 0;
		}

		$output['option'] = isset($data[0]['option']) ? $data[0]['option'] : array();
		$output['quantity'] = isset($data[0]['quantity']) ? $data[0]['quantity'] : 1;

		$result = $this->model_catalog_stock_product->getCartProducts(array($output));

		$result = array_shift($result);

		if (!empty($result['variant']))
			$output = $result;
	}

	public function addOrderHistory($route, $arg) {
		if (!(int)$arg[0]['order_status_id'] && (int)$arg[1]) {
			$this->load->model('catalog/stock_product');

			$this->model_catalog_stock_product->removeProductQuantityByOrderID($arg[0]['order_id']);
		}
	}

	public function getOptionVariants($route, $data, &$output = '') {
		$this->load->model('catalog/stock_product');

		$output = $this->model_catalog_stock_product->getOptionVariantsStock($output);
	}

	public function product_list($route, &$data) {
		$this->load->model('catalog/stock_product');

		foreach ($data['products'] as &$product) {
			$product['stock_data'] = $this->model_catalog_stock_product->getProductStockData($product['product_id']);
		}
		unset($product);
	}

	public function product($route, &$data) {
		if (isset($this->request->get['catalog_product_id'])) {
			$this->load->model('catalog/stock_product');

			$data['stock'] = $this->model_catalog_stock_product->getProductStock($this->request->get['catalog_product_id']);
			$data['stock_data'] = $this->model_catalog_stock_product->getProductStockData($this->request->get['catalog_product_id']);
		}
	}
}
