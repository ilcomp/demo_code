<?php
namespace Controller\Mail;

class Account extends \Controller {
	public function approve($route, $args, $output) {
		$this->load->model('account/account');
		$this->load->model('account/account_auth');

		$account_info = $this->model_account_account->getAccount($args[0]);

		$email = '';

		if ($account_info) {
			$account_auth = $this->model_account_account_auth->getAccountAuthByData(array(
				'account_id' => $account_info['account_id'],
				'type' => $email
			));

			if ($account_auth)
				$email = $account_auth['login'];
		}

		if ($email) {
			$store_name = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');
			$store_url = HTTP_APPLICATION_CLIENT;

			$this->load->model('localisation/language');

			$language_info = $this->model_localisation_language->getLanguage($account_info['language_id']);

			if ($language_info) {
				$language_code = $language_info['code'];
			} else {
				$language_code = $this->config->get('config_language');
			}

			$language = new \Language($language_code);
			$language->load($language_code);
			$language->load('mail/account_approve');

			$data['text_welcome'] = sprintf($language->get('text_welcome'), $store_name);
			$data['text_login'] = $language->get('text_login');
			$data['text_service'] = $language->get('text_service');
			$data['text_thanks'] = $language->get('text_thanks');

			$data['button_login'] = $language->get('button_login');

			$data['login'] = $store_url . 'index.php?route=account/login';
			$data['store'] = $store_name;
			$data['store_url'] = $store_url;

			$this->load->model('tool/image');

			if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
				$data['logo'] = $this->model_tool_image->resize($this->config->get('config_logo'), $this->config->get('theme_default_image_location_width'), $this->config->get('theme_default_image_cart_height'));
			} else {
				$data['logo'] = '';
			}

			$this->load->controller('mail/mail', array(
				'sender' => $store_name,
				'subject' => sprintf($language->get('text_subject'), $store_name),
				'view' => $this->load->view('mail/account_approve', $data),
				'email' => $email
			));
		}
	}

	public function deny($route, $args, $output) {
		$this->load->model('account/account');
		$this->load->model('account/account_auth');

		$account_info = $this->model_account_account->getAccount($args[0]);

		$email = '';

		if ($account_info) {
			$account_auth = $this->model_account_account_auth->getAccountAuthByData(array(
				'account_id' => $account_info['account_id'],
				'type' => $email
			));

			if ($account_auth)
				$email = $account_auth['login'];
		}

		if ($email) {
			$store_name = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');
			$store_url = HTTP_APPLICATION_CLIENT;

			$this->load->model('localisation/language');

			$language_info = $this->model_localisation_language->getLanguage($account_info['language_id']);

			if ($language_info) {
				$language_code = $language_info['code'];
			} else {
				$language_code = $this->config->get('config_language');
			}

			$language = new \Language($language_code);
			$language->load($language_code);
			$language->load('mail/account_deny');

			$data['text_welcome'] = sprintf($language->get('text_welcome'), $store_name);
			$data['text_denied'] = $language->get('text_denied');
			$data['text_thanks'] = $language->get('text_thanks');

			$data['button_contact'] = $language->get('button_contact');

			$data['contact'] = $store_url . 'index.php?route=information/contact';
			$data['store'] = $store_name;
			$data['store_url'] = $store_url;

			$this->load->model('tool/image');

			if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
				$data['logo'] = $this->model_tool_image->resize($this->config->get('config_logo'), $this->config->get('theme_default_image_location_width'), $this->config->get('theme_default_image_cart_height'));
			} else {
				$data['logo'] = '';
			}

			$this->load->controller('mail/mail', array(
				'sender' => $store_name,
				'subject' => sprintf($language->get('text_subject'), $store_name),
				'view' => $this->load->view('mail/account_deny', $data),
				'email' => $email
			));
		}
	}
}