<?php
namespace Controller\Block\Order;

class Contact extends \Controller {
	public function index() {
		$data = (array)$this->config->get('block_order_contact');

		$this->load->language('order/checkout');

		$this->load->model('core/custom_field');

		if (!isset($this->session->data['contact']))
			$this->session->data['contact'] = array();

		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} elseif (isset($this->session->data['contact']['email'])) {
			$data['email'] = $this->session->data['contact']['email'];
		} else {
			$data['email'] = '';
		}

		if (isset($this->request->post['telephone'])) {
			$data['telephone'] = $this->request->post['telephone'];
		} elseif (isset($this->session->data['contact']['telephone'])) {
			$data['telephone'] = $this->session->data['contact']['telephone'];
		} else {
			$data['telephone'] = '';
		}

		if (isset($this->request->post['custom_field'])) {
			$data['custom_field_values'] = $this->request->post['custom_field'];
		} elseif (isset($this->session->data['contact']['custom_field'])) {
			$data['custom_field_values'] = $this->session->data['contact']['custom_field'];
		} else {
			$data['custom_field_values'] = array();
		}

		$this->load->model('core/custom_field');

		$data['custom_fields'] = $this->model_core_custom_field->getAsField($data['custom_field_values'], 'order_contact');

		return $this->load->view('block/order/contact', $data);
	}

	public function form($data = array()) {
		$this->load->model('core/custom_field');

		$this->load->language('order/checkout');

		unset($this->session->data['contact']);

		$this->session->data['contact']['account_group_id'] = $this->config->get('config_account_group_id');
		$this->session->data['contact']['email'] = $this->request->post['email'];
		$this->session->data['contact']['telephone'] = $this->request->post['telephone'];

		if (isset($this->request->post['custom_field'])) {
			$this->session->data['contact']['custom_field'] = $this->request->post['custom_field'];
		} else {
			$this->session->data['contact']['custom_field'] = array();
		}

		if ((utf8_strlen($this->request->post['email']) > 96) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
			$data['error']['email'] = $this->language->get('error_email');
		}

		if ((utf8_strlen(preg_replace('/\D/', '', $this->request->post['telephone'])) < 11) || (utf8_strlen($this->request->post['telephone']) > 32)) {
			$data['error']['telephone'] = $this->language->get('error_telephone');
		}

		$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('order_contact');

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

		if (empty($data['error'])) {
			foreach ($this->session->data['contact'] as $key => $value) {
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

		$this->config->set('block_order_contact', $data);
	}
}