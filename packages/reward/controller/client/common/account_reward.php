<?php
namespace Controller\Common;

class AccountReward extends \Controller {
	public function index() {
		$this->load->model('extension/total/reward');

		$data['reward_total'] = (int)$this->model_extension_total_reward->getAccountRewardValue($this->account->getId());

		if ($this->request->server['HTTP_ACCEPT'] == 'application/json') {
			return $data;
		} else {
			return $this->load->view('common/account_reward', $data);
		}
	}

	public function info() {
		if ($this->request->server['HTTP_ACCEPT'] == 'application/json') {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($this->index()));
		} else {
			$this->response->setOutput($this->index());
		}
	}
}