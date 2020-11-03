<?php
namespace Controller\Block\Order;

class ShippingMethod extends \Controller {
	public function index() {
		$data = (array)$this->config->get('block_order_shipping_method');

		$this->load->language('order/checkout');

		if (!isset($data['shipping_methods']))
			$data['shipping_methods'] = $this->get_methods();

		if (empty($data['shipping_methods'])) {
			$data['error']['warning'] = sprintf($this->language->get('error_no_shipping'), $this->url->link('common/contact'));
		}

		if (isset($this->request->post['shipping_method'])) {
			$data['shipping_method'] = $this->request->post['shipping_method'];
		} elseif (isset($this->session->data['shipping_method'])) {
			$data['shipping_method'] = $this->session->data['shipping_method']['code'];
		} else {
			$data['shipping_method'] = '';
		}

		return $this->load->view('block/order/shipping_method', $data);
	}

	public function form($data = array()) {
		$this->load->language('order/checkout');

		$data['shipping_methods'] = $this->get_methods();

		if (!isset($this->request->post['shipping_method'])) {
			$data['error']['warning'] = $this->language->get('error_shipping');
		} else {
			$shipping = explode('.', $this->request->post['shipping_method']);

			if (!isset($shipping[0]) || !isset($shipping[1]) || !isset($data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]])) {
				$data['error']['warning'] = $this->language->get('error_shipping');
			}
		}

		if (empty($data['error'])) {
			$shipping = explode('.', $this->request->post['shipping_method']);

			$this->session->data['shipping_method'] = $data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];

			if (property_exists($this->{'model_extension_shipping_' . $shipping[0]}, 'validate')) {
				$data['error'] = $this->{'model_extension_shipping_' . $shipping[0]}->validate();
			}
		} 

		if (empty($data['error'])) {
			if (isset($this->session->data['shipping_method']['title'])) {
				$this->session->data['order']['shipping_method'] = $this->session->data['shipping_method']['title'];
			} else {
				$this->session->data['order']['shipping_method'] = '';
			}

			if (isset($this->session->data['shipping_method']['code'])) {
				$this->session->data['order']['shipping_code'] = $this->session->data['shipping_method']['code'];
			} else {
				$this->session->data['order']['shipping_code'] = '';
			}
		} else  {
			$this->session->data['order']['validate'] = false;
			unset($this->session->data['order']['shipping_method']);
			unset($this->session->data['order']['shipping_code']);

			$this->session->data['error'] = $this->language->get('error_form');
		}

		$this->config->set('block_order_shipping_method', $data);
	}

	protected function get_methods() {
		if (!isset($this->session->data['contact']))
			$this->session->data['contact'] = array();

		// Shipping Methods
		$method_data = array();

		$this->load->model('core/extension');
		$this->load->model('tool/image');

		$results = $this->model_core_extension->getExtensions('shipping');

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
			if ($this->config->get('shipping_' . $result['code'] . '_status')) {
				$this->load->model('extension/shipping/' . $result['code']);

				$method = $this->{'model_extension_shipping_' . $result['code']}->getQuote($address);

				if ($method) {
					if (isset($method['quote']))
						foreach ($method['quote'] as &$item) {
							$item['image'] = isset($item['image']) ? $this->model_tool_image->resize($item['image'], 108, 76) : '';
						}
						unset($item);

					$method['code'] = $result['code'];

					if (!isset($method['sort_order']))
						$method['sort_order'] = 0;

					$method_data[$result['code']] = $method;
				}
			}
		}

		$sort_order = array();

		foreach ($method_data as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC, $method_data);

		if (isset($this->session->data['shipping_method'])) {
			$shipping = explode('.', $this->session->data['shipping_method']['code']);

			if (!isset($shipping[0]) || !isset($shipping[1]) || !isset($method_data[$shipping[0]]['quote'][$shipping[1]])) {
				unset($this->session->data['shipping_method']);
			}
		}

		return $method_data;
	}
}