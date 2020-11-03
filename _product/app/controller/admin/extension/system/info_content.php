<?php
namespace Controller\Extension\System;

class InfoContent extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/system/info_content');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_core_setting->editSetting('info_content', $this->request->post, $this->request->get['store_id']);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/system', 'user_token=' . $this->session->data['user_token']));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/system', 'user_token=' . $this->session->data['user_token'])
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/system/info_content', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $this->request->get['store_id'])
		);

		$data['action'] = $this->url->link('extension/system/info_content', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $this->request->get['store_id']);

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('marketplace/system', 'user_token=' . $this->session->data['user_token']);

		if (isset($this->request->get['store_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$setting_info_content = $this->model_core_setting->getSetting('info_content', $this->request->get['store_id']);
		}

		if (isset($this->request->post['info_content_status'])) {
			$data['info_content_status'] = $this->request->post['info_content_status'];
		} elseif ($setting_info_content) {
			$data['info_content_status'] = $setting_info_content['info_content_status'];
		} else {
			$data['info_content_status'] = '';
		}

		$data['content'] = $this->load->view('extension/system/info_content', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	public function install() {
		$table = new \Model\DBTable($this->registry);
		$table->create('info_content');

		$this->load->model('core/event');
		$this->model_core_event->addEvent('info_content', 'api/controller/startup/event/after', 'event/info_content/startup');
		$this->model_core_event->addEvent('info_content', 'admin/controller/startup/event/after', 'event/info_content/startup');
		$this->model_core_event->addEvent('info_content', 'client/controller/startup/event/after', 'event/info_content/startup');

		$this->load->model('info/content');
		$this->model_info_content->install();
	}

	public function uninstall() {
		$table = new \Model\DBTable($this->registry);
		$table->delete('info_content');

		$this->load->model('core/event');
		$this->model_core_event->deleteEventByCode('info_content');

		$this->load->model('info/content');
		$this->model_info_content->uninstall();
	}

	public function update() {
		$table = new \Model\DBTable($this->registry);
		$table->update('info_content');
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/system/info_content')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}