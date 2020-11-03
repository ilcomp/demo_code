<?php
namespace Controller\Extension\Total;

class Reward extends \Controller {
	public function index() {
		if ($this->config->get('reward_status') && $this->config->get('total_reward_status') && $this->account->isLogged()) {
			$this->load->model('extension/system/reward');

			$data['min'] = (int)$this->config->get('total_reward_minimum');
			$data['max'] = (int)$this->model_extension_system_reward->getAccountRewardValue($this->account->getId());
			$total = $this->cart->getSubTotal();

			if ((int)$data['max'] > (int)$total)
				$data['max'] = (int)$total;

			if ((int)$data['max'] && (int)$data['min'] <= (int)$data['max']) {
				$this->load->language('extension/total/reward');

				if (isset($this->session->data['reward_status'])) {
					$data['reward_status'] = $this->session->data['reward_status'];
				} else {
					$data['reward_status'] = '';
				}

				if (isset($this->session->data['reward_value'])) {
					$data['reward_value'] = $this->session->data['reward_value'];
				} else {
					$data['reward_value'] = $data['min'];
				}

				$data['action'] = $this->url->link('api/extension/total/reward/add', 'api_token=' . $this->request->get['api_token']);

				return $this->load->view('extension/total/reward', $data);
			}
		} else {
			unset($this->session->data['reward_status']);
			unset($this->session->data['reward_value']);
		}
	}

	public function add() {
		if (!$this->config->get('reward_status') || !$this->config->get('total_reward_status') || !$this->account->isLogged())
			new Action('error/not_found');

		$this->load->model('extension/system/reward');

		$data['min'] = (int)$this->config->get('total_reward_minimum');
		$data['max'] = (int)$this->model_extension_system_reward->getAccountRewardValue($this->account->getId());
		$total = $this->cart->getSubTotal();

		if ((int)$data['max'] > (int)$total)
			$data['max'] = (int)$total;

		if (!((int)$data['max'] && (int)$data['min'] <= (int)$data['max']))
			new Action('error/not_found');

		$this->load->language('extension/total/reward');

		$json = array();

		$this->load->model('extension/total/reward');

		unset($this->session->data['reward_status']);
		unset($this->session->data['reward_value']);

		if (isset($this->request->post['reward_status']))
			$this->session->data['reward_status'] = $this->request->post['reward_status'];

		if (isset($this->request->post['reward_value']))
			$this->session->data['reward_value'] = $this->request->post['reward_value'];

		$json['success'] = $this->language->get('text_success');

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}