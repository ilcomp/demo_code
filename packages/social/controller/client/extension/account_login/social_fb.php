<?php
namespace Controller\Extension\AccountLogin;

class SocialFB extends \Controller {
	private $ver = 'v5.0';

	public function index() {
		if (!$this->config->get('account_login_social_fb_status') || !$this->config->get('account_login_social_fb_api_id') || empty($this->request->get['code']) || empty($this->request->get['state']) || empty($this->session->data['social_fb_state']) || (string)$this->request->get['state'] != (string)$this->session->data['social_fb_state'])
			return new \Action('error/not_found');

		$code = $this->request->get['code'];

		$api_id = $this->config->get('account_login_social_fb_api_id');
		$user_id = $this->config->get('account_login_social_fb_user_id');
		$secret_key = $this->config->get('account_login_social_fb_secret_key');

		$client = new \GuzzleHttp\Client([
			'defaults' => [
				'connect_timeout' => 10,
				'exceptions' => false
			]
		]);

		try {
			$response = $client->get('https://graph.facebook.com/' . $this->ver . '/oauth/access_token', array(
				'query' => array(
					'client_id' => $api_id,
					'client_secret' => $secret_key,
					'redirect_uri' => $this->url->link('extension/account_login/social_fb'),
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
		$expires_in = isset($result['expires_in']) ? (int)$result['expires_in'] : 0;

		if (!isset($result['error'])) {
			try {
				$response = $client->get('https://graph.facebook.com/' . $this->ver . '/debug_token', array(
					'query' => array(
						'input_token' => $access_token,
						'access_token' => $access_token
					)
				));

				$result = $response->json();
			} catch(Exception $e) {
				$this->log->write($e->getMessage());
			}
		}

		if (!isset($result['error'])) {
			$this->session->data['social_fb'] = $result;
			$this->session->data['social_fb']['access_token'] = $access_token;

			$this->load->model('account/account_auth');

			$this->load->language('account/login');

			$error = '';

			$login = $result['data']['user_id'];

			if (!empty($this->session->data['social_fb_account_id']) && $this->session->data['social_fb_account_id'] == $this->account->getId()) {
				if ($this->model_account_account_auth->excludeAccountAuthLogin($login, 'social_fb')) {
					$error = $this->language->get('error_add');
				} elseif (!$this->model_account_account_auth->getAccountAuthLogin('social_fb')) {
					$account_auth_id = $this->model_account_account_auth->addAccountAuth(array(
						'account_id' => $this->account->getId(),
						'type' => 'social_fb',
						'login' => $login,
						'token' => $access_token,
						'status' => 1,
						'expires_in' => $expires_in
					));
				}
			} else {
				$this->account->logout();

				$account_auth = $this->model_account_account_auth->getAccountAuthByData(array(
					'type' => 'social_fb',
					'login' => $login,
					'status' => 1
				));

				if (!$account_auth || !$this->account->login('social_fb', $account_auth['account_auth_id'], $account_auth['account_id'], '', true)) {
					$this->load->model('account/account');
					$this->load->model('core/custom_field');

					try {
						$response = $client->get('https://graph.facebook.com/' . $this->ver . '/' . $result['data']['user_id'], array(
							'query' => array(
								'access_token' => $access_token
							)
						));

						$result = $response->json();
					} catch(Exception $e) {
						$this->log->write($e->getMessage());
					}

					if (!isset($result['name'])) {
						$error = $this->language->get('error_permission');
					}

					if (empty($error)) {
						list($first_name, $last_name) = explode(' ', $result['name']);

						$data = array(
							'name' => $result['name'],
							'custom_field' => array()
						);

						$photo = $this->load->controller('account/edit/upload_url', 'https://graph.facebook.com/' . $this->ver . '/' . $login . '/picture?access_token=' . $access_token . '&type=normal', 'fb');

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
							'type' => 'social_fb',
							'login' => $login,
							'token' => $access_token,
							'status' => 1,
							'expires_in' => $expires_in
						));

						$this->account->login('social_fb', $account_auth_id, $account_id, '', true);
					}
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
		if (!$this->config->get('account_login_social_fb_status') || !$this->config->get('account_login_social_fb_api_id'))
			return '';

		$token = token(16);

		$this->session->data['social_fb_state'] = $token;
		$this->session->data['social_fb_account_id'] = $this->account->getId();

		$params = array(
			'client_id'     => $this->config->get('account_login_social_fb_api_id'),
			'display'       => 'popup',
			'redirect_uri'  => $this->url->link('extension/account_login/social_fb'),
			'response_type' => 'code',
			'state'         => $token
		);

		$link = 'https://www.facebook.com/' . $this->ver . '/dialog/oauth?' . http_build_query($params);

		$this->response->redirect($link, 302);
	}
}