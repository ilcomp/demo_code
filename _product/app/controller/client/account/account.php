<?php
namespace Controller\Account;

class Account extends \Controller {
	public function index($data = array()) {
		if (!$this->account->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/account');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 401 Unauthorized');

			return new \Action('account/login');
		}

		$this->load->language('account/account');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', 'language=' . $this->config->get('config_language'))
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', 'language=' . $this->config->get('config_language'))
		);

		$data['link_edit'] = $this->url->link('account/edit');
		$data['link_logout'] = $this->url->link('account/logout');

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		} 

		$this->load->model('account/account');
		$this->load->model('core/custom_field');

		$account_info = $this->model_account_account->getAccount($this->account->getId());

		$data['name'] = $account_info['name'];

		$data['custom_fields'] = array();

		$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('account_contact');
		$custom_field_values = $this->model_account_account->getAccountCustomFields($this->account->getId());

		foreach ($custom_fields as $custom_field) {
			$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

			if (isset($custom_field_values[$custom_field['custom_field_id']]) && isset($custom_field_values[$custom_field['custom_field_id']][$language_id])) {
				$value = $custom_field_values[$custom_field['custom_field_id']][$language_id];
			} elseif (isset($custom_field['setting']['default']))
				$value = $custom_field['setting']['default'];
			else
				$value = '';

			$data['custom_fields'][$custom_field['code']] = $this->model_core_custom_field->getAsValue($value, $custom_field);
		}

		$this->load->model('account/account_auth');

		$account_auth = $this->model_account_account_auth->getAccountAuthLoginByData(array('type' => 'login'));
		$data['login'] = $account_auth ? $account_auth['login'] : '';

		$account_auth = $this->model_account_account_auth->getAccountAuthLoginByData(array('type' => 'email'));
		$data['email'] = $account_auth ? $account_auth['login'] : '';

		$account_auth = $this->model_account_account_auth->getAccountAuthLoginByData(array('type' => 'telephone'));
		$data['telephone'] = $account_auth ? $account_auth['login'] : '';

		if ($data['telephone'])
			$data['telephone'] = '+' . preg_replace('/^(\d{1})(\d{3})/', '$1 ($2) ', $data['telephone']);

		$data['content'] = $this->load->view('account/account', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	public function is_login() {
		if (!$this->account->isLogged()) {
			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 401 Unauthorized');
		}

		$this->response->setOutput('Error 401: Unauthorized');
	}
}