<?php
namespace Controller\Extension\AccountLogin;

class Telephone extends \Controller {
	public function index($telephone = false) {
		if ($telephone !== false && $this->config->get('sms_engine') && $this->load->controller('extension/sms/' . $this->config->get('sms_engine') . '/test')) {
			$this->load->language('extension/account_login/telephone');

			$data = array();

			if (isset($this->request->post['code_confirm_id']) && isset($this->session->data['code_confirm'][$this->request->post['code_confirm_id']])) {
				$code_confirm = $this->session->data['code_confirm'][$this->request->post['code_confirm_id']];
			} else {
				$code_confirm = '';

				$data['error'] = $this->language->get('error_code_confirm');
			}

			if (empty($data['error']) && (!isset($code_confirm['code']) || !isset($code_confirm['date_added']) || !isset($code_confirm['telephone']) || $code_confirm['telephone'] != $telephone)) {
				$data['error'] = $this->language->get('error_code_confirm');
			}

			if (!$data['error'] && ($code_confirm['date_added'] - time()) > 300) {
				$data['error'] = $this->language->get('error_code_confirm_time');

				unset($this->request->post['code_confirm']);
			}

			if (!$data['error']) {
				if (!isset($this->request->post['code_confirm']) || $this->request->post['code_confirm'] != $code_confirm['code']) {
					$data['error'] = $this->language->get('error_code');

					$this->config->set('code_confirm_id', $this->request->post['code_confirm_id']);
					$this->config->set('code_confirm_date_added', $code_confirm['date_added']);

					return $data['error'];
				} else
					unset($this->request->post['code_confirm']);
			}

			if (!$data['error']) {
				unset($this->session->data['code_confirm'][$this->request->post['code_confirm_id']]);
			} else {
				$code_len = 8;
				$code_confirm_id = rand(pow(10, $code_len-1), pow(10, $code_len)-1);
				$code_len = 4;
				$code = rand(pow(10, $code_len-1), pow(10, $code_len)-1);
				$date_added = time();

				$this->session->data['code_confirm'][$code_confirm_id]['telephone'] = $telephone;
				$this->session->data['code_confirm'][$code_confirm_id]['code'] = $code;
				$this->session->data['code_confirm'][$code_confirm_id]['date_added'] = $date_added;

				$this->config->set('code_confirm_id', $code_confirm_id);
				$this->config->set('code_confirm_date_added', $date_added);

				$error = $this->load->controller('extension/sms/' . $this->config->get('sms_engine'), $telephone, sprintf($this->language->get('text_message'), $code));

				if ($error)
					$this->log->write($error);
				else
					return $data['error'];
			}
		}
	}

