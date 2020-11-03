<?php
namespace Controller\Startup;

class Router extends \Controller {
	public function index() {
		// Form method
		if (isset($this->request->post['form_method']) && strpos((string)$this->request->post['form_method'], 'startup/') !== 0) {
			if ($this->request->server['HTTP_ACCEPT'] == 'application/json') {
				return $this->load->controller($this->request->post['form_method']);
			} else {
				$data = (array)$this->load->controller($this->request->post['form_method']);
			}
		} else {
			$data = array();
		}

		// Route
		if (isset($this->request->get['route']) && strpos((string)$this->request->get['route'], 'startup/') !== 0) {
			$route = (string)$this->request->get['route'];
		} else {
			$route = $this->config->get('action_default');

			$this->request->get['route'] = $route;
		}

		// Sanitize the call
		$route = preg_replace('/[^a-zA-Z0-9_\/]/', '', $route);

		// Trigger the pre events
		$result = $this->event->trigger('controller/' . $route . '/before', array(&$route, &$data));

		if (!is_null($result)) {
			return $result;
		}

		// We dont want to use the loader class as it would make an controller callable.
		$action = new \Action($route);

		// Any output needs to be another Action object.
		$output = $action->execute($this->registry, $data); 

		// Trigger the post events
		$result = $this->event->trigger('controller/' . $route . '/after', array(&$route, &$data, &$output));

		if (!is_null($result)) {
			return $result;
		}

		return $output;
	}
}
