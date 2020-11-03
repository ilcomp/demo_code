<?php
namespace Controller\Extension\Menu;

class Account extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/menu/account');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('design/menu');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_core_setting->editSetting('menu_account', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('design/extension/menu'));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('design/extension/menu')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/menu/account')
		);

		$data['action'] = $this->url->link('extension/menu/account');

		$data['cancel'] = $this->url->link('design/extension/menu');

		if (isset($this->request->post['menu_account_status'])) {
			$data['menu_account_status'] = $this->request->post['menu_account_status'];
		} else {
			$data['menu_account_status'] = $this->config->get('menu_account_status');
		}

		$data['content'] = $this->load->view('extension/menu/account', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	public function get_setting($setting = array()) {
		$this->load->language('extension/menu/account');

		$client = new \Url('');

		$setting['routes'] = array(
			array(
				'route' => '',
				'name' => $this->language->get('text_none')
			),
			array(
				'route' => $client->link('account/account'),
				'name' => $this->language->get('menu_account')
			),
			array(
				'route' => $client->link('account/edit'),
				'name' => $this->language->get('menu_edit')
			),
			array(
				'route' => $client->link('account/order'),
				'name' => $this->language->get('menu_order')
			),
			array(
				'route' => $client->link('account/logout'),
				'name' => $this->language->get('menu_logout')
			)
		);

		$this->load->model('localisation/language');

		$setting['languages'] = $this->model_localisation_language->getLanguages();

		$setting['user_token'] = $this->session->data['user_token'];

		return $this->load->view('extension/menu/account_setting', $setting);
	}

	public function install() {
	}

	public function uninstall() {
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/menu/account')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}