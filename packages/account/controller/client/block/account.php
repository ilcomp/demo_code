<?php
namespace Controller\Block;

class Account extends \Controller {
	public function index() {
		$data['logged'] = $this->account->isLogged();

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($data);
	}

	public function modal() {
		$this->load->language('block/account');

		// Totals
		$data['form_login'] = $this->load->controller('account/login/form');
		$data['form_forgotten'] = $this->load->controller('account/forgotten/form');
		$data['form_register'] = $this->load->controller('account/register/form');

		$this->response->setOutput($this->load->view('block/account_modal', $data));
	}
}