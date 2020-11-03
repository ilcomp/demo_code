<?php
namespace Controller\Extension\Total;

class Total extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/total/total');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_core_setting->editSetting('total_total', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('order/extension/total'));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('order/extension/total')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/total/total')
		);

		$data['action'] = $this->url->link('extension/total/total');

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('order/extension/total');

		if (isset($this->request->post['total_total_status'])) {
			$data['total_total_status'] = $this->request->post['total_total_status'];
		} else {
			$data['total_total_status'] = $this->config->get('total_total_status');
		}

		if (isset($this->request->post['total_total_sort_order'])) {
			$data['total_total_sort_order'] = $this->request->post['total_total_sort_order'];
		} else {
			$data['total_total_sort_order'] = $this->config->get('total_total_sort_order');
		}

		$data['content'] = $this->load->view('extension/total/total', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/total/total')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}