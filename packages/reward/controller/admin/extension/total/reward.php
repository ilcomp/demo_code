<?php
namespace Controller\Extension\Total;

class Reward extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/total/reward');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_core_setting->editSetting('total_reward', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('order/extension/total'));
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
			'href' => $this->url->link('order/extension/total')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/total/reward')
		);

		$data['action'] = $this->url->link('extension/total/reward');

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('order/extension/total');

		if (isset($this->request->post['total_reward_status'])) {
			$data['total_reward_status'] = (int)$this->request->post['total_reward_status'];
		} else {
			$data['total_reward_status'] = (int)$this->config->get('total_reward_status');
		}

		if (isset($this->request->post['total_reward_sort_order'])) {
			$data['total_reward_sort_order'] = (int)$this->request->post['total_reward_sort_order'];
		} else {
			$data['total_reward_sort_order'] = (int)$this->config->get('total_reward_sort_order');
		}

		if (isset($this->request->post['total_reward_minimum'])) {
			$data['total_reward_minimum'] = (int)$this->request->post['total_reward_minimum'];
		} else {
			$data['total_reward_minimum'] = (int)$this->config->get('total_reward_minimum');
		}

		if (isset($this->request->post['total_reward_percent'])) {
			$data['total_reward_percent'] = (float)$this->request->post['total_reward_percent'];
		} else {
			$data['total_reward_percent'] = (float)$this->config->get('total_reward_percent');
		}

		$data['content'] = $this->load->view('extension/total/reward', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	public function view($json = array()) {
		$this->load->language('extension/total/reward');

		if (empty($this->request->get['account_id']))
			$json['error'] = $this->language->get('error_permission');


		if (empty($json['error'])) {
			$this->load->model('extension/total/reward');

			$json = $this->language->all();

			$this->load->model('extension/total/reward');

			$json['rewards'] = $this->model_extension_total_reward->getAccountRewards(array('filter_account_id' => $this->request->get['account_id'], 'sort' => 'date_added', 'order' => 'DESC'));

			$json['total'] = $this->model_extension_total_reward->getAccountRewardValue($this->request->get['account_id']);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}

	public function add($json = array()) {
		$this->load->language('extension/total/reward');

		if (empty($this->request->post['account_id']))
			$json['error'] = $this->language->get('error_permission');

		if (empty($this->request->post['value']))
			$json['error'] = $this->language->get('error_value');

		if (empty($json['error'])) {
			$this->load->model('extension/total/reward');

			if (empty($this->request->post['comment']))
				$this->request->post['comment'] = '';

			$this->model_extension_total_reward->addAccountReward($this->request->post['account_id'], $this->request->post['value'], $this->request->post['comment']);

			$json['success'] = $this->language->get('success_add');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}

	public function install() {
		$table = new \Model\DBTable($this->registry);
		$table->create('reward');

		$this->load->model('core/event');
		$this->model_core_event->addEvent('reward', 'api/controller/startup/event/after', 'event/reward/startup');
		$this->model_core_event->addEvent('reward', 'admin/controller/startup/event/after', 'event/reward/startup');
		$this->model_core_event->addEvent('reward', 'client/controller/startup/event/after', 'event/reward/startup');
	}

	public function uninstall() {
		$table = new \Model\DBTable($this->registry);
		$table->delete('reward');

		$this->load->model('core/event');
		$this->model_core_event->deleteEventByCode('reward');
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/total/reward')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}