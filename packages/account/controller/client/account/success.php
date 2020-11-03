<?php
namespace Controller\Account;

class Success extends \Controller {
	public function index() {
		$this->load->language('account/success');

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
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('common/success')
		);

		if ($this->account->isLogged()) {
			$data['text_message'] = sprintf($this->language->get('text_success'), $this->url->link('information/contact'));
		} else {
			$data['text_message'] = sprintf($this->language->get('text_approval'), $this->config->get('config_name'), $this->url->link('information/contact'));
		}

		$data['continue'] = $this->url->link('account/account');

		$data['content'] = $this->load->view('common/success', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	public function modal() {
		$this->load->language('account/success');

		if ($this->account->isLogged()) {
			$data['text_message'] = sprintf($this->language->get('text_success'), $this->url->link('information/contact'));
		} else {
			$data['text_message'] = sprintf($this->language->get('text_approval'), $this->config->get('config_name'), $this->url->link('information/contact'));
		}

		$data['continue'] = $this->url->link('account/account');

		return $this->load->view('account/modal_success', $data);
	}
}