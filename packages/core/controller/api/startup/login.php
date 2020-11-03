<?php
namespace Controller\Startup;

class Login extends \Controller {
	public function index() {
		if (isset($this->request->server["REMOTE_USER"])) {
			$auth = $this->request->server["REMOTE_USER"];
		} elseif (isset($this->request->server["HTTP_AUTHORIZATION"])) {
			$auth = $this->request->server["HTTP_AUTHORIZATION"];
		} elseif (isset($this->request->server["REDIRECT_REMOTE_USER"])) {
			$auth = $this->request->server["REDIRECT_REMOTE_USER"];
		} elseif (isset($this->request->server["REDIRECT_HTTP_AUTHORIZATION"])) {
			$auth = $this->request->server["REDIRECT_HTTP_AUTHORIZATION"];
		}

		if (isset($auth)) {
			if ($this->config->get('commerceml2_allow_ip')) {
				$allow_ips = explode("\r\n", $this->config->get('commerceml2_allow_ip'));

				if (!in_array($this->request->server['REMOTE_ADDR'], $allow_ips)){
					$this->log->write(json_encode($this->request->server['REMOTE_ADDR']));
					return new Action('error/not_found');
				}
			}

			if (preg_match('/^(\w+) (.*)$/', $auth, $matche)) {
				switch ($matche[1]) {
					case 'Basic':
						list($login, $password) = explode(':', base64_decode($matche[2]));

						$this->request->post['username'] = $login;
						$this->request->post['key'] = $password;

						break;
					case 'Bearer':
					case 'OAuth':
					case 'Token':
						$token = $matche[2];

						$this->load->model('core/api');

						$this->model_core_api->cleanApiSessions();

						$api_info = $this->model_core_api->getApiByToken($token);

						if ($api_info) {
							$this->session->start($token);

							$this->model_core_api->updateApiSession($api_info['api_session_id']);
						} else {
							return new \Action('error/login');
						}

						break;
					default:
						return new \Action('error/login');

						break;
				}
			} else {
				return new \Action('error/login');
			}
		}
		// $route = isset($this->request->get['route']) ? $this->request->get['route'] : '';

		// $ignore = array(
		// 	'common/login',
		// 	'common/cron',
		// 	'error/login'
		// );

		// if (isset($this->session->data['refresh_token']) && isset($this->request->get['access_token']) && isset($this->session->data['refresh_token'][$this->request->get['access_token']])) {
		// 	$this->request->get['access_token'] = $this->session->data['refresh_token'][$this->request->get['access_token']];
		// }

		// // User
		// $this->registry->set('application', new \Model\Registry\Application($this->registry));

		// if ($this->application->isLogged() && !$this->permission('access', 'startup/login')) {
		// 	$this->application->logout();

		// 	return new \Action('error/login');
		// }

		// if (!$this->application->isLogged() && !in_array($route, $ignore)) {
		// 	return new \Action('error/login');
		// }

		// $ignore = array(
		// 	'common/login',
		// 	'common/logout',
		// 	'common/reset',
		// 	'common/cron',
		// 	'error/not_found',
		// 	'error/login'
		// );

		// if (!in_array($route, $ignore) && (!isset($this->request->get['access_token']) || !isset($this->session->data['access_token']) || ($this->request->get['access_token'] != $this->session->data['access_token']))) {
		// 	return new \Action('error/login');
		// }
	}
}
