<?php
namespace Controller\Extension\AccountLogin;

class SocialYA extends \Controller {
	public function index() {
		if (!$this->config->get('account_login_social_ya_status') || !$this->config->get('account_login_social_ya_api_id') || empty($this->request->get['code']) || empty($this->request->get['state']) || empty($this->session->data['social_ya_state']) || (string)$this->request->get['state'] != (string)$this->session->data['social_ya_state'])
			return new \Action('error/not_found');

		$code = $this->request->get['code'];

		$api_id = $this->config->get('account_login_social_ya_api_id');
		$user_id = $this->config->get('account_login_social_ya_user_id');
		$secret_key = $this->config->get('account_login_social_ya_secret_key');

		$client = new \GuzzleHttp\Client([
			'defaults' => [
				'connect_timeout' => 10
			],
		]);

		try {
			$response = $client->post('https://oauth.yandex.ru/token', array(
				'auth' => array($api_id, $secret_key),
				'headers' => array(
					'Content-type' => 'application/x-www-form-urlencoded'
				),
				'body' => array(
					'grant_type' => 'authorization_code',
					'code' => $code
				)
			));

			$result = $response->json();
		} catch(Exception $e) {
			$this->log->write($e->getMessage());
		}

		if (empty($result))
			return new \Action('error/not_found');

		$access_token = $result['access_token'];

		if (!isset($result['error'])) {
			try {
				$response = $client->get('https://login.yandex.ru/info', array(
					'headers' => array(
						'Authorization' => 'OAuth ' . $access_token
					)
				));

				$result = $response->json();
			} catch(Exception $e) {
				$this->log->write($e->getMessage());
			}
		}

		if (!isset($result['error'])) {
			$this->session->data['social_ya'] = $result;
			$this->session->data['social_ya']['access_token'] = $access_token;

			$this->load->model('account/account_auth');

			$this->load->language('account/login');

			$error = '';

			$login = $result['client_id'];

			if (!empty($this->session->data['social_ya_account_id']) && $this->session->data['social_ya_account_id'] == $this->account->getId()) {
				if ($this->model_account_account_auth->excludeAccountAuthLogin($login, 'social_ya')) {
					$error = $this->language->get('error_add');
				} elseif (!$this->model_account_account_auth->getAccountAuthLogin('social_ya')) {
					$account_auth_id = $this->model_account_account_auth->addAccountAuth(array(
						'account_id' => $this->account->getId(),
						'type' => 'social_ya',
						'login' => $login,
						'password' => '',
						'status' => 1
					));
				}
			} else {
				$this->account->logout();

				$account_auth = $this->model_account_account_auth->getAccountAuthByData(array(
					'type' => 'social_ya',
					'login' => $login,
					'status' => 1
				));

				if (!$account_auth || !$this->account->login('social_ya', $account_auth['account_auth_id'], $account_auth['account_id'], '', true)) {
					$this->load->model('account/account');
					$this->load->model('core/custom_field');

					$first_name = isset($result['first_name']) ? $result['first_name'] : '';
					$last_name = isset($result['last_name']) ? $result['last_name'] : '';

					$photo = !empty($result['is_avatar_empty']) && isset($result['default_avatar_id']) ? $this->load->controller('account/edit/upload_url', $result['default_avatar_id'], 'ya') : '';

					$data = array(
						'name' => $result['real_name'],
						'custom_field' => array()
					);

					$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('account_contact');

					foreach ($custom_fields as $custom_field) {
						$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

						switch ($custom_field['code']) {
							case 'firstname':
								$data['custom_field'][$custom_field['custom_field_id']][$language_id] = $first_name;
								break;
							case 'lastname':
								$data['custom_field'][$custom_field['custom_field_id']][$language_id] = $last_name;
								break;
							case 'image':
								$data['custom_field'][$custom_field['custom_field_id']][$language_id] = $photo;
								break;
						}
					}

					$account_id = $this->model_account_account->addAccount($data);

					$account_auth_id = $this->model_account_account_auth->addAccountAuth(array(
						'account_id' => $account_id,
						'type' => 'social_ya',
						'login' => $login,
						'password' => '',
						'status' => 1
					));

					$this->account->login('social_ya', $account_auth_id, $account_id, '', true);
				}
			}

			$script = '<script>';

			if ($error) {
				$script .= 'window.opener.dispatchEvent(new CustomEvent(\'success\', {detail: {\'error\': \'' . $error . '\' }}));';
			} else {
				$script .= 'window.opener.dispatchEvent(new Event(\'success\'));';
			}

			$script .= 'window.close()';

			$script .= '</script>';

			$this->response->setOutput($script);

			return;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($result);
	}

	public function form() {
		if (!$this->config->get('account_login_social_ya_status') || !$this->config->get('account_login_social_ya_api_id'))
			return '';

		$token = token(16);

		$this->session->data['social_ya_state'] = $token;
		$this->session->data['social_ya_account_id'] = $this->account->getId();

		$params = array(
			'client_id'     => $this->config->get('account_login_social_ya_api_id'),
			'display'       => 'popup',
			'redirect_uri'  => $this->url->link('extension/account_login/social_ya'),
			'response_type' => 'code',
			'state'         => $token,
			'page'          => 'popup',
		);

		$link = 'https://oauth.yandex.ru/authorize?' . http_build_query($params);

		$this->response->redirect($link, 302);
	}
}