	public function edit($json = array()) {
		if (!$this->account->isLogged() || !isset($this->request->post['telephone'])) {
			new Action('error/not_found');
		}

		$this->request->post['telephone'] = trim($this->request->post['telephone']);

		if (utf8_strlen($this->request->post['telephone']) < 3 || utf8_strlen($this->request->post['telephone']) > 32)
			$this->error['telephone'] = $this->language->get('error_telephone');
		elseif ($this->model_account_account_auth->getAccountAuthByData(array('login' => $this->request->post['telephone'])))
			$this->error['telephone'] = $this->language->get('error_exists');

		$this->load->model('account/account_auth');

		if (empty($error)) {
			$auth = $this->model_account_account_auth->getAccountAuthLogin('telephone');

			if ($auth)
				$this->model_account_account_auth->updateAccountAuthLogin('telephone', $this->request->post['telephone']);
			else {
				$this->model_account_account_auth->addAccountAuth(array(
					'account_id' => $this->account->getId(),
					'type' => 'email',
					'login' => $this->request->post['email'],
					'status' => in_array('email', $this->config->get('account_auth_login'))
				));
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}

	private $error = array();

	public function form($data = array()) {
		if (!$this->account->isLogged()) {
			return;
		}

		$this->load->model('account/account');
		$this->load->model('account/account_auth');
		$this->load->model('account/account_attempt');

		$this->load->language('account/account');
		$this->load->language('account/password');
		$this->load->language('account/edit');
		$this->load->language('extension/account_login/telephone');

		$password = $this->model_account_account->getAccountAuthLoginPassword();

		$data['password_old'] = !empty($password);

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate($password)) {
			$this->model_account_account_auth->deleteAccountAuthLoginByData(array('type' => 'telephone'));

			$this->model_account_account_auth->addAccountAuth(array(
				'account_id' => $this->account->getId(),
				'type' => 'telephone',
				'login' => $this->request->post['telephone'],
				'status' => in_array('telephone', $this->config->get('account_auth_login'))
			));

			// Clear any previous login attempts for unregistered accounts.
			$this->model_account_account_attempt->deleteAccountAttempts($this->request->post['telephone']);

			$data['success'] = $this->language->get('text_success');

			$data['refresh'] = true;
		}

		$data['text_account_already'] = sprintf($this->language->get('text_account_already'), $this->url->link('account/login'));

		$data['error'] = $this->error;

		if (isset($this->session->data['error_warning'])) {
			$data['error_warning'] = $this->session->data['error_warning'];

			unset($this->session->data['error_warning']);
		} elseif (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if ($this->config->get('code_confirm_id')) {
			$data_confirm['code_confirm_id'] = $this->config->get('code_confirm_id');
			$data_confirm['date_added'] = $this->config->get('code_confirm_date_added');

			if (isset($this->request->post['code_confirm']))
				$data_confirm['code_confirm'] = $this->request->post['code_confirm'];
			else
				$data_confirm['code_confirm'] = '';

			$data['code_confirm'] = $this->load->view('account/code_confirm', $data_confirm);
		} else {
			$data['code_confirm'] = '';
		}

		if (!empty($data['refresh'])) {
			if (!empty($data['success']))
				$this->session->data['success'] = $data['success'];

			if (!empty($data['error_warning']))
				$this->session->data['error_warning'] = $data['error_warning'];
		}

		$data['action'] = $this->url->link('extension/account_login/telephone/form');

		if (isset($this->request->post['telephone'])) {
			$data['telephone'] = $this->request->post['telephone'];

			if ($data['telephone'])
				$data['telephone'] = '+' . preg_replace('/^(\d{1})(\d{3})/', '$1 ($2) ', $data['telephone']);
		} else {
			$data['telephone'] = '';
		}

		if ($this->request->server['HTTP_ACCEPT'] == 'application/json') {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput($json);
		} else {
			$data['content'] = $this->load->view('extension/account_login/telephone', $data);

			if (!empty($data['return_content'])) {
				return $data['content'];
			} else {
				$this->response->setOutput($data['content']);
			}
		}
	}

	private function validate($password_old) {
		if (isset($this->request->post['telephone'])) {
			$this->request->post['telephone'] = preg_replace('/\D/', '', preg_replace('/^8/', '7', $this->request->post['telephone']));

			if (utf8_strlen($this->request->post['telephone']) < 3 || utf8_strlen($this->request->post['telephone']) > 32)
				$this->error['telephone'] = $this->language->get('error_telephone');
			elseif ($this->model_account_account_auth->getAccountAuthByData(array('login' => $this->request->post['telephone'], 'exclude_login' => true)))
				$this->error['telephone'] = $this->language->get('error_exists');
		}

		if ($password_old) {
			if (empty($this->request->post['password_old']) || !password_verify($this->request->post['password_old'], $password_old)) {
				$this->error['password_old'] = $this->language->get('error_password_old');
			}
		}

		if (!$this->error && in_array('telephone', $this->config->get('account_auth_login'))) {
			if (isset($this->request->post['telephone']))
				$error = $this->load->controller('extension/account_login/telephone', $this->request->post['telephone']);
			else
				$error = $this->language->get('error_register');

			if ($error)
				$this->error['warning'] = $error;
		}

		return !$this->error;
	}
}