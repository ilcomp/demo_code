<?php
namespace Controller\Extension\Total;

class Shipping extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/total/shipping');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_core_setting->editSetting('total_shipping', $this->request->post);

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
			'href' => $this->url->link('extension/total/shipping')
		);

		$data['action'] = $this->url->link('extension/total/shipping');

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('order/extension/total');

		if (isset($this->request->post['total_shipping_estimator'])) {
			$data['total_shipping_estimator'] = $this->request->post['total_shipping_estimator'];
		} else {
			$data['total_shipping_estimator'] = $this->config->get('total_shipping_estimator');
		}

		if (isset($this->request->post['total_shipping_status'])) {
			$data['total_shipping_status'] = $this->request->post['total_shipping_status'];
		} else {
			$data['total_shipping_status'] = $this->config->get('total_shipping_status');
		}

		if (isset($this->request->post['total_shipping_sort_order'])) {
			$data['total_shipping_sort_order'] = $this->request->post['total_shipping_sort_order'];
		} else {
			$data['total_shipping_sort_order'] = $this->config->get('total_shipping_sort_order');
		}

		$data['content'] = $this->load->view('extension/total/shipping', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/total/shipping')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}