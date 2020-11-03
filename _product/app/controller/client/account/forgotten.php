<?php
namespace Controller\Account;

class Forgotten extends \Controller {
	private $error = array();
	private $redirect;

	public function index() {
		if ($this->account->isLogged())
			$this->response->redirect($this->url->link('account/account'));

		$this->load->language('account/account');
		$this->load->language('account/forgotten');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['form'] = $this->form();

		if ($this->redirect) {
			if (!empty($data['success']))
				$this->session->data['success'] = $data['success'];

			if (!empty($data['error_warning']))
				$this->session->data['error_warning'] = $data['error_warning'];

			$data['location'] = $this->redirect;
		}

		$data['actions']['login'] = $this->url->link('account/login');

		if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->request->server['HTTP_ACCEPT'] == 'text/html') {
			$this->response->setOutput($data['form']);
		} elseif ($this->request->server['HTTP_ACCEPT'] == 'application/json') {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput($data);
		} else {
			if ($this->redirect) {
				$this->response->redirect($this->redirect);
			}

			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home')
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_forgotten'),
				'href' => $this->url->link('account/forgotten')
			);

			$data['content'] = $this->load->view('account/forgotten', $data);

			if ($this->request->server['HTTP_ACCEPT'] == 'text/html') {
				$this->response->setOutput($data['content']);
			} else {
				$this->document->setTitle($this->language->get('heading_title'));

				$this->response->setOutput($this->load->controller('common/template', $data));
			}
		}
	}

	public function form($data = array()) {
		if ($this->account->isLogged())
			return;

		$this->load->model('account/account');
		$this->load->model('account/account_auth');
		$this->load->model('account/account_attempt');

		$method = 'account_forgotten';

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->request->post['method'] == $method) && $this->validate()) {
			$this->model_account_account->editAccountLoginPassword('');

			$this->session->data['success'] = $this->language->get('text_success');

			$data['location'] = $this->url->link('account/password');

			$this->redirect = $this->url->link('account/password');
		}

		$this->load->language('account/account');
		$this->load->language('account/forgotten');

		if (isset($this->session->data['error'])) {
			$data['error_warning'] = $this->session->data['error'];

			unset($this->session->data['error']);
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

		$data['action'] = $this->url->link('account/forgotten');

		$data['actions']['login'] = $this->url->link('account/login');

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} else {
			$data['email'] = '';
		}

		$data['method'] = $method;

		if ($this->request->server['HTTP_ACCEPT'] == 'application/json') {
			return $data;
		} else {
			return $this->load->view('account/form_forgotten', $data);
		}
	}

	protected function validate() {
		$this->load->language('account/forgotten');

		if (empty($this->request->post['email']) || (utf8_strlen($this->request->post['email']) > 96) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
			$this->error['warning'] = $this->language->get('error_email');
		}

		$login_info = $this->model_account_account_attempt->getAccountAttempt($this->request->post['email'], 1);

		if ($login_info && $login_info['attempt'] >= $this->config->get('account_login_attempts') && strtotime('-1 hour') < strtotime($login_info['date_modified'])) {
			$this->error['warning'] = $this->language->get('error_attempts');
		}

		if (!$this->error) {
			$account_auth = $this->model_account_account_auth->getAccountAuthByData(array(
				'type' => 'email',
				'login' => $this->request->post['email'],
				'status' => 1
			));

			if ($account_auth) {
				$account_info = $this->model_account_account->getAccount($account_auth['account_id']);

				if ($account_info && !$account_info['status'])
					$this->error['warning'] = $this->language->get('error_approved');
			}

			if (empty($account_auth))
				$this->error['warning'] = $this->language->get('error_login');
		}

		if (!$this->error) {
			$error = $this->load->controller('extension/account_login/email', $this->request->post['email']);

			if ($error)
				$this->error['warning'] = $error;
		}

		if (!$this->error) {
			if (!$this->account->login($account_auth['type'], $account_auth['account_auth_id'], $account_auth['account_id'], '', 1)) {
				$this->error['warning'] = $this->language->get('error_login');

				$this->model_account_account_attempt->addAccountAttempt($this->request->post['email'], 1);
			} else {
				$this->model_account_account_attempt->deleteAccountAttempts($this->request->post['email'], 1);
			}
		}

		return !$this->error;
	}
}