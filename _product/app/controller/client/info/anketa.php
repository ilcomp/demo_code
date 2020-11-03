<?php
namespace Controller\Info;

class Anketa extends \Controller {
	private $fields = ['name', 'telephone', 'email', 'age', 'problem', 'how_long', 'tried', 'medication'];

	public function index() {
		if (is_array($this->config->get('form_info_anketa')))
			$data = $this->config->get('form_info_anketa');

        $this->load->language('info/anketa');

		$this->document->setTitle($this->language->get('meta_title'));
		$this->document->setDescription($this->language->get('meta_description'));

		foreach ($this->fields as $field) {
			$data[$field] = isset($this->request->post[$field]) ? (string)$this->request->post[$field] : '';
		}

		$data['photos'] = '';

		$data['content'] = $this->load->view('info/anketa', $data);

		if ($this->request->server['HTTP_ACCEPT'] == 'text/html') {
			$this->response->setOutput($data['content']);
		} else {
			$this->response->setOutput($this->load->controller('common/template', $data));
		}
	}

	public function send($data = array()) {
		if ($this->request->server['REQUEST_METHOD'] != 'POST') {
			 return new \Action('error/not_found');
		}

		$this->load->language('info/anketa');

		foreach ($this->fields as $field) {
			if (empty($this->request->post[$field]))
				$json['error'][$field] = $this->language->get('error_empty');
		}
		
		if (!isset($json['error'])) {
			if (empty($this->request->post['name']) || utf8_strlen($this->request->post['name']) < 3 || utf8_strlen($this->request->post['name']) > 96) {
				$json['error']['name'] = $this->language->get('error_name');
			}

			if (!empty($this->request->post['telephone']) && !filter_var($this->request->post['telephone'], FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => '/^\D?\d\D*\d{3,}/')))) {
				$json['error']['telephone'] = $this->language->get('error_telephone');
			}

			if (empty($this->request->post['email']) || utf8_strlen($this->request->post['email']) < 3 || utf8_strlen($this->request->post['email']) > 96 || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
				$json['error']['email'] = $this->language->get('error_email');
			}
		}

		if ($this->config->get('error_warning')) {
			$json['error']['warning'] = $this->config->get('error_warning');
		}

		$attachment = array();

		if (!isset($json['error']) && !empty($this->request->files['photos'])) {
			$files = $this->request->files['photos'];

			if (!empty($files['name'])) {
				if (is_array($files['name'])) {
					foreach (array_keys($files['name']) as $key) {
						$value = array(
							'name'     => $files['name'][$key],
							'type'     => $files['type'][$key],
							'tmp_name' => $files['tmp_name'][$key],
							'error'    => $files['error'][$key],
							'size'     => $files['size'][$key]
						);

						$result = $this->load->controller('tool/upload/image', $value, null, 'anketa/');

						if (!empty($result['result'])) {
							$attachment[] = DIR_IMAGE . $result['result'];
						} elseif (isset($result['error'])) {
							$json['photos'] = $result['error'];
						}
					}
				} else {
					$value = array(
						'name'     => $files['name'],
						'type'     => $files['type'],
						'tmp_name' => $files['tmp_name'],
						'error'    => $files['error'],
						'size'     => $files['size']
					);

					$result = $this->load->controller('tool/upload/image', $value, null, 'anketa/');

					if (!empty($result['result'])) {
						$attachment = DIR_IMAGE . $result['result'];
					} elseif (isset($result['error'])) {
						$json['photos'] = $result['error'];
					}
				}
			}
		}

		if (!isset($json['error'])) {
			$data = $this->request->post;

			$data['fields'] = array();

			foreach ($this->fields as $field) {
				$data['fields'][] = array(
					'type' => in_array($field, ['name', 'telephone', 'email']) ? 'text' : 'textarea',
					'name' => $this->language->get('entry_' . $field),
					'code' => $field,
					'value' => $this->request->post[$field]
				);
			}

			$emails = explode(',', $this->config->get('mail_alert_email'));
			array_unshift($emails, $this->config->get('mail_email'));

			$this->load->controller('mail/mail', array(
				'subject' => $this->config->get('config_name') . ' - ' . $this->language->get('text_subject'),
				'view' => $this->load->view('mail/anketa', $data),
				'attachment' => $attachment,
				'email' => $emails
			));

			$json['success'] = $this->language->get('text_success');

			foreach ($attachment as $file) {
				unlink($file);
			}

			unset($this->request->post);
		}

		if ($this->request->server['HTTP_ACCEPT'] == 'application/json') {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput($json);
		} else {
			$this->config->set('form_info_anketa', $json);
		}
	}
}