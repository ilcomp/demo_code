<?php
namespace Controller\Startup;

class Permission extends \Controller {
	public function index() {
		// if (isset($this->request->get['route'])) {
		// 	$part = explode('/', $this->request->get['route']);
		// } else {
		// 	$part = explode('/', $this->config->get('action_default'));
		// }

		// $route = '';

		// if (isset($part[0])) {
		// 	$route .= $part[0];
		// }

		// if (isset($part[1])) {
		// 	$route .= '/' . $part[1];
		// }

		// if (isset($part[2]) && preg_match('/^extension\/.+/', $route)) {
		// 	$route .= '/' . $part[2];
		// }

		// // We want to ingore some pages from having its permission checked.
		// $ignore = array(
		// 	'common/cron',
		// 	'common/login',
		// 	//'common/home',
		// 	'error/not_found',
		// 	'error/permission',
		// 	'error/login'
		// );

		// if (!in_array($route, $ignore) && !$this->permission->has('access', $route)) {
		// 	return new \Action('error/not_found');
		// }
	}
}
