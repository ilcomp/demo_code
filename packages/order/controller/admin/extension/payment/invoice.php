<?php
namespace Controller\Extension\Payment;

class Invoice extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/payment/invoice');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_core_setting->editSetting('payment_invoice', $this->request->post);

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
			'href' => $this->url->link('extension/payment/invoice')
		);

		$data['action'] = $this->url->link('extension/payment/invoice');

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('order/extension/payment');

		if (isset($this->request->post['payment_invoice_status'])) {
			$data['payment_invoice_status'] = $this->request->post['payment_invoice_status'];
		} else {
			$data['payment_invoice_status'] = $this->config->get('payment_invoice_status');
		}

		if (isset($this->request->post['payment_invoice_total'])) {
			$data['payment_invoice_total'] = $this->request->post['payment_invoice_total'];
		} else {
			$data['payment_invoice_total'] = $this->config->get('payment_invoice_total');
		}

		if (isset($this->request->post['payment_invoice_order_status_id'])) {
			$data['payment_invoice_order_status_id'] = $this->request->post['payment_invoice_order_status_id'];
		} else {
			$data['payment_invoice_order_status_id'] = $this->config->get('payment_invoice_order_status_id');
		}

		$this->load->model('localisation/listing');

		$data['order_statuses'] = $this->model_localisation_listing->getListingItems(array('filter_listing_id' => $this->config->get('order_status_listing')));

		if (isset($this->request->post['payment_invoice_geo_zone_id'])) {
			$data['payment_invoice_geo_zone_id'] = $this->request->post['payment_invoice_geo_zone_id'];
		} else {
			$data['payment_invoice_geo_zone_id'] = $this->config->get('payment_invoice_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['payment_invoice_method'])) {
			$data['payment_invoice_method'] = (array)$this->request->post['payment_invoice_method'];
		} else {
			$data['payment_invoice_method'] = (array)$this->config->get('payment_invoice_method');
		}

		if (isset($this->request->post['payment_invoice_sort_order'])) {
			$data['payment_invoice_sort_order'] = $this->request->post['payment_invoice_sort_order'];
		} else {
			$data['payment_invoice_sort_order'] = $this->config->get('payment_invoice_sort_order');
		}

		if (isset($this->request->post['payment_invoice_image'])) {
			$data['payment_invoice_image'] = (array)$this->request->post['payment_invoice_image'];
		} else {
			$data['payment_invoice_image'] = (array)$this->config->get('payment_invoice_image');
		}

		$this->load->model('tool/image');

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		if (is_file(DIR_IMAGE . $data['payment_invoice_image'])) {
			$data['thumb'] = $this->model_tool_image->resize($data['payment_invoice_image'], 100, 100);
		} else {
			$data['thumb'] = $data['placeholder'];
		}

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$data['content'] = $this->load->view('extension/payment/invoice', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/invoice')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function get_methods() {
		$method_data = array();

		$this->load->model('core/extension');

		$results = $this->model_core_extension->getExtensions('payment');

		foreach ($results as $result) {
			if ($this->config->get('payment_' . $result['code'] . '_invoice')) {
				$this->load->model('extension/payment/' . $result['code']);

				$methods = $this->{'model_extension_payment_' . $result['code']}->getMethod($this->session->data['contact'], $total);

				if (isset($methods['code']))
					$methods = array($methods);

				foreach ($methods as $method) {
					if ($method) {
						if (!isset($method['sort_order']))
							$method['sort_order'] = 0;

						$method_data[$method['code']] = $method;
					}
				}
			}
		}

		$sort_order = array();

		foreach ($method_data as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}
	}
}