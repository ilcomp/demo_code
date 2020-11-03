<?php
namespace Controller\Error;

class NotFound extends \Controller {
	public function index($data = array()) {
		$this->load->language('error/not_found');

		$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

		$data['continue'] = $this->url->link('common/home');

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

			$data['content'] = $this->load->view('error/not_found', $data);

			if ($this->request->server['HTTP_ACCEPT'] == 'text/html') {
				$this->response->setOutput($data['content']);
			} else {
				$this->document->setTitle($this->language->get('heading_title'));

				$data['template'] = 'template/default';

				$this->response->setOutput($this->load->controller('common/template', $data));
			}
		}
	}
}