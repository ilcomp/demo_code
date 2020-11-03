<?php
namespace Controller\Account;

class Account extends \Controller {
	public function index() {
		$this->load->language('api/account');

		// Delete past account in case there is an error
		unset($this->session->data['account']);

		$json = array();

		if (!isset($this->session->data['api_id'])) {
			$json['error']['warning'] = $this->language->get('error_permission');
		} else {
			// Add keys for missing post vars
			$keys = array(
				'account_id'
			);

			foreach ($keys as $key) {
				if (!isset($this->request->post[$key])) {
					$this->request->post[$key] = '';
				}
			}

			// Account
			if ($this->request->post['account_id']) {
				$this->load->model('account/account');

				$account_info = $this->model_account_account->getAccount($this->request->post['account_id']);

				if (!$account_info || !$this->account->login($account_info['login'], '', true)) {
					$json['error']['warning'] = $this->language->get('error_account');
				}
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}

	public function is_login($data = array()) {
		$data['logged'] = $this->account->isLogged();

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($data);
	}
}