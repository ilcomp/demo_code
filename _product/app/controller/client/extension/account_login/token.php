<?php
namespace Controller\Extension\AccountLogin;

class Login extends \Controller {
	public function index() {
		if (!$this->config->get('account_login_token_status'))
			return new \Action('error/not_found');

		$this->load->language('account/login');

		if (isset($this->request->get['login'])) {
			$login = $this->request->get['login'];
		} else {
			$login = '';
		}

		if (isset($this->request->get['login_token'])) {
			$login_token = $this->request->get['login_token'];
		} else {
			$login_token = '';
		}

		$this->account->logout();

		$this->load->model('account/account');

		$filter_data = array(
			'login' => $login,
			'type' => 'login',
		);

		if ($this->account->login('token', $login_token, $login)) {
			$this->model_account_account->removeAccountAuth($filter_data);

			$this->response->redirect($this->url->link('account/account', 'language_id=' . $account_info['language_id']));
		} else {
			$this->model_account_account->removeAccountAuth($filter_data);

			$this->session->data['error'] = $this->language->get('error_login');

			$this->response->redirect($this->url->link('account/login', 'language=' . $this->config->get('config_language')));
		}
	}
}
