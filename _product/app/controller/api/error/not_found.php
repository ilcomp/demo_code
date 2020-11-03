<?php
namespace Controller\Error;

class NotFound extends \Controller {
	public function index($data = array()) {
		$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

		$data['error'] = '404 Not Found';

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($data);
	}
}