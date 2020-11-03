<?php
namespace Controller\Order;

class Contact extends \Controller {
	private $error;

	public function index() {
		$this->load->language('api/contact');

		// Delete past account in case there is an error
		unset($this->session->data['contact']);

		$json = array();

		// Add keys for missing post vars
		$keys = array(
			'account_id',
			'account_group_id',
			'email',
			'telephone',
			'custom_field',
		);

		foreach ($keys as $key) {
			if (!isset($this->request->post[$key])) {
				$this->request->post[$key] = '';
			}
		}

		unset($this->session->data['contact']);

		$this->session->data['contact']['account_id'] = isset($this->request->post['account_id']) ? $this->request->post['account_id'] : 0;
		$this->session->data['contact']['account_group_id'] = isset($this->request->post['account_group_id']) ? $this->request->post['account_group_id'] : $this->config->get('config_account_group_id');
		$this->session->data['contact']['email'] = $this->request->post['email'];
		$this->session->data['contact']['telephone'] = $this->request->post['telephone'];

		if (is_array($this->request->post['custom_field'])) {
			$this->session->data['custom_field'] = $this->request->post['custom_field'];
		} else {
			$this->session->data['custom_field'] = array();
		}

		if ($this->validate()){
			$this->session->data['order'] = isset($this->session->data['order']) ? array_merge($this->session->data['order'], $this->session->data['contact']) : $this->session->data['contact'];
			$this->session->data['order']['custom_field'] = $this->session->data['custom_field'];
		} else {
			$json['error'] = $this->error;

			$this->session->data['order']['validate'] = false;
		}

		$json['success'] = $this->language->get('text_success');

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}

	public function validate() {
		$this->load->model('core/custom_field');

		if ((utf8_strlen($this->request->post['email']) > 96) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
			$this->error['email'] = $this->language->get('error_email');
		}

		if ((utf8_strlen(preg_replace('/\D/', '', $this->request->post['telephone'])) < 11) || (utf8_strlen($this->request->post['telephone']) > 32)) {

			$this->error['telephone'] = $this->language->get('error_telephone');
		}

		$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('order_contact');

		foreach ($custom_fields as $custom_field) {
			if ($custom_field['required'] && empty($this->request->post['custom_field'][$custom_field['custom_field_id']][-1])) {
				$this->error['custom_field' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
			} elseif (!empty($this->request->post['custom_field'][$custom_field['custom_field_id']][-1]) && !empty($custom_field['setting']['validation']) && filter_var($this->request->post['custom_field'][$custom_field['custom_field_id']][-1], FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => '/' . html_entity_decode($custom_field['setting']['validation'], ENT_QUOTES, 'UTF-8') . '/')))) {
				$this->error['custom_field' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
			}
		}

		$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('order_address');

		foreach ($custom_fields as $custom_field) {
			if ($custom_field['required'] && empty($this->request->post['custom_field'][$custom_field['custom_field_id']][-1])) {
				$this->error['custom_field' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
			} elseif (!empty($this->request->post['custom_field'][$custom_field['custom_field_id']][-1]) && !empty($custom_field['setting']['validation']) && filter_var($this->request->post['custom_field'][$custom_field['custom_field_id']][-1], FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => '/' . html_entity_decode($custom_field['setting']['validation'], ENT_QUOTES, 'UTF-8') . '/')))) {
				$this->error['custom_field' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
			}
		}

		return !$this->error;
	}
}