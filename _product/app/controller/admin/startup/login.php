<?php
namespace Controller\Startup;

class Login extends \Controller {
	public function index() {
		$this->url->addRewrite($this);

		$route = isset($this->request->get['route']) ? $this->request->get['route'] : '';

		$ignore = array(
			'common/login',
			'common/forgotten',
			'common/reset',
			'common/cron'
		);

		if (isset($this->session->data['replace_token']) && isset($this->request->get['user_token']) && isset($this->session->data['replace_token'][$this->request->get['user_token']])) {
			$this->request->get['user_token'] = $this->session->data['replace_token'][$this->request->get['user_token']];
		}

		// User
		$this->registry->set('user', new \Model\Registry\User($this->registry));

		// if ($this->user->isLogged() && !$this->user->hasPermission('access', 'startup/login')) {
		// 	$this->user->logout();

		// 	$this->response->redirect($this->url->link($route));
		// }

		if (!$this->user->isLogged() && !in_array($route, $ignore)) {
			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 401 Unauthorized');

			return new \Action('common/login');
		}

		if (isset($this->request->get['route'])) {
			$ignore = array(
				'common/login',
				'common/logout',
				'common/forgotten',
				'common/reset',
				'common/cron',
				'error/not_found',
				'error/permission'
			);

			if (!in_array($route, $ignore) && (!isset($this->request->get['user_token']) || !isset($this->session->data['user_token']) || ($this->request->get['user_token'] != $this->session->data['user_token']))) {
				$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 401 Unauthorized');

				return new \Action('common/login');
			}
		} else {
			if (!isset($this->request->get['user_token']) || !isset($this->session->data['user_token']) || ($this->request->get['user_token'] != $this->session->data['user_token'])) {
				$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 401 Unauthorized');

				return new \Action('common/login');
			}
		}
	}

	public function rewrite($link) {
		if (isset($this->session->data['user_token']) && strpos($link, 'user_token=' . $this->session->data['user_token']) === false) {
			$link .= strpos($link, '?') ? ('&user_token=' . $this->session->data['user_token']) : ('?user_token=' . $this->session->data['user_token']);
		}

		return $link;
	}
}