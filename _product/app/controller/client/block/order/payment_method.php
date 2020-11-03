<?php
namespace Controller\Block\Order;

class PaymentMethod extends \Controller {
	public function index() {
		$data = (array)$this->config->get('block_order_payment_method');

		$this->load->language('order/checkout');

		if (!isset($data['payment_methods']))
			$data['payment_methods'] = $this->get_methods();

		if (empty($data['payment_methods'])) {
			$data['error']['warning'] = sprintf($this->language->get('error_no_payment'), $this->url->link('common/contact'));
		}

		if (isset($this->request->post['payment_method'])) {
			$data['payment_method'] = $this->request->post['payment_method'];
		} elseif (isset($this->session->data['payment_method']['code'])) {
			$data['payment_method'] = $this->session->data['payment_method']['code'];
		} else {
			$data['payment_method'] = '';
		}

		return $this->load->view('block/order/payment_method', $data);
	}

	public function form($data = array()) {
		$this->load->language('order/checkout');

		$data['payment_methods'] = $this->get_methods();

		if (!isset($this->request->post['payment_method']) || !isset($data['payment_methods'][$this->request->post['payment_method']])) {
			$data['error']['warning'] = $this->language->get('error_payment');
		}

		if (empty($data['error'])) {
			$payment = explode('.', $this->request->post['payment_method']);

			$this->session->data['payment_method'] = $data['payment_methods'][$this->request->post['payment_method']];

			if (property_exists($this->{'model_extension_payment_' . $payment[0]}, 'validate')) {
				$data['error'] = $this->{'model_extension_payment_' . $payment[0]}->validate();
			}
		} 

		if (empty($data['error'])) {
			if (isset($this->session->data['payment_method']['title'])) {
				$this->session->data['order']['payment_method'] = $this->session->data['payment_method']['title'];
			} else {
				$this->session->data['order']['payment_method'] = '';
			}

			if (isset($this->session->data['payment_method']['code'])) {
				$this->session->data['order']['payment_code'] = $this->session->data['payment_method']['code'];
			} else {
				$this->session->data['order']['payment_code'] = '';
			}
		} else {
			$this->session->data['order']['validate'] = false;
			unset($this->session->data['order']['payment_method']);
			unset($this->session->data['order']['payment_code']);

			$this->session->data['error'] = $this->language->get('error_form');
		}

		$this->config->set('block_order_payment_method', $data);
	}

	protected function get_methods() {
		if (!isset($this->session->data['contact']))
			$this->session->data['contact'] = array();

		// Totals
		$totals = array();
		$total = 0;

		// Because __call can not keep var references so we put them into an array.
		$total_data = array(
			'totals' => &$totals,
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

		$this->load->model('tool/image');

		$results = $this->model_core_extension->getExtensions('payment');

		$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('order_address');

		$address = array();

		foreach ($custom_fields as $custom_field) {
			$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

			if (isset($this->session->data['address']['custom_field'][$custom_field['custom_field_id']]) && isset($this->session->data['address']['custom_field'][$custom_field['custom_field_id']][$language_id])) {
				$value = $this->session->data['address']['custom_field'][$custom_field['custom_field_id']][$language_id];
			} elseif (isset($custom_field['setting']['default']))
				$value = $custom_field['setting']['default'];
			else
				$value = '';

			$address[$custom_field['code']] = $value;
		}

		foreach ($results as $result) {
			if ($this->config->get('payment_' . $result['code'] . '_status')) {
				$this->load->model('extension/payment/' . $result['code']);

				$methods = $this->{'model_extension_payment_' . $result['code']}->getMethod($address, $total);

				if (isset($methods['code']))
					$methods = array($methods);

				foreach ($methods as $method) {
					if ($method) {
						$method['image'] = isset($method['image']) ? $this->model_tool_image->resize($method['image'], 108, 76) : '';

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

		array_multisort($sort_order, SORT_ASC, $method_data);

		if (isset($this->session->data['payment_method'])) {
			if (!isset($method_data[$this->session->data['payment_method']['code']])) {
				unset($this->session->data['payment_method']);
			}
		}

		return $method_data;
	}
}
