<?php
namespace Controller\Startup;

class Permission extends \Controller {
	public function index() {
		if (isset($this->request->get['route'])) {
			$route = '';

			$part = explode('/', $this->request->get['route']);

			if (isset($part[0])) {
				$route .= $part[0];
			}

			if (isset($part[1])) {
				$route .= '/' . $part[1];
			}

			if (isset($part[2]) && (preg_match('/^extension\/.+/', $route) || preg_match('/^.+\/extension/', $route))) {
				$route .= '/' . $part[2];
			}

			// We want to ingore some pages from having its permission checked.
			$ignore = array(
				'common/cron',
				'common/dashboard',
				'common/filemanager',
				'common/file',
				'common/forgotten',
				'common/login',
				'common/logout',
				'common/reset',
				'error/not_found',
				'error/permission',
				'tool/image'
			);

			if (!in_array($route, $ignore) && !$this->user->hasPermission('access', $route)) {
				return new \Action('error/permission');
			}
		}
	}
}
