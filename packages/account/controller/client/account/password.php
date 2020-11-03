<?php
namespace Controller\Account;

class Password extends \Controller {
	private $error = array();

	public function index($data = array()) {
		if (!$this->account->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/password');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 401 Unauthorized');

			return new \Action('account/login');
		}

		$this->load->language('account/password');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('account/account');

		$password = $this->model_account_account->getAccountAuthLoginPassword();

		$data['password_old'] = !empty($password);

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && empty($this->request->post['form_method']) && $this->validate($password)) {
			foreach (array('login', 'email', 'telephone') as $type) {
				$this->model_account_account->editAccountLoginPassword($this->request->post['password']);
			}

			$data['success'] = $this->language->get('text_success');

			$data['refresh'] = true;
		}

		$data['error'] = $this->error;

		$data['action'] = $this->url->link('account/password');

		if (isset($this->request->post['password'])) {
			$data['password'] = $this->request->post['password'];
		} else {
			$data['password'] = '';
		}

		if (isset($this->request->post['password_confirm'])) {
			$data['password_confirm'] = $this->request->post['password_confirm'];
		} else {
			$data['password_confirm'] = '';
		}

		if (!empty($data['refresh'])) {
			if (!empty($data['success']))
				$this->session->data['success'] = $data['success'];

			if (!empty($data['error_warning']))
				$this->session->data['error_warning'] = $data['error_warning'];
		}

		$data['back'] = $this->url->link('account/account');

		$data['action'] = $this->url->link('account/password');

		if ($this->request->server['HTTP_ACCEPT'] == 'application/json') {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput($data);
		} else {
			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home')
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_account'),
				'href' => $this->url->link('account/account')
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('account/password')
			);

			$data['content'] = $this->load->view('account/password', $data);

			if (!empty($data['return_content'])) {
				return $data['content'];
			} elseif ($this->request->server['HTTP_ACCEPT'] == 'text/html') {
				$this->response->setOutput($data['content']);
			} else {
				$this->document->setTitle($this->language->get('heading_title'));

				$this->response->setOutput($this->load->controller('common/template', $data));
			}
		}
	}

	protected function validate($password_old) {
		if ($password_old) {
			if (empty($this->request->post['password_old']) || !password_verify($this->request->post['password_old'], $password_old)) {
				$this->error['password_old'] = $this->language->get('error_password_old');
			}
		}

		if (utf8_strlen($this->request->post['password']) < 4 || utf8_strlen($this->request->post['password']) > 40) {
			$this->error['password'] = $this->language->get('error_password');
		}

		if (isset($this->request->post['password_confirm']) && $this->request->post['password_confirm'] != $this->request->post['password']) {
			$this->error['password_confirm'] = $this->language->get('error_password_confirm');
		}

		return !$this->error;
	}
}