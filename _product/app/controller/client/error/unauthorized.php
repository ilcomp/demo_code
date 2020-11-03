<?php
namespace Controller\Error;

class Unauthorized extends \Controller {
	public function index($data = array()) {
		$this->load->language('error/unauthorized');

		$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 401 Unauthorized');

		$data['continue'] = $this->url->link('account/login');

		$data['error'] = $this->config->get('error') ? $this->config->get('error') : $this->language->get('heading_title');

		if ($this->request->server['HTTP_ACCEPT'] == 'application/json') {
			$data['error'] = $this->language->get('heading_title');

			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput($data);
		} else {
			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home')
			);

			if (isset($this->request->get['route'])) {
				$url = $this->request->get;
				unset($url['_route_']);
				unset($url['route']);

				$data['breadcrumbs'][] = array(
					'text' => $this->language->get('heading_title'),
					'href' => $this->url->link($this->request->get['route'], $url)
				);
			}

			$data['content'] = $this->load->view('error/unauthorized', $data);

			if ($this->request->server['HTTP_ACCEPT'] == 'text/html') {
				$this->response->setOutput($data['content']);
			} else {
				$this->document->setTitle($this->language->get('heading_title'));

				$this->response->setOutput($this->load->controller('common/template', $data));
			}
		}
	}
}