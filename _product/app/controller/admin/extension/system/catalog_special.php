<?php
namespace Controller\Extension\System;

class CatalogSpecial extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('setting/setting');
		$this->load->language('extension/system/catalog_special');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_core_setting->editSetting('catalog_special', $this->request->post, $this->request->get['store_id']);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/system'));
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
			'href' => $this->url->link('marketplace/system')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/system/catalog_special', 'store_id=' . $this->request->get['store_id'])
		);

		$data['action'] = $this->url->link('extension/system/catalog_special', 'store_id=' . $this->request->get['store_id']);

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('marketplace/system');

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->get['store_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$setting_info = $this->model_core_setting->getSetting('catalog_special', $this->request->get['store_id']);
		}

		if (isset($this->request->post['catalog_special_status'])) {
			$data['catalog_special_status'] = $this->request->post['catalog_special_status'];
		} elseif ($setting_info) {
			$data['catalog_special_status'] = $setting_info['catalog_special_status'];
		} else {
			$data['catalog_special_status'] = 0;
		}

		if (isset($this->request->post['catalog_special_operator'])) {
			$data['catalog_special_operator'] = $this->request->post['catalog_special_operator'];
		} elseif ($setting_info) {
			$data['catalog_special_operator'] = $setting_info['catalog_special_operator'];
		} else {
			$data['catalog_special_operator'] = '=';
		}

		if (isset($this->request->post['catalog_special_round'])) {
			$data['catalog_special_round'] = $this->request->post['catalog_special_round'];
		} elseif ($setting_info) {
			$data['catalog_special_round'] = $setting_info['catalog_special_round'];
		} else {
			$data['catalog_special_round'] = 'round';
		}

		if (isset($this->request->post['catalog_special_accuracy'])) {
			$data['catalog_special_accuracy'] = $this->request->post['catalog_special_accuracy'];
		} elseif ($setting_info) {
			$data['catalog_special_accuracy'] = $setting_info['catalog_special_accuracy'];
		} else {
			$data['catalog_special_accuracy'] = 1;
		}

		if (isset($this->request->post['catalog_special_subtraction'])) {
			$data['catalog_special_subtraction'] = $this->request->post['catalog_special_subtraction'];
		} elseif ($setting_info) {
			$data['catalog_special_subtraction'] = $setting_info['catalog_special_subtraction'];
		} else {
			$data['catalog_special_subtraction'] = 0;
		}

		$data['content'] = $this->load->view('extension/system/catalog_special', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	public function install() {
		$table = new \Model\DBTable($this->registry);
		$table->create('catalog_special');

		$this->load->model('core/event');
		$this->model_core_event->addEvent('catalog_special', 'api/controller/startup/event/after', 'event/catalog_special/startup');
		$this->model_core_event->addEvent('catalog_special', 'admin/controller/startup/event/after', 'event/catalog_special/startup');
		$this->model_core_event->addEvent('catalog_special', 'client/controller/startup/event/after', 'event/catalog_special/startup');
	}

	public function uninstall() {
		$table = new \Model\DBTable($this->registry);
		$table->delete('catalog_special');

		$this->load->model('core/event');
		$this->model_core_event->deleteEventByCode('catalog_special');
	}

	public function update() {
		$table = new \Model\DBTable($this->registry);
		$table->update('catalog_special');
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/system/catalog_special')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		if (!(float)$this->request->post['catalog_special_accuracy'])
			$this->request->post['catalog_special_accuracy'] = 1;

		return !$this->error;
	}
}