<?php
namespace Controller\Event;

class WSTTGCatalog extends \Controller {
	public function index() {
		$this->event->register('model/catalog/product/getPriceTotal/after', new \Action('event/ws_ttg_catalog/getPriceTotal'), 0);
		$this->event->register('model/catalog/product/getProduct/before', new \Action('event/ws_ttg_catalog/getProduct'), 0);
		$this->event->register('model/catalog/product/getProducts/before', new \Action('event/ws_ttg_catalog/getProducts'), 100);
		$this->event->register('model/catalog/product/getProducts/sql', new \Action('event/ws_ttg_catalog/getProducts_sql'), 0);

		$this->event->register('view/block/menu/before', new \Action('event/ws_ttg_catalog/menu'), 0);
		$this->event->register('view/catalog/category/before', new \Action('event/ws_ttg_catalog/category'), 0);
		$this->event->register('view/catalog/product_list/before', new \Action('event/ws_ttg_catalog/product_list'), 100);
		$this->event->register('view/catalog/product/before', new \Action('event/ws_ttg_catalog/product'), 100);
	}

	public function getPriceTotal($route, $data, &$output) {
		if ($output) {
			$this->load->model('catalog/product');

			$output['option'] = isset($data[0]['option']) ? $data[0]['option'] : array();
			$output['quantity'] = isset($data[0]['quantity']) ? $data[0]['quantity'] : 1;

			$exlude = (array)$this->config->get('theme_client_ws_ttg_construstor_exlude');
			$exlude[] = (int)$this->config->get('theme_client_ws_ttg_construstor_product_id');

			$price = 0;
			$special = 0;

			if (isset($output['option']['composition'])) {
				$products = array();

				foreach ($output['option']['composition'] as $product_id) {
					if (in_array((int)$product_id, $exlude))
						continue;

					$product = $this->model_catalog_product->getProduct($product_id);

					if (!empty($product['price'])) {
						$price += $this->currency->convert($product['price'], $product['currency_id'], $this->session->data['currency']);

						$products[] = $product_id;
					}
				}

				$output['option']['composition'] = $products;

				$len = count($products);

				if ($len > 4) {
					$special = 0.2;
				} elseif ($len > 3) {
					$special = 0.15;
				} elseif ($len > 2) {
					$special = 0.1;
				}
			} else {
				$output['option']['composition'] = array();
			}

			$output['price'] = $price;
			$output['total'] = $output['price'] * $output['quantity'];

			$output['special'] = $price * (1 - $special);
			$output['total_special'] = $output['special'] * $output['quantity'];
		}
	}

	public function getProduct($route, &$data) {
		if (!empty($data[1]['composition']))
			unset($data[1]['filter']['status']);
	}

	public function getProducts($route, &$data) {
		if (isset($data[0]['filter']) && isset($data[0]['filter']['composition_exlude'])) {
			unset($data[0]['filter']['special']);
		} else {
			$data[0]['filter']['composition_exlude'] = array($this->config->get('theme_client_ws_ttg_construstor_product_id'));
		}
	}

	public function getProducts_sql($data, &$join, &$where, &$order_by) {
		$filter = isset($data['filter']) && is_array($data['filter']) ? $data['filter'] : array();

		if (isset($filter['composition_exlude'])) {
			$implode = array();

			foreach ($filter['composition_exlude'] as $product_id) {
				$implode[] = (int)$product_id;
			}

			array_unique($implode);

			if (count($implode) > 1) {
				$where[] = "p.product_id NOT IN (" . implode(',', $implode) . ")";

				$where[] = "pp.price > 0";
			} elseif (count($implode) == 1) {
				$where[] = "p.product_id <> " . $implode[0];
			}

			$join[] = "price";
		}
	}

	public function menu($route, &$data) {
		if ($data['position'] == 'category' || $data['position'] == 'catalog') {
			$this->load->model('catalog/category');
			$this->load->model('core/custom_field');

			$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('catalog_category');

			foreach ($data['menu_items'] as &$menu_item) {
				$menu_item['icon'] = '';

				foreach ($custom_fields as $custom_field) {
					if ($custom_field['code'] == 'icon') {
						$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

						$value = $this->model_catalog_category->getCategoryCustomField($menu_item['category_id'], $custom_field['custom_field_id'], $language_id);

						$menu_item['icon'] = $this->model_core_custom_field->getAsValue($value, $custom_field);
					}
				}
			}
			unset($menu_item);
		}
	}

