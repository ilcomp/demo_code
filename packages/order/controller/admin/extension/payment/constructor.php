<?php
namespace Controller\Extension\Payment;

class Constructor extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/payment/constructor');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_core_setting->editSetting('payment_constructor', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('order/extension/payment'));
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
			'href' => $this->url->link('order/extension/payment')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/constructor')
		);

		$data['action'] = $this->url->link('extension/payment/constructor');

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('order/extension/payment');

		if (isset($this->request->post['payment_constructor_total'])) {
			$data['payment_constructor_total'] = $this->request->post['payment_constructor_total'];
		} else {
			$data['payment_constructor_total'] = $this->config->get('payment_constructor_total');
		}

		if (isset($this->request->post['payment_constructor_order_status_id'])) {
			$data['payment_constructor_order_status_id'] = $this->request->post['payment_constructor_order_status_id'];
		} else {
			$data['payment_constructor_order_status_id'] = $this->config->get('payment_constructor_order_status_id');
		}

		$this->load->model('localisation/listing');

		$data['order_statuses'] = $this->model_localisation_listing->getListingItems(array('filter_listing_id' => $this->config->get('order_status_listing')));

		if (isset($this->request->post['payment_constructor_geo_zone_id'])) {
			$data['payment_constructor_geo_zone_id'] = $this->request->post['payment_constructor_geo_zone_id'];
		} else {
			$data['payment_constructor_geo_zone_id'] = $this->config->get('payment_constructor_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['payment_constructor_status'])) {
			$data['payment_constructor_status'] = $this->request->post['payment_constructor_status'];
		} else {
			$data['payment_constructor_status'] = $this->config->get('payment_constructor_status');
		}

		if (isset($this->request->post['payment_constructor_methods'])) {
			$data['payment_constructor_methods'] = (array)$this->request->post['payment_constructor_methods'];
		} else {
			$data['payment_constructor_methods'] = (array)$this->config->get('payment_constructor_methods');
		}

		$this->load->model('tool/image');

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		foreach ($data['payment_constructor_methods'] as &$method) {
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

		if (isset($this->request->post['payment_constructor_sort_order'])) {
			$data['payment_constructor_sort_order'] = $this->request->post['payment_constructor_sort_order'];
		} else {
			$data['payment_constructor_sort_order'] = $this->config->get('payment_constructor_sort_order');
		}

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$data['content'] = $this->load->view('extension/payment/constructor', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/constructor')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}