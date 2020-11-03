<?php
namespace Controller\Account;

class Logout extends \Controller {
	public function index() {
		if ($this->account->isLogged()) {
			$this->account->logout();

			$this->response->redirect($this->url->link($this->config->get('action_default')));
		}

		$this->load->language('account/logout');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_logout'),
			'href' => $this->url->link('account/logout')
		);

		$data['continue'] = $this->url->link('common/home');

		$data['content'] = $this->load->view('common/success', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}
}
