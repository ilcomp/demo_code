<?php
namespace Controller\Account;

class AccountAttempt extends \Controller {
	public function index() {
		$this->load->language('account/account_attempt');

		$this->document->setTitle($this->language->get('heading_title'));

		if (isset($this->request->get['filter_login'])) {
			$filter_login = $this->request->get['filter_login'];
		} else {
			$filter_login = '';
		}

		if (isset($this->request->get['filter_ip'])) {
			$filter_ip = $this->request->get['filter_ip'];
		} else {
			$filter_ip = '';
		}

		if (isset($this->request->get['filter_date_added'])) {
			$filter_date_added = $this->request->get['filter_date_added'];
		} else {
			$filter_date_added = '';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['filter_login'])) {
			$url .= '&filter_login=' . urlencode(html_entity_decode($this->request->get['filter_login'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_ip'])) {
			$url .= '&filter_ip=' . $this->request->get['filter_ip'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('account/account_attempt', 'user_token=' . $this->session->data['user_token'])
		);

		$data['action_filter'] = $this->url->link('account/account_attempt', 'user_token=' . $this->session->data['user_token']);
		$data['action_account_autocomplete'] = $this->url->link('account/account/autocomplete', 'user_token=' . $this->session->data['user_token']);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$filter_data = array(
			'filter_login' => $filter_login,
			'filter_ip'    => $filter_ip,
			'filter_date_added' => $filter_date_added,
			'start'        => ($page - 1) * $this->config->get('admin_limit'),
			'limit'        => $this->config->get('admin_limit')
		);

		$this->load->model('account/account_attempt');

		$account_attempt_total = $this->model_account_account_attempt->getTotalAccountAttempts($filter_data);

		$data['account_attempts'] = $this->model_account_account_attempt->getAccountAttempts($filter_data);

		foreach ($data['account_attempts'] as &$result) {
			$result['date_added'] = date($this->language->get('date_format_short'), strtotime($result['date_added']));

			if ($result['attempt'] >= $this->config->get('account_login_attempts')) {
				$result['unlock'] = $this->url->link('account/account_attempt/unlock', 'login=' . $result['login'] . '&forgotten=' . $result['forgotten'] . $url);
			} else {
				$result['unlock'] = '';
			}
		}
		unset($result);

		$url = $this->request->get;
		unset($url['route']);
		unset($url['_route_']);
		$url['page'] = '{page}';

		$data['pagination'] = $this->load->controller('block/pagination', array(
			'total' => $account_attempt_total,
			'page'  => $page,
			'limit' => $this->config->get('config_limit'),
			'url'   => $this->url->link('account/account_attempt/account_attempt', $url)
		));

		$data['results'] = $this->load->controller('block/pagination/result', array(
			'total' => $account_attempt_total,
			'page'  => $page,
			'limit' => $this->config->get('config_limit')
		));

		$data['filter_login'] = $filter_login;
		$data['filter_ip'] = $filter_ip;
		$data['filter_date_added'] = $filter_date_added;

		$this->load->model('account/account_group');

		$data['account_groups'] = $this->model_account_account_group->getAccountGroups();

		$data['content'] = $this->load->view('account/account_attempt', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	public function unlock() {
		$this->load->language('account/account_attempt');

		$data = array();

		if (!$this->user->hasPermission('modify', 'account/account_attempt') || !isset($this->request->get['login'])) {
			$data['error'] = $this->language->get('error_permission');
		} else {
			$this->load->model('account/account_attempt');

			$this->model_account_account_attempt->deleteAccountAttempts($this->request->get['login'], !empty($this->request->get['forgotten']));

			$data['success'] = $this->language->get('text_success');

			$this->session->data['success'] = $this->language->get('text_success');
		}

		if ($this->request->server['HTTP_ACCEPT'] == 'application/json') {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput($data);
		} else {
			$this->response->redirect($this->url->link('account/account_attempt'));
		}
	}
}