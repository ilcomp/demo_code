<?php
namespace Controller\Account;

class AccountApproval extends \Controller {
	public function index() {
		$this->load->language('account/account_approval');

		$this->document->setTitle($this->language->get('heading_title'));

		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = '';
		}

		if (isset($this->request->get['filter_email'])) {
			$filter_email = $this->request->get['filter_email'];
		} else {
			$filter_email = '';
		}

		if (isset($this->request->get['filter_account_group_id'])) {
			$filter_account_group_id = $this->request->get['filter_account_group_id'];
		} else {
			$filter_account_group_id = '';
		}

		if (isset($this->request->get['filter_type'])) {
			$filter_type = $this->request->get['filter_type'];
		} else {
			$filter_type = '';
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

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_email'])) {
			$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_account_group_id'])) {
			$url .= '&filter_account_group_id=' . $this->request->get['filter_account_group_id'];
		}

		if (isset($this->request->get['filter_type'])) {
			$url .= '&filter_type=' . $this->request->get['filter_type'];
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
			'href' => $this->url->link('account/account_approval', 'user_token=' . $this->session->data['user_token'])
		);

		$data['action_filter'] = $this->url->link('account/account_approval', 'user_token=' . $this->session->data['user_token']);
		$data['action_account_autocomplete'] = $this->url->link('account/account/autocomplete', 'user_token=' . $this->session->data['user_token']);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$filter_data = array(
			'filter_name'              => $filter_name,
			'filter_email'             => $filter_email,
			'filter_account_group_id'  => $filter_account_group_id,
			'filter_type'              => $filter_type,
			'filter_date_added'        => $filter_date_added,
			'start'                    => ($page - 1) * $this->config->get('admin_limit'),
			'limit'                    => $this->config->get('admin_limit')
		);

		$this->load->model('account/account_approval');

		$account_approval_total = $this->model_account_account_approval->getTotalAccountApprovals($filter_data);

		$data['account_approvals'] = $this->model_account_account_approval->getAccountApprovals($filter_data);

		foreach ($data['account_approvals'] as &$result) {
			$result['date_added'] = date($this->language->get('date_format_short'), strtotime($result['date_added']));
			$result['approve'] = $this->url->link('account/account_approval/approve', 'user_token=' . $this->session->data['user_token'] . '&account_id=' . $result['account_id'] . '&type=' . $result['type']);
			$result['deny'] = $this->url->link('account/account_approval/deny', 'user_token=' . $this->session->data['user_token'] . '&account_id=' . $result['account_id'] . '&type=' . $result['type']);
			$result['edit'] = $this->url->link('account/account/edit', 'user_token=' . $this->session->data['user_token'] . '&account_id=' . $result['account_id']);
		}
		unset($result);

		$url = $this->request->get;
		unset($url['route']);
		unset($url['_route_']);
		$url['page'] = '{page}';

		$data['pagination'] = $this->load->controller('block/pagination', array(
			'total' => $account_approval_total,
			'page'  => $page,
			'limit' => $this->config->get('config_limit'),
			'url'   => $this->url->link('account/account_approval/account_approval', $url)
		));

		$data['results'] = $this->load->controller('block/pagination/result', array(
			'total' => $account_approval_total,
			'page'  => $page,
			'limit' => $this->config->get('config_limit')
		));

		$data['filter_name'] = $filter_name;
		$data['filter_email'] = $filter_email;
		$data['filter_account_group_id'] = $filter_account_group_id;
		$data['filter_type'] = $filter_type;
		$data['filter_date_added'] = $filter_date_added;

		$this->load->model('account/account_group');

		$data['account_groups'] = $this->model_account_account_group->getAccountGroups();

		$data['content'] = $this->load->view('account/account_approval', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	public function approve() {
		$this->load->language('account/account_approval');

		$json = array();

		if (!$this->user->hasPermission('modify', 'account/account_approval')) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			$this->load->model('account/account_approval');

			$this->model_account_account_approval->approveAccount($this->request->get['account_id']);

			$json['success'] = $this->language->get('text_success');

			$this->session->data['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}

	public function deny() {
		$this->load->language('account/account_approval');

		$json = array();

		if (!$this->user->hasPermission('modify', 'account/account_approval')) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			$this->load->model('account/account_approval');

			$this->model_account_account_approval->denyAccount($this->request->get['account_id']);

			$json['success'] = $this->language->get('text_success');

			$this->session->data['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}
}