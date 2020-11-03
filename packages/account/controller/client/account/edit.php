<?php
namespace Controller\Account;

class Edit extends \Controller {
	private $error = array();

	public function index($data = array()) {
		if (!$this->account->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/edit');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 401 Unauthorized');

			return new \Action('account/login');
		}

		$this->load->language('account/edit');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('account/account');
		$this->load->model('account/account_auth');
		$this->load->model('core/custom_field');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && empty($this->request->post['form_method']) && $this->validate()) {
			$custom_fields = $this->model_account_account->getAccountCustomFields($this->account->getId());

			foreach ($custom_fields as $custom_field_id => $custom_field) {
				if (!isset($this->request->post['custom_field'][$custom_field_id]))
					$this->request->post['custom_field'][$custom_field_id] = $custom_field;
			}

			$this->model_account_account->editAccount($this->account->getId(), $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('account/edit'));
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_edit'),
			'href' => $this->url->link('account/edit')
		);

		$data['error'] = $this->error;

		if (isset($this->session->data['error_warning'])) {
			$data['error_warning'] = $this->session->data['error_warning'];
		} elseif (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['action'] = $this->url->link('account/edit');
		$data['actions']['remove_auth'] = $this->url->link('account/edit/remove_auth');

		$account_info = $this->model_account_account->getAccount($this->account->getId());

		$this->load->model('tool/image');
		$this->load->model('core/extension');

		$sort_order = array();

		$results = $this->model_core_extension->getExtensions('account_login');

		foreach ($results as $key => $value) {
			$sort_order[$key] = $this->config->get('account_login_' . $value['code'] . '_sort_order');
		}

		array_multisort($sort_order, SORT_ASC, $results);

		$data['socials'] = array();

		foreach ($results as $result) {
			if ($this->config->get('account_login_' . $result['code'] . '_status')) {
				$data['socials'][] = array(
					'type' => $result['code'],
					'code' => str_replace('social_', '', $result['code']),
					'active' => $this->model_account_account_auth->getAccountAuthLogin($result['code']),
					'image' => $this->model_tool_image->link('social/' . str_replace('social_', '', $result['code']) . '.svg'),
					'link' => $this->url->link('extension/account_login/' . $result['code'] . '/form')
				);
			}
		}

		$account_auth = $this->model_account_account_auth->getAccountAuthLoginByData(array('type' => 'login'));
		$data['login'] = $account_auth ? $account_auth['login'] : '';

		$account_auth = $this->model_account_account_auth->getAccountAuthLoginByData(array('type' => 'email'));
		$data['email'] = $account_auth ? $account_auth['login'] : '';

		$account_auth = $this->model_account_account_auth->getAccountAuthLoginByData(array('type' => 'telephone'));
		$data['telephone'] = $account_auth ? $account_auth['login'] : '';

		if ($data['telephone'])
			$data['telephone'] = '+' . preg_replace('/^(\d{1})(\d{3})/', '$1 ($2) ', $data['telephone']);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['address'])) {
			$data['addresses'] = (array)$this->request->post['address'];
		} elseif (!empty($account_info)) {
			$data['addresses'] = (array)$this->model_account_account->getAccountAddress($account_info['account_id']);
		} else {
			$data['addresses'] = array();
		}

		if (isset($this->request->post['custom_field'])) {
			$custom_field_values = $this->request->post['custom_field'];
		} elseif (!empty($account_info)) {
			$custom_field_values = $this->model_account_account->getAccountCustomFields($account_info['account_id']);
		} else {
			$custom_field_values = array();
		}

		$data['custom_fields_contact'] = $this->model_core_custom_field->getAsField($custom_field_values, 'account_contact');

		$data['custom_fields_address'] = $this->model_core_custom_field->getAsField($custom_field_values, 'account_address');

		if (!empty($account_info['account_group_id'])) {
			$data['custom_fields_account_group'] = $this->model_core_custom_field->getAsField($custom_field_values, 'account_group_' . $account_info['account_group_id']);
		}

		$data['back'] = $this->url->link('account/account');
		
		$data['edit'] = $this->url->link('account/edit');
		$data['actions']['edit_login'] = $this->url->link('extension/account_login/login/form');
		$data['actions']['edit_email'] = $this->url->link('extension/account_login/email/form');
		$data['actions']['edit_telephone'] = $this->url->link('extension/account_login/telephone/form');
		$data['actions']['edit_password'] = $this->url->link('account/password');

		if ($this->request->server['HTTP_ACCEPT'] == 'application/json') {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput($data);
		} else {
			$data['content'] = $this->load->view('account/edit', $data);

			if (!empty($data['return_content'])) {
				return $data['content'];
			} elseif ($this->request->server['HTTP_ACCEPT'] == 'text/html') {
				$this->response->setOutput($data['content']);
			} else {
				$this->document->setTitle($this->language->get('heading_title'));

				$this->response->setOutput($this->load->controller('common/template', $data));
			}
		}
	}

	protected function validate() {
		// if (isset($this->request->post['login'])) {
		// 	$this->request->post['login'] = trim($this->request->post['login']);

		// 	if (utf8_strlen($this->request->post['login']) < 4 || utf8_strlen($this->request->post['login']) > 32)
		// 		$this->error['login'] = $this->language->get('error_login');
		// 	elseif ($this->model_account_account_auth->excludeAccountAuthLogin($this->request->post['login'], 'login'))
		// 		$this->error['login'] = $this->language->get('error_login_exists');
		// }

		// if (isset($this->request->post['email'])) {
		// 	$this->request->post['email'] = trim($this->request->post['email']);

		// 	if (!empty($this->request->post['email']) && (utf8_strlen($this->request->post['email']) > 96 || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)))
		// 		$this->error['email'] = $this->language->get('error_email');
		// 	elseif ($this->model_account_account_auth->excludeAccountAuthLogin($this->request->post['email'], 'email'))
		// 		$this->error['email'] = $this->language->get('error_email_exists');
		// }

		// if (isset($this->request->post['telephone'])) {
		// 	$this->request->post['telephone'] = trim($this->request->post['telephone']);

		// 	if (!empty($this->request->post['telephone']) && (utf8_strlen($this->request->post['telephone']) < 10 || utf8_strlen($this->request->post['telephone']) > 32))
		// 		$this->error['telephone'] = $this->language->get('error_telephone');
		// 	elseif ($this->model_account_account_auth->excludeAccountAuthLogin($this->request->post['telephone'], 'telephone'))
		// 		$this->error['telephone'] = $this->language->get('error_telephone_exists');
		// }

		$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('account_contact');

		foreach ($custom_fields as $custom_field) {
			if (isset($this->request->post['custom_field'][$custom_field['custom_field_id']])) {
				$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;
				$value = isset($this->request->post['custom_field'][$custom_field['custom_field_id']][$language_id]) ? $this->request->post['custom_field'][$custom_field['custom_field_id']][$language_id] : '';

				if ($custom_field['required'] && !$value) {
					$this->error['custom_field' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
				} elseif ($value && !empty($custom_field['setting']['validation']) && filter_var($value, FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => '/' . html_entity_decode($custom_field['setting']['validation'], ENT_QUOTES, 'UTF-8') . '/')))) {
					$this->error['custom_field' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
				}
			}
		}

		if (isset($this->request->files['custom_field_image'])) {
			$files = $this->request->files['custom_field_image'];

			if (!empty($files['name']) && is_array($files['name'])) {
				foreach (array_keys($files['name']) as $custom_field_id) {
					foreach (array_keys($files['name'][$custom_field_id]) as $language_id) {
						if (is_array($files['name'][$custom_field_id][$language_id])) {
							foreach (array_keys($files['name'][$custom_field_id][$language_id]) as $key) {
								$value = array(
									'name'     => $files['name'][$custom_field_id][$language_id][$key],
									'type'     => $files['type'][$custom_field_id][$language_id][$key],
									'tmp_name' => $files['tmp_name'][$custom_field_id][$language_id][$key],
									'error'    => $files['error'][$custom_field_id][$language_id][$key],
									'size'     => $files['size'][$custom_field_id][$language_id][$key]
								);

								$result = $this->load->controller('tool/upload/image', $value, 'ed_', 'account/');

								if (!empty($result['result'])) {
									$this->request->post['custom_field'][$custom_field_id][$language_id][$key] = $result['result'];
								} elseif (isset($result['error'])) {
									$this->error['custom_field' . $custom_field_id] = $result['error'];
								}
							}
						} else {
							$value = array(
								'name'     => $files['name'][$custom_field_id][$language_id],
								'type'     => $files['type'][$custom_field_id][$language_id],
								'tmp_name' => $files['tmp_name'][$custom_field_id][$language_id],
								'error'    => $files['error'][$custom_field_id][$language_id],
								'size'     => $files['size'][$custom_field_id][$language_id]
							);

							$result = $this->load->controller('tool/upload/image', $value, 'ed_', 'account/');

							if (!empty($result['result'])) {
								$this->request->post['custom_field'][$custom_field_id][$language_id] = $result['result'];
							} elseif (isset($result['error'])) {
								$this->error['custom_field' . $custom_field_id] = $result['error'];
							}
						}
					}
				}
			}
		}

		if (isset($this->request->files['custom_field_file'])) {
			$files = $this->request->files['custom_field_file'];

			if (!empty($files['name']) && is_array($files['name'])) {
				foreach (array_keys($files['name']) as $custom_field_id) {
					foreach (array_keys($files['name'][$custom_field_id]) as $language_id) {
						if (is_array($files['name'][$custom_field_id][$language_id])) {
							foreach (array_keys($files['name'][$custom_field_id][$language_id]) as $key) {
								$value = array(
									'name'     => $files['name'][$custom_field_id][$language_id][$key],
									'type'     => $files['type'][$custom_field_id][$language_id][$key],
									'tmp_name' => $files['tmp_name'][$custom_field_id][$language_id][$key],
									'error'    => $files['error'][$custom_field_id][$language_id][$key],
									'size'     => $files['size'][$custom_field_id][$language_id][$key]
								);

								$result = $this->load->controller('tool/upload/file', $value, 'ed_', 'account/');

								if (!empty($result['result'])) {
									$this->request->post['custom_field'][$custom_field_id][$language_id][$key] = $result['result'];
								} elseif (isset($result['error'])) {
									$this->error['custom_field' . $custom_field_id] = $result['error'];
								}
							}
						} else {
							$value = array(
								'name'     => $files['name'][$custom_field_id][$language_id],
								'type'     => $files['type'][$custom_field_id][$language_id],
								'tmp_name' => $files['tmp_name'][$custom_field_id][$language_id],
								'error'    => $files['error'][$custom_field_id][$language_id],
								'size'     => $files['size'][$custom_field_id][$language_id]
							);

							$result = $this->load->controller('tool/upload/file', $value, 'ed_', 'account/');

							if (!empty($result['result'])) {
								$this->request->post['custom_field'][$custom_field_id][$language_id] = $result['result'];
							} elseif (isset($result['error'])) {
								$this->error['custom_field' . $custom_field_id] = $result['error'];
							}
						}
					}
				}
			}
		}

		return !$this->error;
	}

	public function upload_avatar($url, $path_file) {
		if (!$url || !$path_file)
			return;

		$data = @file_get_contents($url);

		if ($data) {
			$file_info = new \finfo(FILEINFO_MIME_TYPE);
			$mime_type = $file_info->buffer($data);

			// Allowed file mime types
			$allowed = array(
				'image/jpeg',
				'image/pjpeg',
				'image/png',
				'image/x-png'
			);

			if (!in_array($mime_type, $allowed)) {
				$this->log->write('account/upload_avatar ' . $this->language->get('error_filetype') . ': ' . $url);
			} else {
				$filename = basename($path_file);

				$directory = DIR_IMAGE . 'account/' . str_replace($filename, '', $path_file);

				// Check its a directory
				if (!is_dir($directory)) {
					mkdir(rtrim($directory, '/'), 0777, true);
					chmod(rtrim($directory, '/'), 0777);
				}

				$extension = '';

				if (!preg_match('/\.(jpg|jpeg|png)$/i', $filename)) {
					switch ($mime_type) {
						case 'image/jpeg':
						case 'image/pjpeg':
							$extension = '.jpg';
							break;
						case 'image/png':
						case 'image/x-png':
							$extension = '.png';
							break;
					}
				}

				file_put_contents($directory . $filename . $extension, $data);

				return 'account/' . $path_file . $extension;
			}
		} else {
			$this->log->write('account/upload_avatar ' . $this->language->get('error_upload') . ': ' . $url);
		}
	}

	public function remove_auth($json = array()) {
		if (!$this->account->isLogged() || !isset($this->request->post['type'])) {
			new Action('error/not_found');
		}

		$this->load->model('account/account_auth');

		$this->model_account_account_auth->deleteAccountAuthLoginByType($this->request->post['type']);

		if ($this->request->post['type'] == $this->account->getType())
			unset($this->session->data['registry_account_auth_id']);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}
}