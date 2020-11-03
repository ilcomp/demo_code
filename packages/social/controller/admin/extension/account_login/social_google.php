<?php
namespace Controller\Extension\AccountLogin;

class SocialGoogle extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/account_login/social_google');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_core_setting->editSetting('account_login_social_google', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'type=account_login'));
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
			'href' => $this->url->link('marketplace/extension', 'type=account_login')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/account_login/social_google')
		);

		$data['action'] = $this->url->link('extension/account_login/social_google');

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('marketplace/extension', 'type=account_login');

		if (isset($this->request->post['account_login_social_google_status'])) {
			$data['account_login_social_google_status'] = $this->request->post['account_login_social_google_status'];
		} else {
			$data['account_login_social_google_status'] = $this->config->get('account_login_social_google_status');
		}

		if (isset($this->request->post['account_login_social_google_api_id'])) {
			$data['account_login_social_google_api_id'] = $this->request->post['account_login_social_google_api_id'];
		} else {
			$data['account_login_social_google_api_id'] = $this->config->get('account_login_social_google_api_id');
		}

		if (isset($this->request->post['account_login_social_google_user_id'])) {
			$data['account_login_social_google_user_id'] = $this->request->post['account_login_social_google_user_id'];
		} else {
			$data['account_login_social_google_user_id'] = $this->config->get('account_login_social_google_user_id');
		}

		if (isset($this->request->post['account_login_social_google_secret_key'])) {
			$data['account_login_social_google_secret_key'] = $this->request->post['account_login_social_google_secret_key'];
		} else {
			$data['account_login_social_google_secret_key'] = $this->config->get('account_login_social_google_secret_key');
		}

		$data['content'] = $this->load->view('extension/account_login/social_google', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/account_login/social_google')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}