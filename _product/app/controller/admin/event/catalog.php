<?php
namespace Controller\Event;

class Catalog extends \Controller {
	public function startup() {
		$this->registry->set('currency', new \Model\Registry\Currency($this->registry));

		$this->event->register('view/block/column_left/before', new \Action('event/catalog/menu'), 0);

		$this->event->register('view/block/custom_field/*_setting/before', new \Action('event/catalog/custom_field_setting'), 0);
		$this->event->register('view/setting/custom_field_list/before', new \Action('event/catalog/custom_field_list'), 0);

		$this->event->register('model/core/custom_field/render_value/before', new \Action('event/catalog/render_value'), 0);
		$this->event->register('model/core/custom_field/getLocations/after', new \Action('event/catalog/cf_getLocations'), 0);
		$this->event->register('model/core/custom_field/getTypes/after', new \Action('event/catalog/cf_getTypes'), 0);

		$this->load->controller('event/catalog_permission');
	}

	public function menu($route, &$data) {
		$this->load->language('extension/system/catalog', 'temp');

		$language = $this->language->get('temp');

		$route = isset($this->request->get['route']) ? $this->request->get['route'] : $this->config->get('active_default');

		$catalog = array();
		$catalog_active = false;

		if ($this->user->hasPermission('access', 'catalog/category')) {
			if ($active = strpos($route, 'catalog/category') === 0) {
				$catalog_active = true;
			}

			$catalog[] = array(
				'name'	   => $language->get('menu_category'),
				'href'     => $this->url->link('catalog/category', 'user_token=' . $this->session->data['user_token']),
				'children' => array(),
				'active'   => $active
			);
		}

		if ($this->user->hasPermission('access', 'catalog/product')) {
			if ($active = strpos($route, 'catalog/product') === 0) {
				$catalog_active = true;
			}

			$catalog[] = array(
				'name'	   => $language->get('menu_product'),
				'href'     => $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token']),
				'children' => array(),
				'active'   => $active
			);
		}

		if ($this->user->hasPermission('access', 'catalog/price')) {
			if ($active = strpos($route, 'catalog/price') === 0) {
				$catalog_active = true;
			}

			$catalog[] = array(
				'name'	   => $language->get('menu_price'),
				'href'     => $this->url->link('catalog/price', 'user_token=' . $this->session->data['user_token']),
				'children' => array(),
				'active'   => $active
			);
		}
		
		if ($this->config->get('catalog_manufacturer_category_id') && isset($data['menus']['information']['children'][(string)$this->config->get('catalog_manufacturer_category_id')])) {
			$catalog[] = $data['menus']['information']['children'][(string)$this->config->get('catalog_manufacturer_category_id')];
			unset($data['menus']['information']['children'][(string)$this->config->get('catalog_manufacturer_category_id')]);
		}

		if ($catalog) {
			$menu = array_shift($data['menus']);

			$data['menus'] = array_merge(array('catalog' => array(
				'id'       => 'menu-catalog',
				'icon'	   => 'fa-tags',
				'name'	   => $language->get('menu_catalog'),
				'href'     => '',
				'children' => $catalog,
				'active'   => $catalog_active
			)), $data['menus']);

			array_unshift($data['menus'], $menu);
		}

		if ($this->user->hasPermission('access', 'localisation/currency')) {
			$localisation_active = strpos($route, 'localisation/currency') === 0;

			foreach ($data['menus'] as &$value) {
				if (isset($value['id']) && $value['id'] == 'menu-system') {
					if ($localisation_active)
						$value['active'] = true;

					foreach ($value['children'] as &$value2) {
						if (isset($value2['id']) && $value2['id'] == 'menu-localisation') {
							if ($localisation_active)
								$value2['active'] = true;

							$value2['children'][] = array(
								'name'	   => $language->get('text_currency'),
								'href'     => $this->url->link('localisation/currency', 'user_token=' . $this->session->data['user_token']),
								'children' => array(),
								'active'   => $localisation_active
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

	public function custom_field_setting($route, &$data) {
		if (isset($data['type'])) {
			if ($data['type'] == 'catalog_attribute') {
				$this->load->model('localisation/listing');

				$data['listings'] = $this->model_localisation_listing->getLists();

				if (!isset($data['values']['listing_id']))
					$data['values']['listing_id'] = '';
			}
		}
	}

	public function render_value($route, &$data) {
		if ($data['type'] == 'catalog_product') {
			$this->load->model('catalog/product');

			foreach ($data['value'] as &$value) {
				if (!empty($value)) {
					$results = json_decode($value, true);

					foreach ($results as &$result) {
						$result = $this->model_catalog_product->getProduct($result);
					}
					unset($result);

					$value = $results;
				} else {
					$value = array();
				}
			}
			unset($value);
		} elseif ($data['type'] == 'catalog_attribute') {
			foreach ($data['value'] as &$value) {
				if (!empty($value)) {
					$value = json_decode($value, true);
				}
			}
			unset($value);

			$this->load->model('localisation/listing');

			$listing_id = isset($data['custom_field']['setting']['listing_id']) ? $data['custom_field']['setting']['listing_id'] : '';

			$data['listing_items'] = $listing_id ? $this->model_localisation_listings->getListItems(array('filter_listing_id' => $listing_id)) : array();
		}
	}

	public function custom_field_list($route, &$data) {
		$this->load->language('extension/system/catalog', 'temp');

		$language = $this->language->get('temp');

		$data['text_catalog_product'] = $language->get('text_catalog') . ' > ' . $language->get('text_product_content');
		$data['text_catalog_category'] = $language->get('text_catalog') . ' > ' . $language->get('text_category');
		$data['text_catalog_product_attribute'] = $language->get('text_catalog') . ' > ' . $language->get('text_product_attribute');
	}

	public function cf_getLocations($route, $data, &$output) {
		$this->load->language('extension/system/catalog', 'temp');

		$language = $this->language->get('temp');

		$output['catalog'] = array(
			'label' => $language->get('text_catalog'),
			'options' => array(
				array(
					'value' => 'catalog_product',
					'name' => $language->get('text_product_content'),
				),
				array(
					'value' => 'catalog_product_attribute',
					'name' => $language->get('text_product_attribute'),
				),
				array(
					'value' => 'catalog_category',
					'name' => $language->get('text_catalog') . ' > ' . $language->get('text_category'),
				)
			)
		);
	}

	public function cf_getTypes($route, $data, &$output) {
		$this->load->language('extension/system/catalog', 'temp');

		$language = $this->language->get('temp');

		$output['link']['options'][] = array(
			'value' => 'catalog_product',
			'name' => $language->get('text_catalog') . ' > ' . $language->get('text_product'),
		);

		$output['link']['options'][] = array(
			'value' => 'catalog_category',
			'name' => $language->get('text_catalog') . ' > ' . $language->get('text_category'),
		);

		$output['block']['options'][] = array(
			'value' => 'catalog_attribute',
			'name' => $language->get('text_attribute'),
		);
	}
}
