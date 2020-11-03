<?php
namespace Controller\Event;

class ThemeClientWSTTG extends \Controller {
	public function startup() {
		$this->event->register('model/order/order/getOrderProducts/after', new \Action('event/theme_client_ws_ttg/getOrderProducts'), 10);
		$this->event->register('model/core/custom_field/getAsField/before', new \Action('event/theme_client_ws_ttg/getAsField'), 10);
		$this->event->register('model/core/custom_field/render_value/before', new \Action('event/theme_client_ws_ttg/render_value'), 0);

		$this->event->register('view/block/custom_field/*_setting/before', new \Action('event/theme_client_ws_ttg/custom_field_setting'), 0);

		$this->event->register('view/setting/custom_field_list/before', new \Action('event/theme_client_ws_ttg/custom_field_list'), 10);
		$this->event->register('model/core/custom_field/getLocations/after', new \Action('event/theme_client_ws_ttg/cf_getLocations'), 10);
		$this->event->register('model/core/custom_field/getTypes/after', new \Action('event/theme_client_ws_ttg/cf_getTypes'), 10);

		$this->event->register('view/catalog/category_form/before', new \Action('event/theme_client_ws_ttg/seo'), 0);
		$this->event->register('view/catalog/product_form/before', new \Action('event/theme_client_ws_ttg/seo'), 0);
		$this->event->register('view/info/category_form/before', new \Action('event/theme_client_ws_ttg/seo'), 0);
		$this->event->register('view/info/article_form/before', new \Action('event/theme_client_ws_ttg/seo'), 0);

		$this->event->register('view/order/order_list/after', new \Action('event/theme_client_ws_ttg/order_list'), 0);
	}

	public function getOrderProducts($route, $args, &$output) {
		$this->load->model('catalog/product');
		$this->load->model('core/custom_field');

		$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('catalog_product_attribute');

		foreach ($output as &$product) {
			foreach ($custom_fields as $custom_field) {
				if ($custom_field['code'] == 'sku') {
					$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

					$value = $this->model_catalog_product->getProductCustomField($product['product_id'], $custom_field['custom_field_id'], $language_id);

					$product['sku'] = $this->model_core_custom_field->getAsValue($value, $custom_field);
				}
			}
		}
		unset($product);
	}

	public function getAsField($route, &$args) {
		if (isset($this->request->get['route']) && $this->request->get['route'] == 'info/article/edit' && isset($this->request->get['info_article_id'])) {
			$catalogs = (array)$this->config->get('theme_client_ws_ttg_catalogs');

			if (in_array($this->request->get['info_article_id'], array_column($catalogs, 'article_id')))
				$args[1] = 'info_catalog';
		}
	}

	public function render_value($route, &$data) {
		if ($data['type'] == 'table') {
			foreach ($data['value'] as &$value) {
				$value = json_decode($value, true);
			}
			unset($value);
		} elseif ($data['type'] == 'gallery') {
			$this->load->model('tool/image');

			$thumb_width = $this->config->get('admin_image_thumb_width') ? $this->config->get('admin_image_thumb_width') : 100;
			$thumb_height = $this->config->get('admin_image_thumb_height') ? $this->config->get('admin_image_thumb_height') : 100;

			$data['placeholder'] = $this->model_tool_image->resize('no_image.png', $thumb_width, $thumb_height);

			foreach ($data['value'] as &$value) {
				if (!empty($value)) {
					$results = json_decode($value, true);

					foreach ($results as &$result) {
						$image['image'] = $result;

						if ($image['image'] && is_file(DIR_IMAGE . $image['image'])) {
							$image['thumb'] = $this->model_tool_image->resize($image['image'], $thumb_width, $thumb_height);
						} else {
							$image['thumb'] = $custom_field['placeholder'];
						}

						$result = $image;
					}
					unset($result);

					$value = $results;
				} else {
					$value = array();
				}
			}
			unset($value);
		} elseif ($data['type'] == 'menu') {
			$this->load->model('design/menu');

			$data['menus'] = $this->model_design_menu->getMenus();
		} elseif ($data['type'] == 'composition_product') {
			$this->load->model('catalog/product');

			$data['user_token'] = $this->session->data['user_token'];

			foreach ($data['value'] as &$value) {
				if (!empty($value)) {
					$results = json_decode($value, true);

					foreach ($results as &$result) {
						if (is_numeric($result)) {
							$result = array(
								'product_id' => (int)$result
							);
						}

						$product = $this->model_catalog_product->getProduct($result['product_id']);

						$result['name'] = $product ? $product['name'] : '';
						
						if (!isset($result['quantity']) || $result['quantity'] < 1)
							$result['quantity'] = 1;
					}
					unset($result);

					$value = $results;
				} else {
					$value = array();
				}
			}
			unset($value);
		}
	}

	public function custom_field_setting($route, &$data) {
		if (isset($data['type'])) {
			if ($data['type'] == 'table') {
				$this->load->language('extension/theme/client_ws_ttg', 'temp');

				$language = $this->language->get('temp');

				$data['entry_cols'] = $language->get('entry_cols');
			}
		}
	}

	public function custom_field_list($route, &$data) {
		$this->load->language('extension/system/info', 'temp');

		$language_info = $this->language->get('temp');

		$this->load->language('extension/theme/client_ws_ttg', 'temp');

		$language = $this->language->get('temp');

		$data['text_info_catalog'] = $language_info->get('menu_info') . ' > ' . $language->get('menu_catalog');
		$data['text_table'] = $language->get('text_table');
		$data['text_gallery'] = $language->get('text_gallery');
		$data['text_menu'] = $language->get('text_menu');
		$data['text_composition_product'] = $language->get('text_composition_product');
	}

	public function cf_getLocations($route, $data, &$output) {
		$this->load->language('extension/theme/client_ws_ttg', 'temp');

		$language = $this->language->get('temp');

		$output['info']['options'][] = array(
			'value' => 'info_catalog',
			'name' => $language->get('menu_catalog')
		);
	}

	public function cf_getTypes($route, $data, &$output) {
		$this->load->language('extension/theme/client_ws_ttg', 'temp');

		$language = $this->language->get('temp');

		$output['input']['options'][] = array(
			'value' => 'table',
			'name' => $language->get('text_table')
		);

		$output['file']['options'][] = array(
			'value' => 'gallery',
			'name' => $language->get('text_gallery')
		);

		$output['block']['options'][] = array(
			'value' => 'menu',
			'name' => $language->get('text_menu')
		);

		$output['block']['options'][] = array(
			'value' => 'composition_product',
			'name' => $language->get('text_composition_product')
		);
	}

	public function seo($route, $data) {
		$scripts = [
			$this->config->get('config_theme') . '/js/seo.js',
		];
		foreach ($scripts as $script) {
			if (file_exists(DIR_THEME . $script))
				$this->document->addScript('theme/' . $script . '?v=' . filemtime(DIR_THEME . $script), 100);
		}
	}

	public function order_list($route, $data, &$output) {
		$this->load->language('extension/system/retailcrm', 'temp');

		$data_retail = $this->language->get('temp')->all();

		$data_retail['user_token'] = $this->session->data['user_token'];

		$output = $this->load->view('order/retailcrm_view', $data_retail) . $output;
	}
}