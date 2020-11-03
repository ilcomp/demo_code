<?php
namespace Controller\Error;

class BadRequest extends \Controller {
	public function index($data = array()) {
		$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 400 Bad Request');

		$data['error'] = '400 Bad Request';

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($data);
	}
}