<?php
namespace Controller\Order;

class Failure extends \Controller {
	public function index() {
		$this->load->language('order/failure');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', 'language=' . $this->config->get('config_language'))
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_basket'),
			'href' => $this->url->link('order/cart', 'language=' . $this->config->get('config_language'))
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_checkout'),
			'href' => $this->url->link('order/checkout', 'language=' . $this->config->get('config_language'))
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_failure'),
			'href' => $this->url->link('order/failure', 'language=' . $this->config->get('config_language'))
		);

		$data['text_message'] = sprintf($this->language->get('text_message'), $this->url->link('common/contact', 'language=' . $this->config->get('config_language')));

		$data['continue'] = $this->url->link('common/home', 'language=' . $this->config->get('config_language'));

		$data['content'] = $this->load->view('common/success', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}
}