<?php
namespace Controller\Extension\System;

class Account extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('core/setting');
		$this->load->language('extension/system/account');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_core_setting->editSetting('account', $this->request->post, $this->request->get['store_id']);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/system', 'user_token=' . $this->session->data['user_token']));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['group_display'])) {
			$data['error_group_display'] = $this->error['group_display'];
		} else {
			$data['error_group_display'] = '';
		}

		if (isset($this->error['login_attempts'])) {
			$data['error_login_attempts'] = $this->error['login_attempts'];
		} else {
			$data['error_login_attempts'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/system', 'user_token=' . $this->session->data['user_token'])
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/system/account', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $this->request->get['store_id'])
		);

		$data['action'] = $this->url->link('extension/system/account', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $this->request->get['store_id']);

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('marketplace/system', 'user_token=' . $this->session->data['user_token']);

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->get['store_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$setting_info = $this->model_core_setting->getSetting('account', $this->request->get['store_id']);
		}

		if (isset($this->request->post['account_status'])) {
			$data['account_status'] = $this->request->post['account_status'];
		} elseif ($setting_info) {
			$data['account_status'] = $setting_info['account_status'];
		} else {
			$data['account_status'] = 0;
		}

		if (isset($this->request->post['account_group_id'])) {
			$data['account_group_id'] = $this->request->post['account_group_id'];
		} elseif (isset($setting_info['account_group_id'])) {
			$data['account_group_id'] = $setting_info['account_group_id'];
		} else {
			$data['account_group_id'] = 0;
		}

		$this->load->model('account/account_group');

		$data['account_groups'] = $this->model_account_account_group->getAccountGroups();

		if (isset($this->request->post['account_group_display'])) {
			$data['account_group_display'] = $this->request->post['account_group_display'];
		} elseif (isset($setting_info['account_group_display']) && is_array($setting_info['account_group_display'])) {
			$data['account_group_display'] = $setting_info['account_group_display'];
		} else {
			$data['account_group_display'] = array();
		}

		if (isset($this->request->post['account_login_attempts'])) {
			$data['account_login_attempts'] = $this->request->post['account_login_attempts'];
		} elseif (isset($setting_info['account_login_attempts'])) {
			$data['account_login_attempts'] = $setting_info['account_login_attempts'];
		} else {
			$data['account_login_attempts'] = 5;
		}

		if (isset($this->request->post['account_name'])) {
			$data['account_name'] = (array)$this->request->post['account_name'];
		} elseif (isset($setting_info['account_name'])) {
			$data['account_name'] = (array)$setting_info['account_name'];
		} else {
			$data['account_name'] = array();
		}

		if (isset($this->request->post['account_agrement_information_id'])) {
			$data['account_agrement_information_id'] = $this->request->post['account_agrement_information_id'];
		} elseif (isset($setting_info['account_agrement_information_id'])) {
			$data['account_agrement_information_id'] = $setting_info['account_agrement_information_id'];
		} else {
			$data['account_agrement_information_id'] = 0;
		}

		if (isset($this->request->post['account_police_information_id'])) {
			$data['account_police_information_id'] = $this->request->post['account_police_information_id'];
		} elseif (isset($setting_info['account_police_information_id'])) {
			$data['account_police_information_id'] = $setting_info['account_police_information_id'];
		} else {
			$data['account_police_information_id'] = 0;
		}

		if ($this->config->get('information_status')) {
			$this->load->model('information/information');

			$data['informations'] = $this->model_information_information->getInformations(array('filter_category_id' => -1));
		} else {
			$data['informations'] = array();
		}

		$data['content'] = $this->load->view('extension/system/account', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	public function install() {
		$table = new \Model\DBTable($this->registry);
		$table->create('account');

		$this->load->model('core/event');
		$this->model_core_event->addEvent('account', 'api/controller/startup/event/after', 'event/account/startup');
		$this->model_core_event->addEvent('account', 'admin/controller/startup/event/after', 'event/account/startup');
		$this->model_core_event->addEvent('account', 'client/controller/startup/event/after', 'event/account/startup');

		$this->model_core_event->addEvent('account_mail', 'admin/model/account/account_approval/approveAccount/after', 'mail/account/approve');
		$this->model_core_event->addEvent('account_mail', 'admin/model/account/account_approval/denyAccount/after', 'mail/account/deny');
		$this->model_core_event->addEvent('account_mail', 'client/model/account/account/editCode/after', 'mail/account/forgotten');
		$this->model_core_event->addEvent('account_mail', 'client/model/account/account/addAccount/after', 'mail/account/register');
		$this->model_core_event->addEvent('account_mail', 'client/model/account/account/addAccount/after', 'mail/account/register_alert');

		$this->load->model('design/seo_url');
		$this->model_design_seo_url->addSeoUrl(array('store_id' => 0, 'language_id' => 1, 'query' => 'route=account/account', 'keyword' => 'account', 'push' => 'route=account/account'));
	}

	public function uninstall() {
		$table = new \Model\DBTable($this->registry);
		$table->delete('account');

		$this->load->model('core/event');
		$this->model_core_event->deleteEventByCode('account');
		$this->model_core_event->deleteEventByCode('account_mail');

		$this->load->model('design/seo_url');
		$this->model_design_seo_url->deleteSeoUrlByQuery('route=account/');
	}

	public function update() {
		// $table = new Model\DBTable($this->registry);
		// // $table->update('information');
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/system/account')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!empty($this->request->post['account_group_display']) && !in_array($this->request->post['account_group_id'], $this->request->post['account_group_display'])) {
			$this->error['group_display'] = $this->language->get('error_group_display');
		}

		if ($this->request->post['account_login_attempts'] < 1) {
			$this->error['login_attempts'] = $this->language->get('error_login_attempts');
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}
}