<?php
namespace Controller\Event;

class Stock extends \Controller {
	public function startup() {
		$this->event->register('view/block/column_left/before', new \Action('event/stock/menu'), 0);

		$this->event->register('view/catalog/product_list/before', new \Action('event/stock/product_list'), 0);
		$this->event->register('view/catalog/product_form/before', new \Action('event/stock/product_form'), 0);
		$this->event->register('view/catalog/option_variant_view/before', new \Action('event/stock/option_variant_view'), 0);

		$this->event->register('model/catalog/product_modify/*Product/after', new \Action('event/stock/model_product'), 0);
		$this->event->register('model/catalog/option_variant/getOptionVariants/after', new \Action('event/stock/getOptionVariants'), 0);
		$this->event->register('model/catalog/option_variant/updateOptionVariants/after', new \Action('event/stock/updateOptionVariants'), 0);
	}

	public function menu($route, &$data) {
		$this->load->language('extension/system/stock', 'temp');

		$language = $this->language->get('temp');

		$route = isset($this->request->get['route']) ? $this->request->get['route'] : $this->config->get('active_default');

		if ($this->user->hasPermission('access', 'catalog/stock')) {
			$stock_active = strpos($route, 'catalog/stock') === 0;

			foreach ($data['menus'] as &$value) {
				if ($value['id'] == 'menu-catalog') {
					if ($stock_active)
						$value['active'] = true;

					$value['children'][] = array(
						'name'	   => $language->get('menu_stock'),
						'href'     => $this->url->link('catalog/stock', 'user_token=' . $this->session->data['user_token']),
						'children' => array(),
						'active'   => $stock_active
					);
					break;
				}
			}
			unset($value);
		}

		if ($this->user->hasPermission('access', 'localisation/length_class') || $this->user->hasPermission('access', 'localisation/weight_class')) {
			$length_class_active = $this->user->hasPermission('access', 'localisation/length_class') && strpos($route, 'localisation/length_class') === 0;
			$weight_class_active = $this->user->hasPermission('access', 'localisation/weight_class') && strpos($route, 'localisation/weight_class') === 0;

			foreach ($data['menus'] as &$value) {
				if ($value['id'] == 'menu-system') {
					if ($length_class_active || $weight_class_active)
						$value['active'] = true;

					foreach ($value['children'] as &$value2) {
						if (isset($value2['id']) && $value2['id'] == 'menu-localisation') {
							if ($length_class_active || $weight_class_active)
								$value2['active'] = true;

							if ($this->user->hasPermission('access', 'localisation/length_class'))
								$value2['children'][] = array(
									'name'     => $this->language->get('text_length_class'),
									'href'     => $this->url->link('localisation/length_class', 'user_token=' . $this->session->data['user_token']),
									'children' => array(),
									'active'   => $length_class_active
								);

							if ($this->user->hasPermission('access', 'localisation/weight_class'))
								$value2['children'][] = array(
									'name'     => $this->language->get('text_weight_class'),
									'href'     => $this->url->link('localisation/weight_class', 'user_token=' . $this->session->data['user_token']),
									'children' => array(),
									'active'   => $weight_class_active
								);

							break;
						}
					}
				}
			}
			unset($value2);
			unset($value);
		}
	}

	public function product_list($route, &$data) {
		// if ($this->config->get('stock_status')) {
		// 	$this->load->model('catalog/stock');

		// 	foreach ($data['products'] as &$product) {
		// 		$product_stock = $this->model_catalog_stock->getProductStock($product['product_id']);

		// 		if ($product_stock) {
		// 			$product['sku'] = $product_stock['sku'];
		// 		}
		// 	}
		// 	unset($product);
		// }
	}

