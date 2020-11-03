<?php
namespace Controller\Error;

class Permission extends \Controller {
	public function index($data = array()) {
		$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 403 Forbidden');

		$data['error'] = '403 Forbidden';

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($data);
	}
}