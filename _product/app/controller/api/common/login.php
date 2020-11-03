<?php
namespace Controller\Common;

class Login extends \Controller {
	public function index() {
		$this->load->language('api/login');

		$json = array();

		$this->load->model('core/api');

		// Login with API Key
		$api_info = $this->model_core_api->login($this->request->post['username'], $this->request->post['key']);

		if ($api_info) {
			// Check if IP is allowed
			$ip_data = $this->model_core_api->getApiIps($api_info['api_id']);

			if (!in_array($this->request->server['REMOTE_ADDR'], $ip_data)) {
				$json['error']['ip'] = sprintf($this->language->get('error_ip'), $this->request->server['REMOTE_ADDR']);
			}

			if (!$json) {
				$json['success'] = $this->language->get('text_success');

				$session = new \Session($this->config->get('session_engine'), $this->registry);
				$session->start();

				$this->model_core_api->addApiSession($api_info['api_id'], $session->getId(), $this->request->server['REMOTE_ADDR']);

				$session->data['api_id'] = $api_info['api_id'];

				// Create Token
				$json['access_token'] = $session->getId();
				$json['expires_in'] = 3600;
			} else {
				$json['error']['key'] = $this->language->get('error_key');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}
}
