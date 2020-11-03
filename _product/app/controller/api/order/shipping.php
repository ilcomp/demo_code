<?php
namespace Controller\Order;

class Shipping extends \Controller {
	public function methods() {
		$this->load->language('api/shipping');

		// Delete past shipping methods and method just in case there is an error
		unset($this->session->data['shipping_methods']);
		unset($this->session->data['shipping_method']);
		unset($this->session->data['total_shipping']);

		$json = array();

		if (!isset($this->session->data['contact']))
			$this->session->data['contact'] = array();

		// Shipping Methods
		$method_data = array();

		$this->load->model('core/extension');
		$this->load->model('tool/image');

		$results = $this->model_core_extension->getExtensions('shipping');

		foreach ($results as $result) {
			if ($this->config->get('shipping_' . $result['code'] . '_status')) {
				$this->load->model('extension/shipping/' . $result['code']);

				$quote = $this->{'model_extension_shipping_' . $result['code']}->getQuote($this->session->data['contact']);

				if ($quote) {
					$method_data[$result['code']] = array(
						'title'       => $quote['title'],
						'description' => isset($quote['description']) ? $quote['description'] : '',
						'quote'       => $quote['quote'],
						'sort_order'  => $quote['sort_order'],
						'error'       => $quote['error']
					);
				}
			}
		}

		$sort_order = array();

		foreach ($method_data as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC, $method_data);

		if (!empty($method_data)) {
			if (!isset($this->request->post['shipping_method'])) {
				$json['error'] = $this->language->get('error_method');
			} else {
				$shipping = explode('.', $this->request->post['shipping_method']);

				if (!isset($shipping[0]) || !isset($shipping[1]) || !isset($method_data[$shipping[0]]['quote'][$shipping[1]])) {
					$json['error'] = $this->language->get('error_method');
				}
			}

			if (!isset($json['error'])) {
				$this->session->data['shipping_method'] = $method_data[$shipping[0]]['quote'][$shipping[1]];

				$json['success'] = $this->language->get('text_method');
			}

			$this->session->data['shipping_methods'] = $method_data;
		} else {
			$json['error'] = $this->language->get('error_no_shipping');
		}

		$json['shipping_methods'] = isset($this->session->data['shipping_methods']) ? $this->session->data['shipping_methods'] : array();

		$json['shipping_method'] = isset($this->session->data['shipping_method']) ? $this->session->data['shipping_method'] : '';

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}
}
