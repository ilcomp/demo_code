<?php
namespace Controller\Common;

class Home extends \Controller {
	public function index($data = array()) {
		$data['link'] = HTTP_APPLICATION;

		$pathes = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(DIR_CONTROLLER));

		foreach ($pathes as $path) {
			if ($path->isFile()) {
				$route = trim(str_replace(array(DIR_CONTROLLER, '.php'), '', $path->getPathname()), '/');

				if (!preg_match('/^(common|error|startup)/', $route)) {
					$result = $this->load->controller($route . '/tree');

					if (is_array($result)) {
						$data[$route] = $result;
					}
				}
			}
		}

		$this->response->setOutput($data);
	}
}