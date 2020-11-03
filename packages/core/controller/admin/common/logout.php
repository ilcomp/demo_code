<?php
namespace Controller\Common;

class Logout extends \Controller {
	public function index() {
		$this->user->logout();

		unset($this->session->data['user_token']);

		$this->response->redirect($this->url->link('common/login'));
	}
}