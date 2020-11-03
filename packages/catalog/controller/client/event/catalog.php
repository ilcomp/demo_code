<?php
namespace Controller\Event;

class Catalog extends \Controller {
	public function startup() {
		$code = '';

		$this->load->model('localisation/currency');

		$currencies = $this->model_localisation_currency->getCurrencies();

		if (isset($this->session->data['currency'])) {
			$code = $this->session->data['currency'];
		}

		if (isset($this->request->cookie['currency']) && !array_key_exists($code, $currencies)) {
			$code = $this->request->cookie['currency'];
		}

		if (!array_key_exists($code, $currencies)) {
			$code = $this->config->get('catalog_currency');
		}

		if (!isset($this->session->data['currency']) || $this->session->data['currency'] != $code) {
			$this->session->data['currency'] = $code;
		}

		// Set a new currency cookie if the code does not match the current one
		if (!isset($this->request->cookie['currency']) || $this->request->cookie['currency'] != $code) {
			setcookie('currency', $code, time() + 60 * 60 * 24 * 30, '/', $this->request->server['HTTP_HOST']);
		}

		$this->registry->set('currency', new \Model\Registry\Currency($this->registry));

		$this->load->model('catalog/price');

		$this->config->set('catalog_price_id', $this->model_catalog_price->getPriceIdMain());

		if ($this->currency->get($this->session->data['currency']))
			$this->language->set('catalog_price_format', $this->currency->get($this->session->data['currency']));

		$this->event->register('model/core/custom_field/getAsValue/before', new \Action('event/catalog/getAsValue'), 10);

		$this->event->register('controller/common/template/before', new \Action('event/catalog/template'), 0);
		$this->event->register('controller/common/search/before', new \Action('event/catalog/search'), 0);
		$this->event->register('controller/common/search/autocomplete/before', new \Action('event/catalog/search_autocomplete'), 0);

		$this->load->controller('event/catalog_permission');
	}

	public function getAsValue($route, &$args) {
		if ($args[1]['type'] == 'catalog_product') {
			$this->load->model('catalog/product');

			$values = json_decode($args[0], true);

			$products = array();

			if (is_array($values)) {
				foreach ($values as $product_id) {
					$product = $this->model_catalog_product->getProduct($product_id);

					if ($product)
						$products[] = $product;
				}
			}

			$args[0] = $products;
		} elseif ($args[1]['type'] == 'catalog_attribute') {
			$this->load->model('localisation/listing');
			$this->load->model('tool/image');

			$value = json_decode($args[0], true);

			$result = array();

			if (is_array($value)) {
				if (isset($value['listing_item'])) {
					foreach ($value['listing_item'] as $listing_item) {
						$listing_item_info = $this->model_localisation_listing->getListItem($listing_item['listing_item_id']);

						if ($listing_item_info) {
							$result[] = array(
								'name' => $listing_item_info['name'],
								'image' => $this->model_tool_image->compress($listing_item_info['image']),
								'value' => $listing_item['value'][$this->config->get('config_language_id')]
							);
						}
					}
				}
			}

			$args[0] = $result;
		}
	}

	public function template($route, &$data) {
		if (isset($this->request->get['route']) && $this->request->get['route'] == 'product/category' && isset($this->request->get['catalog_category_id'])) {
			$data[1]['user_route'] = 'catalog/category/edit';
			$data[1]['user_args'] = 'catalog_category_id=' . $this->request->get['catalog_category_id'];
		}

		if (isset($this->request->get['route']) && $this->request->get['route'] == 'product/product' && isset($this->request->get['catalog_product_id'])) {
			$data[1]['user_route'] = 'catalog/product/edit';
			$data[1]['user_args'] = 'product_id=' . $this->request->get['catalog_product_id'];
		}
	}

	public function search($route, &$args) {
		if (isset($this->request->get['search']) && preg_replace('/[\s]/', '', $this->request->get['search'])) {
			$search = $this->request->get['search'];
		} else {
			$search = '';
		}

		if ($search) {
			if (isset($this->request->get['page'])) {
				$page = $this->request->get['page'];
			} else {
				$page = 1;
			}

			if ($this->config->get('theme_' . $this->config->get('config_theme') . '_search_limit')) {
				$limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_search_limit');
			} elseif ($this->config->get('config_limit')) {
				$limit = (int)$this->config->get('config_limit');
			} else {
				$limit = 12;
			}

			if (!isset($args[0]['total']))
				$args[0]['total'] = 0;

			if (!isset($args[0]['count']))
				$args[0]['count'] = 0;

			$this->load->model('catalog/product');

			$filter_data = array(
				'filter' => array('search' => $search),
				'sort'   => 'name',
				'order'  => 'ASC',
				'start'  => ($page - 1) * $limit - $args[0]['total'] + $args[0]['count'],
				'limit'  => $limit - $args[0]['count']
			);

			if ($limit - $args[0]['count'] > 0) {
				$products = $this->model_catalog_product->getProducts($filter_data);

				$args[0]['count'] += count($products);

				$args[0]['products'] = $this->load->controller('catalog/product_list', array(
					'products' => $products,
					'type' => 'search'
				));
			}

			$args[0]['total'] += $this->model_catalog_product->getTotalProducts($filter_data);
		}
	}

	public function search_autocomplete($route, &$args) {
		if (isset($this->request->get['search']) && preg_replace('/[\s]/', '', $this->request->get['search'])) {
			$search = $this->request->get['search'];
		} else {
			$search = '';
		}

		if ($search) {
			if ($this->config->get('theme_' . $this->config->get('config_theme') . '_search_limit')) {
				$limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_search_limit');
			} elseif ($this->config->get('config_limit')) {
				$limit = (int)$this->config->get('config_limit');
			} else {
				$limit = 12;
			}

			if (!isset($args[0]['search_results']))
				$args[0]['search_results'] = array();

			$count = count($args[0]['search_results']);

			if ($limit - $count > 0) {
				$this->load->model('catalog/product');

				$filter_data = array(
					'filter' => array('search' => $search),
					'sort'   => 'name',
					'order'  => 'ASC',
					'limit'  => $limit - $count
				);

				$products = $this->model_catalog_product->getProducts($filter_data);

				foreach ($products as $product) {
					if (empty($product['title']))
						$product['title'] = $product['name'];

					$product['href'] = $this->url->link('catalog/product', 'catalog_product_id=' . $product['product_id']);

					$args[0]['search_results'][] = $product;
				}
			}
		}
	}
}