<?php
namespace Controller\Common;

class Home extends \Controller {
	public function index() {
		$this->load->language('common/home');

		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->setDescription($this->language->get('heading_description'));

		$data['content'] = $this->load->view('common/home', $data = array());

		$this->response->setOutput($this->load->controller('common/template', $data));
	}
}
