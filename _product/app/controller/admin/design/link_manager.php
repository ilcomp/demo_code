<?php
namespace Controller\Design;

class LinkManager extends \Controller {
	protected function index() {
		$this->load->model('localisation/language');
		$this->load->model('core/extension');

		$data['extensions'] = array(
			array(
				'name' => $this->language->get('text_custom_link'),
				'code' => ''
			),
			array(
				'name' => $this->language->get('text_separator'),
				'code' => 'separator'
			)
		);

		// Get a list of installed menus
		$extensions = $this->model_core_extension->getInstalled('menu');

		// Add all the menus which have multiple settings for each menu
		foreach ($extensions as $code) {
			$this->load->language('extension/menu/' . $code, 'extension');

			if ($this->config->has('menu_' . $code . '_status')) {
				$data['extensions'][] = array(
					'name'   => strip_tags($this->language->get('extension')->get('heading_title')),
					'code'   => $code
				);
			}
		}

		if (isset($this->request->post['menu_item'])) {
			$menu_items = $this->request->post['menu_item'];
		} elseif (isset($this->request->get['menu_id'])) {
			$menu_items = $this->model_design_menu->getMenuItems($this->request->get['menu_id']);
		} else {
			$menu_items = array();
		}

		$data['menu_items'] = array();

		foreach ($menu_items as $language_id => $values) {
			$sort_order = array();

			foreach ($values as $key => $menu_item) {
				$data_menu_item = $menu_item;
				$data_menu_item['menu_row']= $key;
				$data_menu_item['language_id'] = $menu_item['language_id'];

				$menu_item['setting'] = $this->load->controller('extension/menu/' . $menu_item['code'] . '/get_setting', $data_menu_item);

				$data['menu_items'][$language_id][] = $menu_item;

				$sort_order[] = $menu_item['sort_order'];
			}

			array_multisort($sort_order, $data['menu_items'][$language_id]);
		}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($menu_info)) {
			$data['name'] = $this->model_design_menu->getMenuDescriptions($this->request->get['menu_id']);
		} else {
			$data['name'] = '';
		}

		if (isset($this->request->post['position'])) {
			$data['position'] = $this->request->post['position'];
		} elseif (!empty($menu_info)) {
			$data['position'] = $menu_info['position'];
		} else {
			$data['position'] = '';
		}

		if (isset($this->request->post['setting'])) {
			$data['setting'] = $this->request->post['setting'];
		} elseif (!empty($menu_info)) {
			$data['setting'] = $menu_info['setting'];
		} else {
			$data['setting'] = '';
		}

		if (isset($this->request->post['store_id'])) {
			$data['store_id'] = $this->request->post['store_id'];
		} elseif (!empty($menu_info)) {
			$data['store_id'] = $menu_info['store_id'];
		} else {
			$data['store_id'] = 0;
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($menu_info)) {
			$data['status'] = $menu_info['status'];
		} else {
			$data['status'] = true;
		}

		$this->load->model('core/store');

		$data['stores'] = array();

		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		);

		$stores = $this->model_core_store->getStores();

		foreach ($stores as $store) {
			$data['stores'][] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			);
		}

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$data['content'] = $this->load->view('design/menu_form', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	public function get_setting() {
		$menu_code = isset($this->request->post['menu_code']) ? $this->request->post['menu_code'] : 'custom_link';
		$menu_row = isset($this->request->post['menu_row']) ? $this->request->post['menu_row'] : 0;
		$language_id = isset($this->request->post['language_id']) ? $this->request->post['language_id'] : 0;

		if (isset($this->request->post['menu_item'][$language_id]) && isset($this->request->post['menu_item'][$language_id][$menu_row]) && is_array($this->request->post['menu_item'][$language_id][$menu_row])) {
			$data_menu_item = $this->request->post['menu_item'][$language_id][$menu_row];
			$data_menu_item['menu_row'] = $menu_row;
			$data_menu_item['language_id'] = $language_id;

			$this->response->setOutput($this->load->controller('extension/menu/' . $menu_code . '/get_setting', $data_menu_item));
		}
	}
}