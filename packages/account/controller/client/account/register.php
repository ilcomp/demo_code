<?php
namespace Controller\Account;

class Register extends \Controller {
	private $error = array();
	private $redirect;

	public function index() {
		if ($this->account->isLogged())
			$this->response->redirect($this->url->link('account/account'));

		$this->load->language('account/account');
		$this->load->language('account/register');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['account_groups'] = array();

		if (is_array($this->config->get('account_group_display'))) {
			$this->load->model('account/account_group');

			$account_groups = $this->model_account_account_group->getAccountGroups();

			foreach ($account_groups as $account_group) {
				if (in_array($account_group['account_group_id'], $this->config->get('account_group_display'))) {
					$account_group['form'] = $this->form(array(
						'method' => 'account_register_' . $account_group['account_group_id'],
						'account_group_id' => $account_group['account_group_id']
					));

					$data['account_groups'][] = $account_group;
				}
			}

			if (!isset($data['account_group_id'])) {
				if (isset($this->request->post['account_group_id'])) {
					$data['account_group_id'] = $this->request->post['account_group_id'];
				} else {
					$data['account_group_id'] = $this->config->get('account_group_id');
				}
			}
		}

		if (empty($data['account_groups']))
			$data['form'] = $this->form(array(
				'method' => 'account_register'
			));

		if ($this->redirect) {
			if (!empty($data['success']))
				$this->session->data['success'] = $data['success'];

			if (!empty($data['error_warning']))
				$this->session->data['error_warning'] = $data['error_warning'];

			$data['location'] = $this->redirect;
		}

		$data['actions']['login'] = $this->url->link('account/login');

		if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->request->server['HTTP_ACCEPT'] == 'text/html') {
			$this->response->setOutput($data['form']);
		} elseif ($this->request->server['HTTP_ACCEPT'] == 'application/json') {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput($data);
		} else {
			if ($this->redirect) {
				$this->response->redirect($this->redirect);
			}

			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home')
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_register'),
				'href' => $this->url->link('account/register')
			);

			$data['content'] = $this->load->view('account/register', $data);

