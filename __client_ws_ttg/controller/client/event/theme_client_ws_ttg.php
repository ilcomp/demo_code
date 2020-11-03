<?php
namespace Controller\Event;

class ThemeClientWSTTG extends \Controller {
	public function index() {
		$this->event->register('controller/startup/event/after', new \Action('event/theme_client_ws_ttg/startup'), 0);

		$this->load->controller('event/ws_ttg_seourl');
	}

	public function startup() {
		$this->load->controller('event/ws_ttg_account');
		$this->load->controller('event/ws_ttg_catalog');
		$this->load->controller('event/ws_ttg_info');
		$this->load->controller('event/ws_ttg_order');

		$this->event->register('model/core/custom_field/getAsValue/before', new \Action('event/theme_client_ws_ttg/getAsValue'), 10);
		$this->event->register('model/catalog/product/getPriceTotal/after', new \Action('event/theme_client_ws_ttg/getPriceTotal'), 100);

		$this->event->register('controller/common/template/before', new \Action('event/theme/theme'), 997);
		$this->event->register('controller/common/template/before', new \Action('event/theme/scss'), 998);
		$this->event->register('controller/common/template/before', new \Action('event/theme_client_ws_ttg/template'), 999);

		$this->event->register('view/account/*/before', new \Action('event/theme_client_ws_ttg/view'), 0);
		$this->event->register('view/catalog/*/before', new \Action('event/theme_client_ws_ttg/view'), 0);
		$this->event->register('view/info/*/before', new \Action('event/theme_client_ws_ttg/view'), 0);
		$this->event->register('view/common/*/before', new \Action('event/theme_client_ws_ttg/view'), 0);
		$this->event->register('view/template/*/before', new \Action('event/theme_client_ws_ttg/view'), 0);

		$this->event->register('view/block/form/before', new \Action('event/theme_client_ws_ttg/form'), 0);
		$this->event->register('view/account/form_register/before', new \Action('event/theme_client_ws_ttg/form'), 0);
		$this->event->register('view/account/form_login/before', new \Action('event/theme_client_ws_ttg/form'), 0);
		$this->event->register('view/order/confirm/before', new \Action('event/theme_client_ws_ttg/form'), 0);
		$this->event->register('view/template/footer/before', new \Action('event/theme_client_ws_ttg/form'), 0);

		$this->load->language('extension/theme/client_ws_ttg');

		if (isset($this->request->post['agree'])) {
			$this->config->set('error_warning', $this->language->get('error_agree'));

			$log = new \Log('atack.log');

			$log->write('ATACK: ' . (string)$this->request->server['REMOTE_ADDR'] . ' - register');
		}
	}

	public function getAsValue($route, &$args) {
		if ($args[1]['type'] == 'table') {
			$args[0] = json_decode($args[0], true);
		} elseif ($args[1]['type'] == 'gallery') {
			$this->load->model('tool/image');

			$thumb_width = isset($args[1]['setting']['width']) ? $args[1]['setting']['width'] : 100;
			$thumb_height = isset($args[1]['setting']['height']) ? $args[1]['setting']['height'] : 100;
			$thumb_method = isset($args[1]['setting']['method']) ? $args[1]['setting']['method'] : '';
			$log=new \Log('debug.log');$log->write($args[0]);
			$values = json_decode($args[0], true);

			$images = array();

			if (!empty($values)) {
				foreach ($values as $image) {
					if ($image && is_file(DIR_IMAGE . $image)) {
						switch ($thumb_method) {
							case 'link': $thumb = $this->model_tool_image->compress($image); break;
							case 'crop': $thumb = $this->model_tool_image->crop($image, $thumb_width, $thumb_height); break;
							default: $thumb = $this->model_tool_image->resize($image, $thumb_width, $thumb_height); break;
						}

						$images[] = array(
							'image' => $image,
							'original' => $this->model_tool_image->compress($image),
							'thumb' => $thumb,
						);
					}
				}
			}
			$log=new \Log('debug.log');$log->write($images);
			$args[0] = $images;
		} elseif ($args[1]['type'] == 'menu') {
			if ($args[0])
				$args[0] = $this->load->controller('block/menu/get_menu', $args[0]);
		} elseif ($args[1]['type'] == 'composition_product') {
			$this->load->model('catalog/product');

			$values = json_decode($args[0], true);

			$results = array();

			if (!empty($values)) {
				foreach ($values as &$value) {
					if (is_numeric($value)) {
						$product_id = $value;
						$quantity = 1;
					} elseif (isset($value['product_id']) && isset($value['quantity'])) {
						$product_id = $value['product_id'];
						$quantity = $value['quantity'];
					} else {
						$product_id = 0;
						$quantity = 0;
					}

					if ($product_id && $quantity) {
						$product = $this->model_catalog_product->getProduct($product_id, array('composition' => true));

						if ($product) {
							$result = $product;

							$result['quantity'] = $quantity;

							$results[] = $result;
						}
					}
				}
			}

			$args[0] = $results;
		}
	}