	public function product_form($route, &$data) {
		$this->load->language('extension/system/stock', 'temp');

		$this->load->model('catalog/stock');
		$this->load->model('catalog/stock_product');
		$this->load->model('localisation/listing');
		$this->load->model('localisation/length_class');
		$this->load->model('localisation/weight_class');

		$language = $this->language->get('temp');

		$data_stock = $language->all();

		$data_stock['user_token'] = $this->session->data['user_token'];
		$data_stock['product_id'] = isset($this->request->get['product_id']) ? $this->request->get['product_id'] : 0;

		if (isset($this->request->post['product_stock_data'])) {
			$data_stock['product_stock_data'] = (array)$this->request->post['product_stock_data'];
		} elseif (isset($this->request->get['product_id'])) {
			$data_stock['product_stock_data'] = $this->model_catalog_stock_product->getProductStockData($this->request->get['product_id']);
		} else {
			$data_stock['product_stock_data'] = array(
				'shipping' => 1,
				'minimum' => 1,
				'weight_class_id' => $this->config->get('stock_weight_class'),
				'length_class_id' => $this->config->get('stock_length_class'),
			);
		}

		if (isset($this->request->post['product_stock'])) {
			$data_stock['product_stock'] = (array)$this->request->post['product_stock'];
		} elseif (isset($this->request->get['product_id'])) {
			$data_stock['product_stock'] = $this->model_catalog_stock_product->getProductStock($this->request->get['product_id']);
		} else {
			$data_stock['product_stock'] = array();
		}

		$data_stock['stocks'] = $this->model_catalog_stock->getStocks();

		$data_stock['listing_status'] = $this->config->get('stock_listing_status') ? $this->model_localisation_listing->getListingItems(array('filter_listing_id' => $this->config->get('stock_listing_status'))) : array();
		$data_stock['weight_classes'] = $this->model_localisation_weight_class->getWeightClasses();
		$data_stock['length_classes'] = $this->model_localisation_length_class->getLengthClasses();

		$data['additional_fields'] .= $this->load->view('catalog/stock_view', $data_stock);
	}

	public function model_product($route, $data, $output = '') {
		if ((int)$output) {
			if ($route == 'catalog/product_modify/addProduct') {
				$this->load->model('catalog/stock_product');

				if (!isset($data[0]))
					$data[0] = array();

				$this->model_catalog_stock_product->updateProductStock((int)$output, $data[0]);
			}
		} elseif (isset($data[0]) && (int)$data[0]) {
			if ($route == 'catalog/product_modify/editProduct') {
				$this->load->model('catalog/stock_product');

				if (!isset($data[1]))
					$data[1] = array();

				$this->model_catalog_stock_product->updateProductStock((int)$data[0], $data[1]);
			} elseif ($route == 'catalog/product_modify/deleteProduct') {
				$this->load->model('catalog/stock_product');

				$this->model_catalog_stock_product->deleteProductStock((int)$data[0]);
			}
		}
	}


	public function option_variant_view($route, &$data) {
		$this->load->language('extension/system/stock', 'temp');

		$language = $this->language->get('temp');

		foreach ($language->all() as $key => $value) {
			if (!isset($data[$key]))
				$data[$key] = $value;
		}

		$this->load->model('catalog/stock');

		$data['stocks'] = $this->model_catalog_stock->getStocks();

		$file = '/template/catalog/stock_option_variant_view';

		if (is_file(DIR_TEMPLATE . $this->config->get('config_theme') . $file . '.twig'))
			$data['files'][] = $this->config->get('config_theme') . $file;
		elseif (is_file(DIR_TEMPLATE . $this->config->get('theme_default') . $file . '.twig'))
			$data['files'][] = $this->config->get('theme_default') . $file;
	}

	public function getOptionVariants($route, $data, &$output = '') {
		$this->load->model('catalog/stock_product');

		$output = $this->model_catalog_stock_product->getOptionVariantsStock($output);
	}

	public function updateOptionVariants($route, $data, $output = '') {
		$this->load->model('catalog/stock_product');

		$this->model_catalog_stock_product->updateOptionVariantsStock($output);
	}
}