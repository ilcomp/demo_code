<?php
namespace Controller\Setting;

class Mail extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('setting/setting');
		$this->load->language('setting/mail');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_core_setting->editSetting('mail', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['email'])) {
			$data['error_email'] = $this->error['email'];
		} else {
			$data['error_email'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('setting/mail', 'user_token=' . $this->session->data['user_token'])
		);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['action'] = $this->url->link('setting/mail', 'user_token=' . $this->session->data['user_token']);

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('setting/mail', 'user_token=' . $this->session->data['user_token']);

		$data['user_token'] = $this->session->data['user_token'];

		$data['store_url'] = HTTP_APPLICATION_CLIENT;

		if (isset($this->request->post['mail_engine'])) {
			$data['mail_engine'] = $this->request->post['mail_engine'];
		} else {
			$data['mail_engine'] = $this->config->get('mail_engine');
		}

		if (isset($this->request->post['mail_parameter'])) {
			$data['mail_parameter'] = $this->request->post['mail_parameter'];
		} else {
			$data['mail_parameter'] = $this->config->get('mail_parameter');
		}

		if (isset($this->request->post['mail_smtp_hostname'])) {
			$data['mail_smtp_hostname'] = $this->request->post['mail_smtp_hostname'];
		} else {
			$data['mail_smtp_hostname'] = $this->config->get('mail_smtp_hostname');
		}

		if (isset($this->request->post['mail_smtp_username'])) {
			$data['mail_smtp_username'] = $this->request->post['mail_smtp_username'];
		} else {
			$data['mail_smtp_username'] = $this->config->get('mail_smtp_username');
		}

		if (isset($this->request->post['mail_smtp_password'])) {
			$data['mail_smtp_password'] = $this->request->post['mail_smtp_password'];
		} else {
			$data['mail_smtp_password'] = $this->config->get('mail_smtp_password');
		}

		if (isset($this->request->post['mail_smtp_port'])) {
			$data['mail_smtp_port'] = $this->request->post['mail_smtp_port'];
		} elseif ($this->config->has('mail_smtp_port')) {
			$data['mail_smtp_port'] = $this->config->get('mail_smtp_port');
		} else {
			$data['mail_smtp_port'] = 25;
		}

		if (isset($this->request->post['mail_smtp_timeout'])) {
			$data['mail_smtp_timeout'] = $this->request->post['mail_smtp_timeout'];
		} elseif ($this->config->has('mail_smtp_timeout')) {
			$data['mail_smtp_timeout'] = $this->config->get('mail_smtp_timeout');
		} else {
			$data['mail_smtp_timeout'] = 5;
		}

		if (isset($this->request->post['mail_email'])) {
			$data['mail_email'] = $this->request->post['mail_email'];
		} else {
			$data['mail_email'] = $this->config->get('mail_email');
		}

		if (isset($this->request->post['mail_alert'])) {
			$data['mail_alert'] = $this->request->post['mail_alert'];
		} elseif ($this->config->has('mail_alert')) {
		   	$data['mail_alert'] = $this->config->get('mail_alert');
		} else {
			$data['mail_alert'] = array();
		}

		$data['mail_alerts'] = array();

		if (isset($this->request->post['mail_alert_email'])) {
			$data['mail_alert_email'] = $this->request->post['mail_alert_email'];
		} else {
			$data['mail_alert_email'] = $this->config->get('mail_alert_email');
		}

		$data['content'] = $this->load->view('setting/mail', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'setting/mail')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['mail_email']) > 96) || !filter_var($this->request->post['mail_email'], FILTER_VALIDATE_EMAIL)) {
			$this->error['email'] = $this->language->get('error_email');
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}
}
