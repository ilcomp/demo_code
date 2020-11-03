<?php
namespace Controller\Startup;

class Maintenance extends \Controller {
	public function index($ignore = array('common/language/language')) {
		if ($this->config->get('system_maintenance')) {
			if ($this->config->get('system_maintenance_ips') && !in_array($this->request->server['REMOTE_ADDR'], (array)$this->config->get('system_maintenance_ips'))) {
				return new \Action('error/maintenance');
			}

			if ($this->config->get('system_maintenance_user') {
				$this->user = new \Model\User($this->registry);

				if (!$this->user->isLogged()) {
					return new \Action('common/maintenance');
				}
			}
		}
	}
}
