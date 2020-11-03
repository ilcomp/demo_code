<?php
namespace Controller\Error;

class NotFound extends \Controller {
	public function index() {
		$this->load->language('error/not_found');
		
		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('error/not_found')
		);

		$data['content'] = $this->load->view('error/not_found', $data);

		$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');
		$this->response->setOutput($this->load->controller('common/template', $data));
	}
}