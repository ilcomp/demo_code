<?php
namespace Controller\Event;

class Order extends \Controller {
	public function startup() {
		$this->event->register('view/block/column_left/before', new \Action('event/order/menu'), 0);
		$this->event->register('view/setting/mail/before', new \Action('event/order/setting_mail'), 0);

		$this->event->register('view/setting/custom_field_list/before', new \Action('event/order/custom_field_list'), 0);
		$this->event->register('model/core/custom_field/getLocations/after', new \Action('event/order/cf_getLocations'), 0);
		$this->event->register('model/core/custom_field/getTypes/after', new \Action('event/order/cf_getTypes'), 0);
	}

	public function menu($route, &$data) {
		$this->load->language('extension/system/order', 'temp');

		$language = $this->language->get('temp');

		$route = isset($this->request->get['route']) ? $this->request->get['route'] : $this->config->get('active_default');

		$order = array();
		$order_active = false;

		if ($this->user->hasPermission('access', 'order/order')) {
			if ($active = strpos($route, 'order/order') === 0) {
				$order_active = true;
			}

			$order[] = array(
				'name'	   => $language->get('menu_order'),
				'href'     => $this->url->link('order/order'),
				'children' => array(),
				'active'   => $active
			);
		}

		if ($this->user->hasPermission('access', 'marketplace/extension')) {
			$extension_active = false;

			$children = array();

			$files = glob(DIR_CONTROLLER . 'order/extension/*.php', GLOB_BRACE);

			foreach ($files as $file) {
				$extension = basename($file, '.php');

				if ($active = (strpos($route, 'order/extension/' . $extension) === 0 || strpos($route, 'extension/' . $extension) === 0)) {
					$extension_active = true;
					$order_active = true;
				}

				// Compatibility code for old extension folders
				$this->load->language('order/extension/' . $extension, 'extension');

				if ($this->user->hasPermission('access', 'order/extension/' . $extension)) {
					$files = glob(DIR_CONTROLLER . 'extension/' . $extension . '/*.php', GLOB_BRACE);

					$children[] = array(
						'name'     => $this->language->get('extension')->get('heading_title') . ' (' . count($files) .')',
						'href'     => $this->url->link('order/extension/' . $extension),
						'children' => array(),
						'active'   => $active
					);
				}
			}

			if (!empty($children))
				$order[] = array(
					'name'	   => $this->language->get('text_extension'),
					'href'     => '',
					'children' => $children,
					'active'   => $extension_active
				);
		}

		if ($order) {
			$menu = array_shift($data['menus']);

			$data['menus'] = array_merge(array('order' => array(
				'id'       => 'menu-order',
				'icon'	   => 'fa-tags',
				'name'	   => $language->get('menu_order'),
				'href'     => '',
				'children' => $order,
				'active'   => $order_active
			)), $data['menus']);

			array_unshift($data['menus'], $menu);
		}
	}

	public function setting_mail($route, &$data) {
		$this->load->language('extension/system/order', 'temp');

		$language = $this->language->get('temp');

		$data['mail_alerts'][] = array(
			'text'  => $language->get('text_mail_order'),
			'value' => 'order'
		);
	}

	public function custom_field_list($route, &$data) {
		$this->load->language('extension/system/order', 'temp');

		$language = $this->language->get('temp');

		$data['text_order'] = $language->get('text_order');
		$data['text_order_contact'] = $language->get('text_order_contact');
		$data['text_order_address'] = $language->get('text_order_address');
	}

	public function cf_getLocations($route, $data, &$output) {
		$this->load->language('extension/system/order', 'temp');

		$language = $this->language->get('temp');

		$output['order'] = array(
			'label' => $language->get('text_order'),
			'options' => array(
				array(
					'value' => 'order_contact',
					'name' => $language->get('text_order_contact'),
				),
				array(
					'value' => 'order_address',
					'name' => $language->get('text_order_address'),
				)
			)
		);
	}
}