<?php
namespace Controller\Error;

class Maintenance extends \Controller {
	public function index() {
		$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

		$this->response->setOutput('<html><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL ' . $this->request->server['REQUEST_URI'] . ' was not found on this server.</p></body></html>');
	}
}