	public function category($route, &$data) {
		$this->load->model('catalog/category');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		$data['parent_id'] = (int)$data['parent_id'];

		$categories = $this->model_catalog_category->getCategories(array(
			'filter' => array('parent_id' => $this->config->get('catalog_category_general'))
		));

		$data['menu_catalog'] = '';

		if (!empty($categories)) {
			$menu_data['menu_items'] = array();

			$submenu = array(
				'position' => 'category_sub',
				'menu_items' => array()
			);

			$menu_item['get']['route'] = 'catalog/category';
			$menu_item['setting']['depth'] = 0;
			$menu_item['setting']['active_filter'] = true;

			foreach ($categories as $category) {
				$menu_data['menu_items'][] = array(
					'title' => $category['title'] ? $category['title'] : $category['name'],
					'active' => $category['category_id'] == $this->request->get['catalog_category_id'] || !empty($result['active']),
					'href' => $this->url->link('catalog/category', 'catalog_category_id=' . $category['category_id']),
					'category_id' => $category['category_id'],
					'submenu' => ''
				);
			}

			if (!empty($menu_data['menu_items'])) {
				$menu_data['title'] = '';
				$menu_data['position'] = $data['parent_id'] ? 'category' : 'catalog';
				$menu_data['setting'] = array();

				$data['menu_catalog'] = !empty($menu_data['menu_items']) ? $this->load->view('block/menu', $menu_data) : '';
			}
		}
	}

	public function product_list($route, &$data) {
		$this->load->model('catalog/product');
		$this->load->model('tool/image');
		$this->load->model('core/custom_field');

		if (empty($data['image_width']))
			$data['image_width'] = $this->config->get('catalog_image_list_width');

		if (empty($data['image_height']))
			$data['image_height'] = $this->config->get('catalog_image_list_height');

		$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('catalog_product');
		$attributes = $this->model_core_custom_field->getCustomFieldsByLocation('catalog_product_attribute');

		foreach ($data['products'] as &$product) {
			$images = $this->model_catalog_product->getProductImages($product['product_id'], array('limit' => 1, 'start' => 1));

			$image = array_shift($images);

			if ($image) {
				$product['thumb_dop'] = $this->model_tool_image->resize($image['image'], $data['image_width'], $data['image_height']);
			} else {
				$product['thumb_dop'] = '';
			}

			if (isset($product['price']))
				$product['price_format'] = $this->currency->format($product['price'], $this->session->data['currency']);

			if (empty($product['attributes']['missing'])) {
				foreach ($custom_fields as $custom_field) {
					if ($custom_field['code'] == 'composition_products') {
						$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

						$value = $this->model_catalog_product->getProductCustomField($product['product_id'], $custom_field['custom_field_id'], $language_id);

						$composition_products = $this->model_core_custom_field->getAsValue($value, $custom_field);

						foreach ($composition_products as $composition_product) {
							foreach ($attributes as $custom_field) {
								if ($custom_field['code'] == 'missing') {
									$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

									$value = $this->model_catalog_product->getProductCustomField($composition_product['product_id'], $custom_field['custom_field_id'], $language_id);

									$product['attributes']['missing'] = $this->model_core_custom_field->getAsValue($value, $custom_field);

									if ($product['attributes']['missing']) {
										break;
									}
								}
							}

							if (!empty($product['attributes']['missing'])) {
								break;
							}
						}
					}
				}
			}
		}
		unset($product);
	}

