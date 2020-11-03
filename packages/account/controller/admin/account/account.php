<?php
namespace Controller\Account;

class Account extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('account/account');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('account/account_modify');

		$this->getList();
	}

	public function add() {
		$this->load->language('account/account');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('account/account_modify');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_account_account_modify->addAccount($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_account_group_id'])) {
				$url .= '&filter_account_group_id=' . $this->request->get['filter_account_group_id'];
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}

			if (isset($this->request->get['filter_ip'])) {
				$url .= '&filter_ip=' . $this->request->get['filter_ip'];
			}

			if (isset($this->request->get['filter_date_added'])) {
				$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('account/account', 'user_token=' . $this->session->data['user_token'] . $url));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('account/account');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('account/account_modify');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_account_account_modify->editAccount($this->request->get['account_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_account_group_id'])) {
				$url .= '&filter_account_group_id=' . $this->request->get['filter_account_group_id'];
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}

			if (isset($this->request->get['filter_ip'])) {
				$url .= '&filter_ip=' . $this->request->get['filter_ip'];
			}

			if (isset($this->request->get['filter_date_added'])) {
				$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('account/account', 'user_token=' . $this->session->data['user_token'] . $url));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('account/account');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('account/account_modify');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $account_id) {
				$this->model_account_account_modify->deleteAccount($account_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_account_group_id'])) {
				$url .= '&filter_account_group_id=' . $this->request->get['filter_account_group_id'];
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}

			if (isset($this->request->get['filter_ip'])) {
				$url .= '&filter_ip=' . $this->request->get['filter_ip'];
			}

			if (isset($this->request->get['filter_date_added'])) {
				$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('account/account', 'user_token=' . $this->session->data['user_token'] . $url));
		}

		$this->getList();
	}

	protected function getList() {
		$filter = array_filter($this->request->get, function($key) {
			return strpos($key, 'filter_') === 0;
		}, ARRAY_FILTER_USE_KEY);

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
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

		if (isset($this->request->get['filter_account_group_id'])) {
			$url .= '&filter_account_group_id=' . $this->request->get['filter_account_group_id'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_ip'])) {
			$url .= '&filter_ip=' . $this->request->get['filter_ip'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
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
			'href' => $this->url->link('account/account', 'user_token=' . $this->session->data['user_token'] . $url)
		);

		$data['actions']['add'] = $this->url->link('account/account/add', 'user_token=' . $this->session->data['user_token'] . $url);
		$data['actions']['delete'] = $this->url->link('account/account/delete', 'user_token=' . $this->session->data['user_token'] . $url);

		$this->load->model('core/store');

		$stores = $this->model_core_store->getStores();

		$filter_data = array_merge($filter, array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit'),
			'limit' => $this->config->get('config_limit')
		));

		$account_total = $this->model_account_account_modify->getTotalAccounts($filter_data);

		$data['accounts'] = $this->model_account_account_modify->getAccounts($filter_data);

		foreach ($data['accounts'] as &$result) {
			$result['store_data'] = array();

			$result['store_data'][] = array(
				'name' => $this->config->get('config_name'),
				'href' => $this->url->link('account/account/login', 'user_token=' . $this->session->data['user_token'] . '&account_id=' . $result['account_id'] . '&store_id=0')
			);

			foreach ($stores as $store) {
				$result['store_data'][] = array(
					'name' => $store['name'],
					'href' => $this->url->link('account/account/login', 'user_token=' . $this->session->data['user_token'] . '&account_id=' . $result['account_id'] . '&store_id=' . $result['store_id'])
				);
			}

			$result['status'] = $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled');
			$result['date_added'] = date($this->language->get('date_format_short'), strtotime($result['date_added']));
			$result['edit'] = $this->url->link('account/account/edit', 'user_token=' . $this->session->data['user_token'] . '&account_id=' . $result['account_id'] . $url);
		}
		unset($result);

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_account_group_id'])) {
			$url .= '&filter_account_group_id=' . $this->request->get['filter_account_group_id'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_ip'])) {
			$url .= '&filter_ip=' . $this->request->get['filter_ip'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link('account/account', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url);
		$data['sort_account_group'] = $this->url->link('account/account', 'user_token=' . $this->session->data['user_token'] . '&sort=account_group' . $url);
		$data['sort_status'] = $this->url->link('account/account', 'user_token=' . $this->session->data['user_token'] . '&sort=status' . $url);
		$data['sort_ip'] = $this->url->link('account/account', 'user_token=' . $this->session->data['user_token'] . '&sort=ip' . $url);
		$data['sort_date_added'] = $this->url->link('account/account', 'user_token=' . $this->session->data['user_token'] . '&sort=date_added' . $url);

		$url = $this->request->get;
		unset($url['route']);
		unset($url['_route_']);
		$url['page'] = '{page}';

		$data['pagination'] = $this->load->controller('block/pagination', array(
			'total' => $account_total,
			'page'  => $page,
			'limit' => $this->config->get('config_limit'),
			'url'   => $this->url->link('account/account', $url)
		));

		$data['results'] = $this->load->controller('block/pagination/result', array(
			'total' => $account_total,
			'page'  => $page,
			'limit' => $this->config->get('config_limit')
		));

		$this->load->model('account/account_group');

		$data['account_groups'] = $this->model_account_account_group->getAccountGroups();

		foreach ($filter as $key => $value) {
			$data[$key] = $value;
		}

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['content'] = $this->load->view('account/account_list', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['account_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->get['account_id'])) {
			$data['account_id'] = (int)$this->request->get['account_id'];
		} else {
			$data['account_id'] = 0;
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
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
			'href' => $this->url->link('account/account', 'user_token=' . $this->session->data['user_token'] . $url)
		);

		if (!isset($this->request->get['account_id'])) {
			$data['action'] = $this->url->link('account/account/add', 'user_token=' . $this->session->data['user_token'] . $url);
		} else {
			$data['action'] = $this->url->link('account/account/edit', 'user_token=' . $this->session->data['user_token'] . '&account_id=' . $this->request->get['account_id'] . $url);
		}

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('account/account', 'user_token=' . $this->session->data['user_token'] . $url);

		if (isset($this->request->get['account_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$account_info = $this->model_account_account_modify->getAccount($this->request->get['account_id']);
		}

		$this->load->model('account/account_group');

		$data['account_groups'] = $this->model_account_account_group->getAccountGroups();

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($account_info)) {
			$data['name'] = $account_info['name'];
		} else {
			$data['name'] = '';
		}

		if (isset($this->request->post['account_group_id'])) {
			$data['account_group_id'] = $this->request->post['account_group_id'];
		} elseif (!empty($account_info)) {
			$data['account_group_id'] = $account_info['account_group_id'];
		} else {
			$data['account_group_id'] = $this->config->get('account_group_id');
		}

		if (isset($this->request->post['language_id'])) {
			$data['language_id'] = $this->request->post['language_id'];
		} elseif (!empty($account_info)) {
			$data['language_id'] = $account_info['language_id'];
		} else {
			$data['language_id'] = $this->config->get('config_language_id');
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($account_info)) {
			$data['status'] = $account_info['status'];
		} else {
			$data['status'] = 1;
		}

		if (isset($this->request->post['address'])) {
			$data['address'] = (array)$this->request->post['address'];
		} elseif (!empty($account_info)) {
			$data['address'] = (array)$this->model_account_account_modify->getAccountAddress($account_info['account_id']);
		} else {
			$data['address'] = array();
		}

		if (isset($this->request->post['account_custom_fields'])) {
			$custom_field_values = $this->request->post['account_custom_fields'];
		} elseif (!empty($account_info)) {
			$custom_field_values = $this->model_account_account_modify->getAccountCustomFields($account_info['account_id']);
		} else {
			$custom_field_values = array();
		}

		$this->load->model('account/account_auth');

		if (!empty($account_info['account_id']))
			$data['account_auths'] = $this->model_account_account_auth->getAccountAuths($account_info['account_id']);
		else
			$data['account_auths'] = array();

		$this->load->model('core/custom_field');

		$custom_fields_contact = $this->model_core_custom_field->getAsField($custom_field_values, 'account_contact');

		$data['custom_fields_contact'] = $this->load->controller('setting/custom_field/render', $custom_fields_contact);

		$custom_fields_address = $this->model_core_custom_field->getAsField($custom_field_values, 'account_address');

		$data['custom_fields_address'] = $this->load->controller('setting/custom_field/render', $custom_fields_address);

		$custom_fields_address = $this->model_core_custom_field->getAsField($custom_field_values, 'account_group_' . $data['account_group_id']);

		$data['custom_fields_account_group'] = $this->load->controller('setting/custom_field/render', $custom_fields_address);

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$data['content'] = $this->load->view('account/account_form', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'account/account')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		// $this->load->model('account/account_auth');

		// $account_info = $this->model_account_account_auth->getAccountAuth($this->request->post['login']);

		// if (!isset($this->request->get['account_id'])) {
		// 	if ($account_info) {
		// 		$this->error['warning'] = $this->language->get('error_exists');
		// 	}
		// } else {
		// 	if ($account_info && ($this->request->get['account_id'] != $account_info['account_id'])) {
		// 		$this->error['warning'] = $this->language->get('error_exists');
		// 	}
		// }

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'account/account')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function login() {
		if (!isset($this->request->get['account_id']))
			new \Action('error/not_found');

		$this->load->model('account/account_modify');

		$account_info = $this->model_account_account_modify->getAccount($account_id);

		if (!$account_info)
			new \Action('error/not_found');

		$this->load->model('account/account_auth');

		$login = token(16);
		$token = token(64);

		$this->model_account_account_auth->addAccountAuth(array(
			'account_id' => $account_id,
			'type' => 'token',
			'login' => $login,
			'token' => $token
		));

		if (isset($this->request->get['store_id'])) {
			$store_id = $this->request->get['store_id'];
		} else {
			$store_id = 0;
		}

		$this->load->model('core/store');

		$store_info = $this->model_core_store->getStore($store_id);

		$client = new \Url(!empty($store_info) ? $store_info['url'] : HTTP_APPLICATION_CLIENT);

		$this->response->redirect($client->link('extension/account_login/token', 'login=' . $login . '&login_token=' . $token));
	}

	public function ip() {
		$this->load->language('account/account');

		if (isset($this->request->get['account_id'])) {
			$account_id = $this->request->get['account_id'];
		} else {
			$account_id = 0;
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$this->load->model('account/account_ip');
		$this->load->model('core/store');

		$data['ips'] = array();

		$results = $this->model_account_account_ip->getAccountIps(array(
			'account_id' => $account_id,
			'start' => ($page - 1) * 10,
			'limir' => 10
		));

		foreach ($results as $result) {
			$store_info = $this->model_core_store->getStore($result['store_id']);

			if ($store_info) {
				$result['store'] = $store_info['name'];
			} elseif (!$result['store_id']) {
				$result['store'] = $this->config->get('config_name');
			} else {
				$result['store'] = '';
			}

			$result['account'] = $this->model_account_account_ip->getTotalAccountsByIp($result['ip']);
			$result['date_added'] = date($this->language->get('datetime_format'), strtotime($result['date_added']));
			$result['filter_ip'] = $this->url->link('account/account', 'user_token=' . $this->session->data['user_token'] . '&filter_ip=' . $result['ip']);

			$data['ips'][] = $result;
		}

		$ip_total = $this->model_account_account_ip->getTotalAccountIps($account_id);

		$url = $this->request->get;
		unset($url['route']);
		unset($url['_route_']);
		$url['page'] = '{page}';

		$data['pagination'] = $this->load->controller('block/pagination', array(
			'total' => $ip_total,
			'page'  => $page,
			'limit' => $this->config->get('config_limit'),
			'url'   => $this->url->link('account/account/ip', $url)
		));

		$data['results'] = $this->load->controller('block/pagination/result', array(
			'total' => $ip_total,
			'page'  => $page,
			'limit' => $this->config->get('config_limit')
		));

		$this->response->setOutput($this->load->view('account/account_ip', $data));
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_login']) || isset($this->request->get['filter_email']) || isset($this->request->get['filter_telephone'])) {
			if (isset($this->request->get['filter_name'])) {
				$filter_name = $this->request->get['filter_name'];
			} else {
				$filter_name = '';
			}
			if (isset($this->request->get['filter_login'])) {
				$filter_login = $this->request->get['filter_login'];
			} else {
				$filter_login = '';
			}

			if (isset($this->request->get['filter_email'])) {
				$filter_email = $this->request->get['filter_email'];
			} else {
				$filter_email = '';
			}

			if (isset($this->request->get['filter_telephone'])) {
				$filter_telephone = $this->request->get['filter_telephone'];
			} else {
				$filter_telephone = '';
			}

			$this->load->model('account/account_modify');

			$filter_data = array(
				'filter_name'      => '%' . $filter_name . '%',
				'filter_login'     => '%' . $filter_login . '%',
				'filter_email'     => '%' . $filter_email . '%',
				'filter_telephone' => '%' . $filter_telephone . '%',
				'start'            => 0,
				'limit'            => 5
			);

			$results = $this->model_account_account_modify->getAccounts($filter_data);

			foreach ($results as $result) {
				$result['name'] = strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'));
				$json[] = $result;
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}

	public function custom_field() {
		if (empty($this->request->post['account_group_id'])) {
			$this->response->setOutput('');

			return;
		}

		$this->load->model('account/account_modify');
		$this->load->model('core/custom_field');

		if (!empty($this->request->get['account_id'])) {
			$custom_field_values = $this->model_account_account_modify->getAccountCustomFields($this->request->get['account_id']);
		} else {
			$custom_field_values = array();
		}

		$custom_fields_address = $this->model_core_custom_field->getAsField($custom_field_values, 'account_group_' . $this->request->post['account_group_id']);

		$this->response->setOutput($this->load->controller('setting/custom_field/render', $custom_fields_address));
	}
}