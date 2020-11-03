<?php
namespace Controller\Extension\Module;

class Form extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/form');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/module');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_core_module->addModule('form', $this->request->post);
			} else {
				$this->model_core_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('design/extension/module'));
		}

		$data['error'] = $this->error;

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

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/form')
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/form', 'module_id=' . $this->request->get['module_id'])
			);
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/form');
		} else {
			$data['action'] = $this->url->link('extension/module/form', 'module_id=' . $this->request->get['module_id']);
		}

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('design/extension/module');

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_core_module->getModule($this->request->get['module_id']);
		}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info)) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}

		if (isset($this->request->post['html'])) {
			$data['html'] = $this->request->post['html'];
		} elseif (!empty($module_info)) {
			$data['html'] = $module_info['html'];
		} else {
			$data['html'] = array();
		}

		if (isset($this->request->post['form_id'])) {
			$data['form_id'] = (int)$this->request->post['form_id'];
		} elseif (!empty($module_info)) {
			$data['form_id'] = (int)$module_info['form_id'];
		} else {
			$data['form_id'] = 0;
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = (int)$this->request->post['status'];
		} elseif (!empty($module_info)) {
			$data['status'] = (int)$module_info['status'];
		} else {
			$data['status'] = 0;
		}

		$this->load->model('design/form');

		$data['forms'] = $this->model_design_form->getForms();

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$data['content'] = $this->load->view('extension/module/form', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/form')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		foreach ($this->request->post['html'] as $value) {
			if (strpos($value, '{{ form }}') === false) {
				$this->error['html'] = $this->language->get('error_html');
			}
		}

		return !$this->error;
	}
}