	public function product($route, &$data) {
		$this->load->model('catalog/product');
		$this->load->model('catalog/category');
		$this->load->model('core/custom_field');
		$this->load->model('tool/image');

		array_pop($data['breadcrumbs']);

		$data['breadcrumbs'][] = array(
			'text' => '',
			'href' => '#'
		);

		if (!$data['meta_title']) {
			$this->document->setTitle('Купить ' . $data['name']);
		}

		$this->document->addMeta('og:title', $data['title'] ?  $data['title'] :  $data['name'], 1);
		$this->document->addMeta('og:type', 'website', 1);
		$this->document->addMeta('og:url', $this->url->link('catalog/product', 'catalog_product_id=' . $this->request->get['catalog_product_id']), 1);
		if (!empty($data['images'])) {
			$this->document->addMeta('og:image', HTTP_APPLICATION . $data['images'][0]['popup'], 1);

			$data['image_link'] = HTTP_APPLICATION . $data['images'][0]['thumb'];
		} else {
			$data['image_link'] = '';
		}

		if (isset($data['price']))
			$data['price_format'] = $this->currency->format($data['price'], $this->session->data['currency']);

		if (isset($data['special']))
			$data['special_format'] = $this->currency->format($data['special'], $this->session->data['currency']);

		if (!empty($this->request->get['modal'])) {
			return $this->load->view('catalog/product_modal', $data);
		}

		$data['currency'] = $this->session->data['currency'];

		if (empty($data['image_width']))
			$data['image_width'] = $this->config->get('catalog_image_thumb_width');

		if (empty($data['image_height']))
			$data['image_height'] = $this->config->get('catalog_image_thumb_height');

		$attributes = $this->model_core_custom_field->getCustomFieldsByLocation('catalog_product_attribute');

		foreach ($data['custom_fields'] as $key => &$values) {
			if ($key == 'composition_products') {
				foreach ($values as &$composition_product) {
					$image = $this->model_catalog_product->getProductImageMain($composition_product['product_id']);

					if ($image) {
						$composition_product['image'] = $image['image'];
						$composition_product['thumb'] = $this->model_tool_image->resize($image['image'], $data['image_width'], $data['image_height']);
					} else {
						$composition_product['image'] = '';
						$composition_product['thumb'] = $this->model_tool_image->resize('placeholder.png', $data['image_width'], $data['image_height']);
					}

					if (empty($composition_product['title']))
						$composition_product['title'] = $composition_product['name'];

					$composition_product['href'] = $this->url->link('catalog/product', 'catalog_product_id=' . $composition_product['product_id']);

					if (empty($data['attributes']['missing'])) {
						foreach ($attributes as $custom_field) {
							if ($custom_field['code'] == 'missing') {
								$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

								$value = $this->model_catalog_product->getProductCustomField($composition_product['product_id'], $custom_field['custom_field_id'], $language_id);

								$data['attributes']['missing'] = $this->model_core_custom_field->getAsValue($value, $custom_field);

								if ($data['attributes']['missing']) {
									break;
								}
							}
						}
					}
				}
				unset($composition_product);
			}
		}
		unset($values);

		if ($this->request->get['catalog_product_id'] == $this->config->get('theme_client_ws_ttg_construstor_product_id')) {
			if (empty($image_width))
				$image_width = $this->config->get('catalog_image_list_width');

			if (empty($image_height))
				$image_height = $this->config->get('catalog_image_list_height');


			$data['construstor_min'] = max(1, $this->config->get('theme_client_ws_ttg_construstor_min'));
			$data['construstor_max'] = (int)$this->config->get('theme_client_ws_ttg_construstor_max');

			$exlude = (array)$this->config->get('theme_client_ws_ttg_construstor_exlude');
			$exlude[] = (int)$this->config->get('theme_client_ws_ttg_construstor_product_id');

			$data['all_products'] = array();

			$all_products = $this->model_catalog_product->getProducts(array(
				'filter' => array('composition_exlude' => $exlude),
				'order_by' => 'sort_order'
			));

			foreach ($all_products as $product) {
				if (in_array((int)$product['product_id'], $exlude))
					continue;

				$missing = false;

				foreach ($attributes as $custom_field) {
					if ($custom_field['code'] == 'missing' && empty($product['missing'])) {
						$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

						$value = $this->model_catalog_product->getProductCustomField($product['product_id'], $custom_field['custom_field_id'], $language_id);

						$missing = $this->model_core_custom_field->getAsValue($value, $custom_field);
					}
				}

				if (!$missing && !empty($product['price'])) {
					$image = $this->model_catalog_product->getProductImageMain($product['product_id']);

					if ($image) {
						$product['image'] = $image['image'];
						$product['thumb'] = $this->model_tool_image->resize($image['image'], $image_width, $image_height);
					} else {
						$product['image'] = '';
						$product['thumb'] = $this->model_tool_image->resize('placeholder.png', $image_width, $image_height);
					}

					if (empty($product['title']))
						$product['title'] = $product['name'];

					$product['href'] = $this->url->link('catalog/product', 'catalog_product_id=' . $product['product_id']);

					$product['price'] = $this->currency->convert($product['price'], $product['currency_id'], $this->session->data['currency']);
					$product['price_format'] = $this->currency->format($product['price'], $this->session->data['currency']);

					$data['all_products'][] = $product;
				}
			}

			$categories = $this->model_catalog_category->getCategories(array(
				'filter' => array('parent_id' => $this->config->get('catalog_category_general'))
			));

			$data['menu_catalog'] = '';

			if (!empty($categories)) {
				$menu_data['menu_items'] = array();

				$submenu = array(
					'position' => 'category_sub',
					'menu_items' => array()
				);

				$menu_item['get']['route'] = 'catalog/category';
				$menu_item['setting']['depth'] = 0;
				$menu_item['setting']['active_filter'] = true;

				foreach ($categories as $category) {
					$menu_data['menu_items'][] = array(
						'title' => $category['title'] ? $category['title'] : $category['name'],
						'active' => false,
						'href' => $this->url->link('catalog/category', 'catalog_category_id=' . $category['category_id']),
						'category_id' => $category['category_id'],
						'submenu' => ''
					);
				}

				if (!empty($menu_data['menu_items'])) {
					$menu_data['title'] = '';
					$menu_data['position'] = 'category';
					$menu_data['setting'] = array();

					$data['menu_catalog'] = !empty($menu_data['menu_items']) ? $this->load->view('block/menu', $menu_data) : '';
				}
			}

			return $this->load->view('catalog/product_composition', $data);
		}
	}
}