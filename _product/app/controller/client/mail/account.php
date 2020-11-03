<?php
namespace Controller\Mail;

class Account extends \Controller {
	public function forgotten(&$route, &$args, &$output) {
		if ($args[0] && $args[1]) {
			$this->load->language('mail/account_forgotten', 'temp');

			$data = $this->language->get('temp')->all();

			$data['reset'] = str_replace('&amp;', '&', $this->url->link('account/reset', 'language=' . $this->config->get('config_language') . '&email=' . urlencode($args[0]) . '&code=' . $args[1]));
			$data['ip'] = $this->request->server['REMOTE_ADDR'];

			$this->load->controller('mail/mail', array(
				'subject' => sprintf($this->language->get('temp')->get('text_subject'), $this->config->get('config_name')),
				'view' => $this->load->view('mail/account_forgotten', $data),
				'email' => $args[0]
			));
		}
	}

	public function register($route, $args, $output) {
		$this->load->language('mail/account_register', 'temp');

		$data = $this->language->get('temp')->all();

		$this->load->model('account/account_group');

		if (isset($args[0]['account_group_id']) && $args[0]['account_group_id']) {
			$account_group_id = $args[0]['account_group_id'];
		} else {
			$account_group_id = $this->config->get('account_group_id');
		}

		$account_group_info = $this->model_account_account_group->getAccountGroup($account_group_id);

		$data['approval'] = $account_group_info ? $account_group_info['approval'] : '';

		if (isset($args[1]) && $args[1] && !$data['approval'])
			$data['link_login'] = str_replace('&amp;', '&', $this->url->link('account/login_code', 'language=' . $this->config->get('config_language') . '&email=' . urlencode($args[0]['email']) . '&code=' . urlencode($args[1])));
		else
			$data['link_login'] = str_replace('&amp;', '&', $this->url->link('account/login', 'language=' . $this->config->get('config_language')));

		$data['login'] = isset($args[0]['login']) ? $args[0]['login'] : $args[0]['email'];
		$data['password'] = isset($args[0]['password']) ? $args[0]['password'] : '';

		$this->load->controller('mail/mail', array(
			'subject' => sprintf($this->language->get('temp')->get('text_subject'), $this->config->get('config_name')),
			'view' => $this->load->view('mail/account_register', $data),
			'email' => $args[0]['email']
		));
	}

	public function register_alert($route, $args, $output) {
		// Send to main admin email if new account email is enabled
		if (in_array('account', (array)$this->config->get('mail_alert'))) {
			$this->load->language('mail/account_register_alert', 'temp');

			$data = $this->language->get('temp')->all();

			$this->load->model('core/custom_field');

			$custom_field_values = isset($args[0]['custom_field']) ? $args[0]['custom_field'] : '';

			$data['custom_fields'] = array();

			$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('account');

			foreach ($custom_fields as $custom_field) {
				$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

				if (isset($custom_field_values[$custom_field['custom_field_id']]) && isset($custom_field_values[$custom_field['custom_field_id']][$language_id])) {
					$value = $custom_field_values[$custom_field['custom_field_id']][$language_id];
				} elseif (isset($custom_field['setting']['default']))
					$value = $custom_field['setting']['default'];
				else
					$value = '';

				$data['custom_fields'][$custom_field['code']] = $this->model_core_custom_field->processValue($value, $custom_field);
			}

			$data['login'] = $this->url->link('account/login', 'language=' . $this->config->get('config_language'));

			$this->load->model('account/account_group');

			if (isset($args[0]['account_group_id']) && $args[0]['account_group_id']) {
				$account_group_id = $args[0]['account_group_id'];
			} else {
				$account_group_id = $this->config->get('account_group_id');
			}

			$account_group_info = $this->model_account_account_group->getAccountGroup($account_group_id);

			$data['account_group'] = $account_group_info ? $account_group_info['name'] : '';

			$data['email'] = $args[0]['email'];
			$data['telephone'] = $args[0]['telephone'];

			$emails = explode(',', $this->config->get('mail_alert_email'));
			array_unshift($emails, $this->config->get('mail_email'));

			$this->load->controller('mail/mail', array(
				'subject' => sprintf($this->language->get('temp')->get('text_subject'), $this->config->get('config_name'), $data['account_group']),
				'view' => $this->load->view('mail/account_register_alert', $data),
				'email' => $emails
			));
		}
	}
}