	public function getPriceTotal($route, $data, &$output) {
		if (isset($output['price']))
			$output['price_format'] = $this->currency->format($output['price'], $this->session->data['currency']);

		if (isset($output['total']))
			$output['total_format'] = $this->currency->format($output['total'], $this->session->data['currency']);

		if (isset($output['special']))
			$output['special_format'] = $this->currency->format($output['special'], $this->session->data['currency']);

		if (isset($output['total_special']))
			$output['total_special_format'] = $this->currency->format($output['total_special'], $this->session->data['currency']);
	}

	public function template($route, &$args) {
		$styles = [
			$this->config->get('config_theme') . '/css/core.css',
			$this->config->get('config_theme') . '/css/style.css'
		];
		foreach ($styles as $style) {
			$this->document->addStyle('theme/' . $style . (file_exists(DIR_THEME . $style) ? ('?v=' . filemtime(DIR_THEME . $style)) : ''));
		}

		$scripts = [
			$this->config->get('config_theme') . '/js/script.js',
			$this->config->get('config_theme') . '/js/theme.js',
			$this->config->get('config_theme') . '/js/common.js',
			$this->config->get('config_theme') . '/js/toast.js'
		];
		foreach ($scripts as $script) {
			if (file_exists(DIR_THEME . $script))
				$this->document->addScript('theme/' . $script . '?v=' . filemtime(DIR_THEME . $script), 100);
		}

		$this->load->model('tool/image');

		$args[0]['logo'] = $this->model_tool_image->compress($this->config->get('config_logo'));

		$args[0]['form'] = $this->load->controller('form/callback');
		$args[0]['link_contact'] = $this->url->link('common/contact');

		$args[0]['name'] = $this->config->get('config_name');
		$args[0]['address'] = $this->config->get('config_address');
		$args[0]['open'] = $this->config->get('config_open');
		$args[0]['telephone'] = $this->config->get('config_telephone');
		$args[0]['email'] = $this->config->get('config_email');
		$args[0]['comment'] = html_entity_decode($this->config->get('config_comment'), ENT_QUOTES, 'UTF-8');

		// $data['menu'] = $this->load->controller('common/menu');
		// $data['language'] = $this->load->controller('common/language');
		// $data['search_form'] = $this->load->controller('common/search/form');

		// $data['panel_user'] = $this->load->controller('common/panel_user', $setting);

		$args[0]['header_link'] = $this->config->get('theme_client_ws_ttg_header_link');

		if (!isset($args[0]['menu']))
			$args[0]['menu'] = array();

		foreach ($this->config->get('theme_client_ws_ttg_menus') as $menu_id) {
			$menu = $this->load->controller('block/menu/get_menu', $menu_id);

			if ($menu)
				$args[0]['menu'][$menu['position']] = $menu;
		}
	}

	public function view($route, &$data) {
		if ($route == 'template/default' && isset($this->request->get['route']) && in_array($this->request->get['route'], array('order/cart', 'order/checkout', 'order/confirm')))
			return;

		if (strpos($route, 'account/') === 0 && strpos($route, 'login') === false && strpos($route, 'register') === false)
			$data['menu'] = $this->load->controller('block/menu');

		$modules = (array)$this->config->get('theme_client_ws_ttg_modules');

		$templates = array_column($modules, 'template');

		if (!isset($data['modules']))
			$data['modules'] = array();

		if (in_array($route, $templates)) {
			$this->load->model('core/module');

			foreach ($modules as $module) {
				if ($route == $module['template']) {
					if (!isset($data['modules'][$module['code']]))
						$data['modules'][$module['code']] = '';

					$part = explode('.', $module['module']);

					if (isset($part[0]) && $this->config->get('module_' . $part[0] . '_status')) {
						$module_data = $this->load->controller('extension/module/' . $part[0]);

						$data['modules'][$module['code']] .= $module_data;
					}

					if (isset($part[1])) {
						$setting_info = $this->model_core_module->getModule($part[1]);

						if ($setting_info && $setting_info['status']) {
							$output = $this->load->controller('extension/module/' . $part[0], $setting_info);

							$data['modules'][$module['code']] .= $output;
						}
					}
				}
			}
		}
	}

	public function form($route, &$data) {
		$data['confidentiality'] = $this->config->get('theme_client_ws_ttg_confidentiality') ? $this->url->link('info/article', 'info_article_id=' . $this->config->get('theme_client_ws_ttg_confidentiality')) : '';
		$data['agreement'] = $this->config->get('theme_client_ws_ttg_agreement') ? $this->url->link('info/article', 'info_article_id=' . $this->config->get('theme_client_ws_ttg_agreement')) : '';
	}
}