<?php
class ControllerAccountLoginCode extends Controller {
	public function index() {
		if ($this->account->isLogged()) {
			$this->response->redirect($this->url->link('account/account'));
		}

		$this->load->language('account/login');

		if (isset($this->request->get['email'])) {
			$email = $this->request->get['email'];
		} else {
			$email = '';
		}

		if (isset($this->request->get['code'])) {
			$code = $this->request->get['code'];
		} else {
			$code = '';
		}

		$this->load->model('account/account');

		$account_info = $this->model_account_account->getAccountByEmail($email);

		if (!$account_info || !$account_info['status'] || !$account_info['code'] || $account_info['code'] !== $code) {
			$this->model_account_account->editCode($email, '');

			$this->session->data['error'] = $this->language->get('error_code');

			$this->response->redirect($this->url->link('account/login'));
		}

		unset($this->session->data['guest']);

		$token = token(64);

		$this->model_account_account->editToken($account_info['login'], $token);

		$this->response->redirect($this->url->link('account/login/token', 'login=' . urlencode($account_info['login']) . '&login_token=' . $token));
	}
}
