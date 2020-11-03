<?php
namespace Controller\Block\Order;

class Address extends \Controller {
	public function index() {
		$data = (array)$this->config->get('block_order_address');

		$this->load->language('order/checkout');

		$this->load->model('core/custom_field');

		if (!isset($this->session->data['address']) || !isset($this->session->data['address']['custom_field']))
			$this->session->data['address']['custom_field'] = array();

		$this->load->model('localisation/country');

		$data['countries'] = $this->model_localisation_country->getCountries();

		if (isset($this->request->post['custom_field'])) {
			$data['custom_field_values'] = $this->request->post['custom_field'];
		} else {
			$data['custom_field_values'] = $this->session->data['address']['custom_field'];
		}

		$this->load->model('core/custom_field');

		$data['custom_fields'] = $this->model_core_custom_field->getAsField($data['custom_field_values'], 'order_address');

		foreach ($data['custom_fields'] as $custom_field) {
			$this->session->data['address']['custom_field'][$custom_field['custom_field_id']] = $custom_field['value'];
		}

		return $this->load->view('block/order/address', $data);
	}

	public function form($data = array()) {
		$this->load->language('order/checkout');

		$this->load->model('core/custom_field');

		if (isset($this->request->post['custom_field'])) {
			$this->session->data['address']['custom_field'] = $this->request->post['custom_field'];
		} else {
			$this->session->data['address']['custom_field'] = array();
		}

		if (!isset($this->request->post['shipping_method']) || !preg_match('/^pickup/', $this->request->post['shipping_method'])) {
			$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('order_address');

			foreach ($custom_fields as $custom_field) {
				unset($this->session->data['order']['custom_field'][$custom_field['custom_field_id']]);

				if (isset($this->request->post['custom_field'][$custom_field['custom_field_id']])) {
					$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;
					$value = isset($this->request->post['custom_field'][$custom_field['custom_field_id']][$language_id]) ? $this->request->post['custom_field'][$custom_field['custom_field_id']][$language_id] : '';

					if ($custom_field['required'] && !$value) {
						$data['error']['custom_field' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
					} elseif ($value && !empty($custom_field['setting']['validation']) && filter_var($value, FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => '/' . html_entity_decode($custom_field['setting']['validation'], ENT_QUOTES, 'UTF-8') . '/')))) {
						$data['error']['custom_field' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
					}
				}
			}
		}

		if (empty($data['error'])) {
			foreach ($this->session->data['address'] as $key => $value) {
				if ($key == 'custom_field')
					foreach ($value as $custom_field_id => $item) {
						$this->session->data['order']['custom_field'][$custom_field_id] = $item;
					}
				else
					$this->session->data['order'][$key] = $value;
			}
		} else {
			$this->session->data['order']['validate'] = false;

			$this->session->data['error'] = $this->language->get('error_form');
		}

		$this->config->set('block_order_address', $data);
	}
}