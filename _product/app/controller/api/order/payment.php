<?php
namespace Controller\Order;

class Payment extends \Controller {
	public function methods() {
		$this->load->language('api/payment');
		
		// Delete past shipping methods and method just in case there is an error
		unset($this->session->data['payment_methods']);
		unset($this->session->data['payment_method']);

		$json = array();

		if (!isset($this->session->data['contact']))
			$this->session->data['contact'] = array();

		// Totals
		$totals = array();
		$taxes = $this->cart->getTaxes();
		$total = 0;

		// Because __call can not keep var references so we put them into an array. 
		$total_data = array(
			'totals' => &$totals,
			'taxes'  => &$taxes,
			'total'  => &$total
		);

		$this->load->model('core/extension');

		$sort_order = array();

		$results = $this->model_core_extension->getExtensions('total');

		foreach ($results as $key => $value) {
			$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
		}

		array_multisort($sort_order, SORT_ASC, $results);

		foreach ($results as $result) {
			if ($this->config->get('total_' . $result['code'] . '_status')) {
				$this->load->model('extension/total/' . $result['code']);
				
				// We have to put the totals in an array so that they pass by reference.
				$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
			}
		}

		// Payment Methods
		$method_data = array();

		$this->load->model('core/extension');
		$this->load->model('tool/image');

		$results = $this->model_core_extension->getExtensions('payment');

		$recurring = $this->cart->hasRecurringProducts();

		foreach ($results as $result) {
			if ($this->config->get('payment_' . $result['code'] . '_status')) {
				$this->load->model('extension/payment/' . $result['code']);

				$methods = $this->{'model_extension_payment_' . $result['code']}->getMethod($this->session->data['contact'], $total);

				if (isset($methods['code']))
					$methods = array($methods);

				foreach ($methods as $method) {
					if ($method) {
						$method['image'] = isset($method['image']) ? $this->model_tool_image->resize($method['image'], 108, 76) : '';

						if ($recurring) {
							if (property_exists($this->{'model_extension_payment_' . $result['code']}, 'recurringPayments') && $this->{'model_extension_payment_' . $result['code']}->recurringPayments()) {
								$method_data[$method['code']] = $method;
							}
						} else {
							$method_data[$method['code']] = $method;
						}
					}
				}
			}
		}

		$sort_order = array();

		foreach ($method_data as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC, $method_data);

		if ($method_data) {
			if (!isset($this->request->post['payment_method'])) {
				$json['error'] = $this->language->get('error_method');
			} elseif (!isset($method_data[$this->request->post['payment_method']])) {
				$json['error'] = $this->language->get('error_method');
			}

			if (!isset($json['error'])) {
				$this->session->data['payment_method'] = $method_data[$this->request->post['payment_method']];

				$json['success'] = $this->language->get('text_method');
			}

			$this->session->data['payment_methods'] = $method_data;
		} else {
			$json['error'] = $this->language->get('error_no_payment');
		}

		$json['payment_methods'] = isset($this->session->data['payment_methods']) ? $this->session->data['payment_methods'] : array();

		$json['payment_method'] = isset($this->session->data['payment_method']) ? $this->session->data['payment_method'] : '';

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}
}
