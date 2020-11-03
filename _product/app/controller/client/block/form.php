<?php
namespace Controller\Block;

class Form extends \Controller {
	public function index($data = array()) {
		$return = !empty($data['form_id']);

		if ($this->config->get('block_form'))
			$data = (array)$this->config->get('block_form');

		$this->load->language('block/form');

		$this->load->model('design/form');

		if (!empty($data['form_id']))
			$form_id = (int)$data['form_id'];
		elseif (!empty($this->request->get['form_id']))
			$form_id = (int)$this->request->get['form_id'];

		if (empty($form_id))
			return;

		$form = $this->model_design_form->getForm($form_id);

		if (!$form)
			return;

		$data = array_merge($data, $form);

		if (isset($this->request->post['form'])) {
			$field_values = $this->request->post['form'];
		} else {
			$field_values = array();
		}

		$data['fields'] = $this->model_design_form->getAsField($field_values, $form['form_id']);

		if ($return)
			return $this->load->view('block/form', $data);
		else
			$this->response->setOutput($this->load->view('block/form', $data));
	}

	public function send($data = array()) {
		if (empty($this->request->post['form_id']) || $this->request->server['REQUEST_METHOD'] != 'POST') {
			 return new \Action('error/not_found');
		}

		$this->load->language('block/form');

		$this->load->model('design/form');

		$form = $this->model_design_form->getForm($this->request->post['form_id']);

		if (!$form)
			new \Action('error/not_found');

		$fields = $this->model_design_form->getFormFields($this->request->post['form_id']);

		foreach ($fields as $field) {
			if (isset($this->request->post[$field['code']])) {
				$value = $this->request->post[$field['code']];

				if (!empty($field['required']) && !$value) {
					$json['error'][$field['code']] = $field['error'];
				} elseif (($value && $field['type'] == 'email') && (utf8_strlen($value) < 3 || utf8_strlen($value) > 96 || !filter_var($value, FILTER_VALIDATE_EMAIL))) {
					$json['error'][$field['code']] = $field['error'];
				} elseif ($value && !empty($field['setting']['validate']) && !filter_var($value, FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => '/' . html_entity_decode($field['setting']['validate'], ENT_QUOTES, 'UTF-8') . '/')))) {
					$json['error'][$field['code']] = $field['error'];
				}
			} elseif ($field['required']) {
				$json['error'][$field['code']] = $field['error'];
			}
		}

		if ($this->config->get('error_warning')) {
			$json['error']['warning'] = $this->config->get('error_warning');
		}

		if (!isset($json['error'])) {
			$data = $form;

			$data['fields'] = array();

			foreach ($fields as $field) {
				if (isset($this->request->post[$field['code']]))
					$value = $this->request->post[$field['code']];
				else
					$value = '';

				$field['value'] = $this->model_design_form->getAsValue($value, $field);

				$data['fields'][] = $field;
			}

			if (!empty($form['email'])) {
				$emails = explode(',', $form['email']);
			} else {
				$emails = explode(',', $this->config->get('mail_alert_email'));
				array_unshift($emails, $this->config->get('mail_email'));
			}

			$this->load->controller('mail/mail', array(
				'subject' => $this->config->get('config_name') . ' - ' . $form['name'],
				'view' => $this->load->view('mail/form', $data),
				'email' => $emails
			));

			$json['success'] = $this->language->get('text_success');
		}

		if ($this->request->server['HTTP_ACCEPT'] == 'application/json') {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput($json);
		} else {
			$this->config->set('block_form', $json);
		}
	}
}