			if ($this->request->server['HTTP_ACCEPT'] == 'text/html') {
				$this->response->setOutput($data['content']);
			} else {
				$this->document->setTitle($this->language->get('heading_title'));

				$this->response->setOutput($this->load->controller('common/template', $data));
			}
		}
	}

	public function form($data = array()) {
		if ($this->account->isLogged())
			return;

		$this->load->model('account/account');
		$this->load->model('account/account_auth');
		$this->load->model('account/account_attempt');
		$this->load->model('core/custom_field');

		if (!isset($data['method']))
			$data['method'] = isset($this->request->post['method']) && strpos($this->request->post['method'], 'register') > 0 ? $this->request->post['method'] : 'form_register';

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && isset($this->request->post['method']) && ($this->request->post['method'] == $data['method']) && $this->validate()) {
			$this->load->controller('order/checkout/reset');

			$account_id = $this->model_account_account->addAccount($this->request->post);

			foreach (array('login', 'email', 'telephone') as $type) {
				if (!empty($this->request->post[$type])) {
					$this->model_account_account_auth->addAccountAuth(array(
						'account_id' => $account_id,
						'type' => $type,
						'login' => $this->request->post[$type],
						'status' => in_array($type, $this->config->get('account_auth_login'))
					));

					// Clear any previous login attempts for unregistered accounts.
					$this->model_account_account_attempt->deleteAccountAttempts($this->request->post[$type]);
				}
			}

			$this->session->data['success'] = $this->language->get('text_success');

			if (isset($this->request->post['redirect']) && $this->request->post['redirect'] != $this->url->link('account/register') && (strpos($this->request->post['redirect'], $this->config->get('config_url')) !== false)) {
				$data['location'] = str_replace('&amp;', '&', $this->request->post['redirect']);

				$this->redirect = str_replace('&amp;', '&', $this->request->post['redirect']);
			} else {
				$data['location'] = $this->url->link('account/login');

				$this->redirect = $this->url->link('account/login');
			}
		}

		$this->load->language('account/account');
		$this->load->language('account/register');

		$data['text_account_already'] = sprintf($this->language->get('text_account_already'), $this->url->link('account/login'));

		$data['error'] = $this->error;

		if (isset($this->session->data['error_warning'])) {
			$data['error_warning'] = $this->session->data['error_warning'];

			unset($this->session->data['error_warning']);
		} elseif (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if ($this->config->get('code_confirm_id')) {
			$data_confirm['code_confirm_id'] = $this->config->get('code_confirm_id');
			$data_confirm['date_added'] = $this->config->get('code_confirm_date_added');

			if (isset($this->request->post['code_confirm']))
				$data_confirm['code_confirm'] = $this->request->post['code_confirm'];
			else
				$data_confirm['code_confirm'] = '';

			$data['code_confirm'] = $this->load->view('account/code_confirm', $data_confirm);
		} else {
			$data['code_confirm'] = '';
		}

		$data['action'] = $this->url->link('account/register');
		$data['actions']['login'] = $this->url->link('account/login');

		$data['account_groups'] = array();

		if (is_array($this->config->get('account_group_display'))) {
			$this->load->model('account/account_group');

			$account_groups = $this->model_account_account_group->getAccountGroups();

			foreach ($account_groups as $account_group) {
				if (in_array($account_group['account_group_id'], $this->config->get('account_group_display'))) {
					$data['account_groups'][] = $account_group;
				}
			}

			if (!isset($data['account_group_id'])) {
				if (isset($this->request->post['account_group_id'])) {
					$data['account_group_id'] = $this->request->post['account_group_id'];
				} else {
					$data['account_group_id'] = $this->config->get('account_group_id');
				}
			}
		}

		if (isset($this->request->post['login'])) {
			$data['login'] = (string)$this->request->post['login'];
		} else {
			$data['login'] = '';
		}

		if (isset($this->request->post['email'])) {
			$data['email'] = (string)$this->request->post['email'];
		} else {
			$data['email'] = '';
		}

		if (isset($this->request->post['telephone'])) {
			$data['telephone'] = (string)$this->request->post['telephone'];

			if ($data['telephone'])
				$data['telephone'] = '+' . preg_replace('/^(\d{1})(\d{3})/', '$1 ($2) ', $data['telephone']);
		} else {
			$data['telephone'] = '';
		}

		if (isset($this->request->post['password'])) {
			$data['password'] = $this->request->post['password'];
		} else {
			$data['password'] = '';
		}

		if (isset($this->request->post['password_confirm'])) {
			$data['password_confirm'] = $this->request->post['password_confirm'];
		} else {
			$data['password_confirm'] = '';
		}

		if (isset($this->request->post['custom_field'])) {
			$custom_field_values = $this->request->post['custom_field'];
		} else {
			$custom_field_values = array();
		}

		$this->load->model('core/custom_field');

		$data['custom_fields_contact'] = $this->model_core_custom_field->getAsField($custom_field_values, 'account_contact');

		if (!empty($data['account_group_id'])) {
			$data['custom_fields_account_group'] = $this->model_core_custom_field->getAsField($custom_field_values, 'account_group_' . $data['account_group_id']);
		}

		if ($this->request->server['HTTP_ACCEPT'] == 'application/json') {
			return $data;
		} else {
			return $this->load->view('account/form_register', $data);
		}
	}

	private function validate() {
		if ($this->config->get('error_warning')) {
			$this->error['warning'] = $this->config->get('error_warning');
		}

		if (isset($this->request->post['login'])) {
			$this->request->post['login'] = trim($this->request->post['login']);

			if (utf8_strlen($this->request->post['login']) < 3 || utf8_strlen($this->request->post['login']) > 32)
				$this->error['login'] = $this->language->get('error_login');
			elseif ($this->model_account_account_auth->getAccountAuthByData(array('login' => $this->request->post['login'])))
				$this->error['login'] = $this->language->get('error_exists');
		}

		if (isset($this->request->post['email'])) {
			$this->request->post['email'] = trim($this->request->post['email']);

			if (utf8_strlen($this->request->post['email']) < 3 || utf8_strlen($this->request->post['email']) > 96 || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL))
				$this->error['email'] = $this->language->get('error_email');
			elseif ($this->model_account_account_auth->getAccountAuthByData(array('login' => $this->request->post['email'])))
				$this->error['email'] = $this->language->get('error_exists');
		}

		if (isset($this->request->post['telephone'])) {
			$this->request->post['telephone'] = preg_replace('/\D/', '', preg_replace('/^8/', '7', $this->request->post['telephone']));

			if (utf8_strlen($this->request->post['telephone']) < 3 || utf8_strlen($this->request->post['telephone']) > 32)
				$this->error['telephone'] = $this->language->get('error_telephone');
			elseif ($this->model_account_account_auth->getAccountAuthByData(array('login' => $this->request->post['telephone'])))
				$this->error['telephone'] = $this->language->get('error_exists');
		}

		if (utf8_strlen($this->request->post['password']) < 4 || utf8_strlen($this->request->post['password']) > 40) {
			$this->error['password'] = $this->language->get('error_password');
		}

		if (isset($this->request->post['password_confirm']) && $this->request->post['password_confirm'] != $this->request->post['password']) {
			$error['password_confirm'] = $this->language->get('error_password_confirm');
		}

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

		if (!empty($this->request->post['account_group_id'])) {
			$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('account_group_' . $this->request->post['account_group_id']);

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

								$result = $this->load->controller('tool/upload/image', $value, 'reg_', 'account/');

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

							$result = $this->load->controller('tool/upload/image', $value, 'reg_', 'account/');

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

								$result = $this->load->controller('tool/upload/file', $value, 'reg_', 'account/');

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

							$result = $this->load->controller('tool/upload/file', $value, 'reg_', 'account/');

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

		$this->event->trigger('controller/account/register/validate', array('account/register/validate', &$this->error));

		if (!$this->error) {
			foreach ($this->config->get('account_auth_login') as $type) {
				if (isset($this->request->post[$type]))
					$error = $this->load->controller('extension/account_login/' . $type, $this->request->post[$type]);
				else
					$error = $this->language->get('error_register');

				if ($error)
					$this->error['warning'] = $error;
			}
		}

		return !$this->error;
	}
}