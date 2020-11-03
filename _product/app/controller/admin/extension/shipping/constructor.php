<?php
namespace Controller\Extension\Shipping;

class Constructor extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/shipping/constructor');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_core_setting->editSetting('shipping_constructor', $this->request->post);

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('order/extension/shipping'));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['error_code'])) {
			$data['error_code'] = $this->error['error_code'];
		} else {
			$data['error_code'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('order/extension/shipping')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/shipping/constructor')
		);

		$data['action'] = $this->url->link('extension/shipping/constructor');

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('order/extension/shipping');

		if (isset($this->request->post['shipping_constructor_methods'])) {
			$data['shipping_constructor_methods'] = (array)$this->request->post['shipping_constructor_methods'];
		} else {
			$data['shipping_constructor_methods'] = (array)$this->config->get('shipping_constructor_methods');
		}

		$this->load->model('tool/image');

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		foreach ($data['shipping_constructor_methods'] as &$method) {
			if (is_file(DIR_IMAGE . html_entity_decode($method['image'], ENT_QUOTES, 'UTF-8'))) {
				$method['image'] = $method['image'];
				$method['thumb'] = $this->model_tool_image->resize(html_entity_decode($method['image'], ENT_QUOTES, 'UTF-8'), 100, 100);
			} else {
				$method['image'] = '';
				$method['thumb'] = $data['placeholder'];
			}
		}
		unset($method);

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['shipping_constructor_status'])) {
			$data['shipping_constructor_status'] = $this->request->post['shipping_constructor_status'];
		} else {
			$data['shipping_constructor_status'] = $this->config->get('shipping_constructor_status');
		}

		if (isset($this->request->post['shipping_constructor_sort_order'])) {
			$data['shipping_constructor_sort_order'] = $this->request->post['shipping_constructor_sort_order'];
		} else {
			$data['shipping_constructor_sort_order'] = $this->config->get('shipping_constructor_sort_order');
		}

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$data['content'] = $this->load->view('extension/shipping/constructor', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/shipping/constructor')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['shipping_constructor_methods'] as $value)
			if (!$value['code'] || preg_match('/^[0-9a-zA-z]$/', $value['code']))
				$this->error['error_code'] = $this->language->get('error_permission');

		return !$this->error;
	}
}