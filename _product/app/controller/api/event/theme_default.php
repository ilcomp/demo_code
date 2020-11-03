<?php
namespace Controller\Event;

class ThemeDefault extends \Controller {
	public function index($data = array()) {
		$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 401 Unauthorized');

		$data['error'] = '401 Unauthorized';

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($data);
	}
}