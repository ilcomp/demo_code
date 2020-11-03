<?php
namespace Controller\Design;

class Menu extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('design/menu');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('design/menu');

		$this->getList();
	}

	public function add() {
		$this->load->language('design/menu');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('design/menu');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_design_menu->addMenu($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('design/menu'));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('design/menu');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('design/menu');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_design_menu->editMenu($this->request->get['menu_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('design/menu'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('design/menu');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('design/menu');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $menu_id) {
				$this->model_design_menu->deleteMenu($menu_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('design/menu'));
		}

		$this->getList();
	}

	protected function getList() {
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('design/menu')
		);

		$data['actions']['add'] = $this->url->link('design/menu/add');
		$data['actions']['delete'] = $this->url->link('design/menu/delete');

		$data['menus'] = array();

		$data['menus'] = $this->model_design_menu->getMenus();

		foreach ($data['menus'] as &$result) {
			$result['status'] = $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled');
			$result['edit'] = $this->url->link('design/menu/edit', 'user_token=' . $this->session->data['user_token'] . '&menu_id=' . $result['menu_id']);
		}
		unset($result);

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$data['content'] = $this->load->view('design/menu_list', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function getForm() {
		$this->load->model('localisation/language');

		$data['text_form'] = !isset($this->request->get['menu_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('design/menu')
		);

		if (!isset($this->request->get['menu_id'])) {
			$data['action'] = $this->url->link('design/menu/add');
		} else {
			$data['action'] = $this->url->link('design/menu/edit', 'user_token=' . $this->session->data['user_token'] . '&menu_id=' . $this->request->get['menu_id']);
		}

		$data['get_setting'] = $this->url->link('design/menu/get_setting');

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('design/menu');

		if (isset($this->request->get['menu_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$menu_info = $this->model_design_menu->getMenu($this->request->get['menu_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('core/extension');

		$this->load->model('design/menu');

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

	protected function validateForm() {
		foreach ($this->request->post['name'] as $language_id => $name) {
			if ((utf8_strlen($name) < 3) || (utf8_strlen($name) > 255)) {
				$this->error['name'][$language_id] = $this->language->get('error_name');
			}
		}

		if ((utf8_strlen($this->request->post['position']) < 3) || (utf8_strlen($this->request->post['position']) > 15)) {
			$this->error['position'] = $this->language->get('error_position');
		}

		return !$this->error;
	}

	protected function validateDelete() {

		return !$this->error;
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

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('design/menu');

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name']
			);

			$results = $this->model_design_menu->getMenus($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'menu_id'	=> $result['menu_id'],
					'name'		=> strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
				);
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}

	public function install() {
		$table = new \Model\DBTable($this->registry);

		$table->create('menu');
	}

	public function uninstall() {
		$table = new \Model\DBTable($this->registry);

		$table->delete('menu');
	}
}