<?php
namespace Controller\Extension\Module;

class Store extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/store');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_core_setting->editSetting('module_store', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('design/extension/module'));
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
			'href' => $this->url->link('design/extension/module')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/store')
		);

		$data['action'] = $this->url->link('extension/module/store');

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('design/extension/module');

		if (isset($this->request->post['module_store_admin'])) {
			$data['module_store_admin'] = $this->request->post['module_store_admin'];
		} else {
			$data['module_store_admin'] = $this->config->get('module_store_admin');
		}

		if (isset($this->request->post['module_store_status'])) {
			$data['module_store_status'] = $this->request->post['module_store_status'];
		} else {
			$data['module_store_status'] = $this->config->get('module_store_status');
		}

		$data['content'] = $this->load->view('extension/module/store', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/store')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}