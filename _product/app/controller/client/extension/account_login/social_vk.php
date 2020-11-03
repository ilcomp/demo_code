<?php
namespace Controller\Extension\AccountLogin;

class SocialVK extends \Controller {
	private $ver = '5.101';

	public function index() {
		if (!$this->config->get('account_login_social_vk_status') || !$this->config->get('account_login_social_vk_api_id') || empty($this->request->get['code']) || empty($this->request->get['state']) || empty($this->session->data['social_vk_state']) || (string)$this->request->get['state'] != (string)$this->session->data['social_vk_state'])
			return new \Action('error/not_found');

		$code = $this->request->get['code'];

		$api_id = $this->config->get('account_login_social_vk_api_id');
		$user_id = $this->config->get('account_login_social_vk_user_id');
		$secret_key = $this->config->get('account_login_social_vk_secret_key');

		$client = new \GuzzleHttp\Client([
			'defaults' => [
				'connect_timeout' => 10
			],
		]);

		try {
			$response = $client->get('https://oauth.vk.com/access_token', array(
				'query' => array(
					'client_id' => $api_id,
					'client_secret' => $secret_key,
					'redirect_uri' => $this->url->link('extension/account_login/social_vk'),
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
			$this->session->data['social_vk'] = $result;
			$this->session->data['social_vk']['access_token'] = $access_token;

			$this->load->model('account/account_auth');

			$this->load->language('account/login');

			$error = '';

			$login = $result['user_id'];

			if (!empty($this->session->data['social_vk_account_id']) && $this->session->data['social_vk_account_id'] == $this->account->getId()) {
				if ($this->model_account_account_auth->excludeAccountAuthLogin($login, 'social_vk')) {
					$error = $this->language->get('error_add');
				} elseif (!$this->model_account_account_auth->getAccountAuthLogin('social_vk')) {
					$account_auth_id = $this->model_account_account_auth->addAccountAuth(array(
						'account_id' => $this->account->getId(),
						'type' => 'social_vk',
						'login' => $login,
						'password' => '',
						'status' => 1
					));
				}
			} else {
				$this->account->logout();

				$account_auth = $this->model_account_account_auth->getAccountAuthByData(array(
					'type' => 'social_vk',
					'login' => $login,
					'status' => 1
				));

				if (!$account_auth || !$this->account->login('social_vk', $account_auth['account_auth_id'], $account_auth['account_id'], '', true)) {
					$this->load->model('account/account');
					$this->load->model('core/custom_field');

					try {
						$response = $client->get('https://api.vk.com/method/users.get', array(
							'query' => array(
								'user_ids' => [$result['user_id']],
								'fields' => 'photo_max_orig',
								'access_token' => $access_token,
								'v' => $this->ver
							)
						));

						$result = $response->json();
					} catch(Exception $e) {
						$this->log->write($e->getMessage());
					}

					if (!isset($result['response'])) {
						$error = $this->language->get('error_permission');
					}

					if (empty($error)) {
						$account = array_pop($result['response']);

						if (empty($account['is_closed'])) {
							$first_name = isset($account['first_name']) ? $account['first_name'] : '';
							$last_name = isset($account['last_name']) ? $account['last_name'] : '';

							$photo = isset($account['photo_max_orig']) ? $this->load->controller('account/edit/upload_url', $account['photo_max_orig'], 'vk') : '';

							$name = array();

							if ($first_name) $name[] = $first_name;
							if ($last_name) $name[] = $last_name;

							$data = array(
								'name' => implode(' ', $name),
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
								'type' => 'social_vk',
								'login' => $login,
								'password' => '',
								'status' => 1
							));

							$this->account->login('social_vk', $account_auth_id, $account_id, '', true);
						} else {
							$error = $this->language->get('error_permission');
						}
					}
				}
			}

			$script = '<script type="text/javascript">';

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
		if (!$this->config->get('account_login_social_vk_status') || !$this->config->get('account_login_social_vk_api_id'))
			return '';

		$token = token(16);

		$this->session->data['social_vk_state'] = $token;
		$this->session->data['social_vk_account_id'] = $this->account->getId();

		$params = array(
			'client_id'     => $this->config->get('account_login_social_vk_api_id'),
			'display'       => 'popup',
			'redirect_uri'  => $this->url->link('extension/account_login/social_vk'),
			'response_type' => 'code',
			'state'         => $token,
			'v'             => $this->ver
		);

		$link = 'https://oauth.vk.com/authorize?' . http_build_query($params);

		$this->response->redirect($link, 302);
	}
}