<?php
namespace Controller\Extension\Menu;

class CatalogCategory extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/menu/catalog_category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_core_setting->editSetting('menu_catalog_category', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('design/extension/menu'));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('design/extension/menu')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/menu/catalog_category')
		);

		$data['action'] = $this->url->link('extension/menu/catalog_category');

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('design/extension/menu');

		if (isset($this->request->post['menu_catalog_category_status'])) {
			$data['menu_catalog_category_status'] = $this->request->post['menu_catalog_category_status'];
		} else {
			$data['menu_catalog_category_status'] = $this->config->get('menu_catalog_category_status');
		}

		$data['content'] = $this->load->view('extension/menu/catalog_category', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	public function get_setting($data = array()) {
		$this->load->language('extension/menu/catalog_category');

		if (!isset($data['setting']))
			$data['setting'] = array();

		if (empty($data['setting']['language_id']))
			$data['setting']['language_id'] = $data['language_id'];

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$data['user_token'] = $this->session->data['user_token'];

		return $this->load->view('extension/menu/catalog_category_setting', $data);
	}

	public function install() {
	}

	public function uninstall() {
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/menu/catalog_category')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}