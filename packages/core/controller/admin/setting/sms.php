<?php
namespace Controller\Setting;

class SMS extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('setting/setting');
		$this->load->language('setting/sms');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->uninstall();

			$this->model_core_setting->editSetting('sms', $this->request->post);

			if ($this->request->post['sms_engine'])
				$this->install();

			$this->session->data['success'] = $this->language->get('text_success');
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['sms'])) {
			$data['error_sms'] = $this->error['sms'];
		} else {
			$data['error_sms'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('setting/sms', 'user_token=' . $this->session->data['user_token'])
		);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['action'] = $this->url->link('setting/sms', 'user_token=' . $this->session->data['user_token']);

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('setting/sms', 'user_token=' . $this->session->data['user_token']);

		$data['user_token'] = $this->session->data['user_token'];

		$data['store_url'] = HTTP_APPLICATION_CLIENT;

		if (isset($this->request->post['sms_engine'])) {
			$data['sms_engine'] = $this->request->post['sms_engine'];
		} else {
			$data['sms_engine'] = $this->config->get('sms_engine');
		}

		if (isset($this->request->post['sms_login'])) {
			$data['sms_login'] = $this->request->post['sms_login'];
		} else {
			$data['sms_login'] = $this->config->get('sms_login');
		}

		if (isset($this->request->post['sms_api_key'])) {
			$data['sms_api_key'] = $this->request->post['sms_api_key'];
		} else {
			$data['sms_api_key'] = $this->config->get('sms_api_key');
		}

		if (isset($this->request->post['sms_sign'])) {
			$data['sms_sign'] = $this->request->post['sms_sign'];
		} else {
			$data['sms_sign'] = $this->config->get('sms_sign');
		}

		if (isset($this->request->post['sms_code_lifetime'])) {
			$data['sms_code_lifetime'] = $this->request->post['sms_code_lifetime'];
		} else {
			$data['sms_code_lifetime'] = $this->config->get('sms_code_lifetime');
		}

		$data['engines'] = array(
			'SmsaeroApiV2' => 'SmsaeroApiV2',
			'SmsRu' => 'SmsRu'
		);

		$data['content'] = $this->load->view('setting/sms', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	public function install() {
		$table = new Model\DBTable($this->registry);
		$table->create('sms');

		$this->load->model('core/event');
		// $this->model_core_event->addEvent('sms', 'client/view/account/*login/before', 'event/sms/view');
		// $this->model_core_event->addEvent('sms', 'client/view/account/*register/before', 'event/sms/view');
		// $this->model_core_event->addEvent('sms', 'client/view/account/*forgotten/before', 'event/sms/view');
		// $this->model_core_event->addEvent('sms', 'client/account/*', 'event/sms/validate');
		// $this->model_core_event->addEvent('sms', 'client/controller/startup/event/after', 'event/sms/startup');
	}

	public function uninstall() {
		$table = new Model\DBTable($this->registry);
		$table->delete('sms');

		$this->load->model('core/event');
		$this->model_core_event->deleteEventByCode('sms');
	}

	public function update() {
		$table = new Model\DBTable($this->registry);
		$table->update('sms');
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'setting/sms')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!isset($this->request->post['sms_code_lifetime']) || !$this->request->post['sms_code_lifetime']) {
			$this->request->post['sms_code_lifetime'] = 5*60;
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}
}
