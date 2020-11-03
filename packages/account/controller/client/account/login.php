<?php
namespace Controller\Account;

class Login extends \Controller {
	private $error = array();
	private $redirect;

	public function index() {
		if ($this->account->isLogged())
			$this->response->redirect($this->url->link('account/account'));

		$this->load->language('account/account');
		$this->load->language('account/login');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['form'] = $this->form();

		if ($this->redirect) {
			if (!empty($data['success']))
				$this->session->data['success'] = $data['success'];

			if (!empty($data['error_warning']))
				$this->session->data['error_warning'] = $data['error_warning'];

			$data['location'] = $this->redirect;
		}

		$data['actions']['register'] = $this->url->link('account/register');
		$data['actions']['forgotten'] = $this->url->link('account/forgotten');

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
				'text' => $this->language->get('text_login'),
				'href' => $this->url->link('account/login')
			);

			$data['content'] = $this->load->view('account/login', $data);

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

		$method = 'account_login';

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->request->post['method'] == $method) && $this->validate()) {
			$this->load->controller('order/checkout/reset');

			// Log the IP info
			// $this->model_account_account->addLogin($this->account->getId(), $this->request->server['REMOTE_ADDR']);

			if (!empty($this->request->post['redirect']) && $this->request->post['redirect'] != $this->url->link('account/logout') && (strpos($this->request->post['redirect'], $this->config->get('config_url')) !== false)) {
				$data['location'] = str_replace('&amp;', '&', $this->request->post['redirect']);

				$this->redirect = str_replace('&amp;', '&', $this->request->post['redirect']);
			} else {
				$data['refresh'] = true;

				$this->redirect = $this->url->link('account/account');
			}
		}

		$this->load->language('account/account');
		$this->load->language('account/login');

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

		$data['action'] = $this->url->link('account/login');

		$data['actions']['register'] = $this->url->link('account/register');
		$data['actions']['forgotten'] = $this->url->link('account/forgotten');

		if (isset($this->request->post['redirect']) && (strpos($this->request->post['redirect'], $this->config->get('config_url')) !== false)) {
			$data['redirect'] = $this->request->post['redirect'];
		} elseif (isset($this->session->data['redirect'])) {
			$data['redirect'] = $this->session->data['redirect'];

			unset($this->session->data['redirect']);
		} else {
			$data['redirect'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['login'])) {
			$data['login'] = $this->request->post['login'];
		} else {
			$data['login'] = '';
		}

		if (isset($this->request->post['password'])) {
			$data['password'] = $this->request->post['password'];
		} else {
			$data['password'] = '';
		}

		$this->load->model('tool/image');
		$this->load->model('core/extension');

		$sort_order = array();

		$results = $this->model_core_extension->getExtensions('account_login');

		foreach ($results as $key => $value) {
			$sort_order[$key] = $this->config->get('account_login_' . $value['code'] . '_sort_order');
		}

		array_multisort($sort_order, SORT_ASC, $results);

		$data['socials'] = array();

		foreach ($results as $result) {
			if ($this->config->get('account_login_' . $result['code'] . '_status')) {
				$data['socials'][] = array(
					'code' => str_replace('social_', '', $result['code']),
					'image' => $this->model_tool_image->link('social/' . str_replace('social_', '', $result['code']) . '.svg'),
					'link' => $this->url->link('extension/account_login/' . $result['code'] . '/form')
				);
			}
		}

		$data['method'] = $method;

		if ($this->request->server['HTTP_ACCEPT'] == 'application/json') {
			return $data;
		} else {
			return $this->load->view('account/form_login', $data);
		}
	}

	protected function validate() {
		if (empty($this->config->get('account_auth_login')))
			new \Action('error/not_found');

		if (!isset($this->request->post['login']))
			$this->request->post['login'] = '';

		if (!$this->request->post['login'] || !isset($this->request->post['password']) || utf8_strlen(html_entity_decode($this->request->post['password'], ENT_QUOTES, 'UTF-8')) < 3) {
			$this->error['warning'] = $this->language->get('error_login');
		}

		$login_info = $this->model_account_account_attempt->getAccountAttempt($this->request->post['login']);

		if ($login_info && $login_info['attempt'] >= $this->config->get('account_login_attempts') && strtotime('-1 hour') < strtotime($login_info['date_modified'])) {
			$this->error['warning'] = $this->language->get('error_attempts');
		}

		if (!$this->error) {
			foreach ($this->config->get('account_auth_login') as $type) {
				$account_auth = $this->model_account_account_auth->getAccountAuthByData(array(
					'type' => $type,
					'login' => $this->request->post['login'],
					'status' => 1
				));

				if ($account_auth) {
					$account_info = $this->model_account_account->getAccount($account_auth['account_id']);

					if ($account_info && !$account_info['status'])
						$this->error['warning'] = $this->language->get('error_approved');

					break;
				}
			}

			if (empty($account_auth))
				$this->error['warning'] = $this->language->get('error_login');
		}

		if (!$this->error) {
			if (!$this->account->login($account_auth['type'], $account_auth['account_auth_id'], $account_auth['account_id'], html_entity_decode($this->request->post['password'], ENT_QUOTES, 'UTF-8'))) {
				$this->error['warning'] = $this->language->get('error_login');

				$this->model_account_account_attempt->addAccountAttempt($this->request->post['login']);
			} else {
				$this->model_account_account_attempt->deleteAccountAttempts($this->request->post['login']);
			}
		}

		return !$this->error;
	}
}