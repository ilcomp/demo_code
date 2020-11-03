<?php
namespace Controller\Error;

class Permission extends \Controller {
	public function index() {
		$this->load->language('error/permission');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->request->get['route'])
		);

		$data['content'] = $this->load->view('error/permission', $data);

		$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 403 Forbidden');
		$this->response->setOutput($this->load->controller('common/template', $data));
	}
}
