<?php
namespace Controller\Setting;

class System extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('setting/setting');
		$this->load->language('setting/system');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_core_setting->editSetting('system', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['encryption'])) {
			$data['error_encryption'] = $this->error['encryption'];
		} else {
			$data['error_encryption'] = '';
		}

		if (isset($this->error['extension'])) {
			$data['error_extension'] = $this->error['extension'];
		} else {
			$data['error_extension'] = '';
		}

		if (isset($this->error['mime'])) {
			$data['error_mime'] = $this->error['mime'];
		} else {
			$data['error_mime'] = '';
		}

		if (isset($this->error['log'])) {
			$data['error_log'] = $this->error['log'];
		} else {
			$data['error_log'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('setting/system', 'user_token=' . $this->session->data['user_token'])
		);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['action'] = $this->url->link('setting/system', 'user_token=' . $this->session->data['user_token']);

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('setting/system', 'user_token=' . $this->session->data['user_token']);

		$data['user_token'] = $this->session->data['user_token'];

		$data['store_url'] = HTTP_APPLICATION_CLIENT;

		if (isset($this->request->post['system_maintenance'])) {
			$data['system_maintenance'] = $this->request->post['system_maintenance'];
		} else {
			$data['system_maintenance'] = $this->config->get('system_maintenance');
		}

		if (isset($this->request->post['system_seo_url'])) {
			$data['system_seo_url'] = $this->request->post['system_seo_url'];
		} else {
			$data['system_seo_url'] = $this->config->get('system_seo_url');
		}

		if (isset($this->request->post['system_seo_url_consolidate'])) {
			$data['system_seo_url_consolidate'] = $this->request->post['system_seo_url_consolidate'];
		} else {
			$data['system_seo_url_consolidate'] = $this->config->get('system_seo_url_consolidate');
		}

		if (isset($this->request->post['system_robots'])) {
			$data['system_robots'] = $this->request->post['system_robots'];
		} else {
			$data['system_robots'] = $this->config->get('system_robots');
		}

		if (isset($this->request->post['system_compression'])) {
			$data['system_compression'] = $this->request->post['system_compression'];
		} else {
			$data['system_compression'] = $this->config->get('system_compression');
		}

		if (isset($this->request->post['system_password'])) {
			$data['system_password'] = $this->request->post['system_password'];
		} else {
			$data['system_password'] = $this->config->get('system_password');
		}

		if (isset($this->request->post['system_shared'])) {
			$data['system_shared'] = $this->request->post['system_shared'];
		} else {
			$data['system_shared'] = $this->config->get('system_shared');
		}

		if (isset($this->request->post['system_encryption'])) {
			$data['system_encryption'] = $this->request->post['system_encryption'];
		} else {
			$data['system_encryption'] = $this->config->get('system_encryption');
		}

		if (isset($this->request->post['system_file_max_size'])) {
			$data['system_file_max_size'] = $this->request->post['system_file_max_size'];
		} elseif ($this->config->get('system_file_max_size')) {
			$data['system_file_max_size'] = $this->config->get('system_file_max_size');
		} else {
			$data['system_file_max_size'] = 300000;
		}

		if (isset($this->request->post['system_file_ext_allowed'])) {
			$data['system_file_ext_allowed'] = $this->request->post['system_file_ext_allowed'];
		} else {
			$data['system_file_ext_allowed'] = $this->config->get('system_file_ext_allowed');
		}

		if (isset($this->request->post['system_file_mime_allowed'])) {
			$data['system_file_mime_allowed'] = $this->request->post['system_file_mime_allowed'];
		} else {
			$data['system_file_mime_allowed'] = $this->config->get('system_file_mime_allowed');
		}

		if (isset($this->request->post['system_error_display'])) {
			$data['system_error_display'] = $this->request->post['system_error_display'];
		} else {
			$data['system_error_display'] = $this->config->get('system_error_display');
		}

		if (isset($this->request->post['system_error_log'])) {
			$data['system_error_log'] = $this->request->post['system_error_log'];
		} else {
			$data['system_error_log'] = $this->config->get('system_error_log');
		}

		if (isset($this->request->post['system_error_filename'])) {
			$data['system_error_filename'] = $this->request->post['system_error_filename'];
		} else {
			$data['system_error_filename'] = $this->config->get('system_error_filename');
		}
		if (isset($this->request->post['system_api_id'])) {
			$data['system_api_id'] = $this->request->post['system_api_id'];
		} else {
			$data['system_api_id'] = $this->config->get('system_api_id');
		}

		if (isset($this->request->post['system_timezone'])) {
			$data['system_timezone'] = $this->request->post['system_timezone'];
		} elseif ($this->config->has('system_timezone')) {
			$data['system_timezone'] = $this->config->get('system_timezone');
		} else {
			$data['system_timezone'] = 'UTC';
		}

		// Set Time Zone
		$data['timezones'] = array();

		$timestamp = time();

		$timezones = timezone_identifiers_list();

		foreach($timezones as $timezone) {
			date_default_timezone_set($timezone);

			$hour = ' (' . date('P', $timestamp) . ')';

			$data['timezones'][] = array(
				'text'  => $timezone . $hour,
				'value' => $timezone
			);
		}

		date_default_timezone_set($this->config->get('system_timezone'));

		if (isset($this->request->post['system_captcha'])) {
			$data['system_captcha'] = $this->request->post['system_captcha'];
		} else {
			$data['system_captcha'] = $this->config->get('system_captcha');
		}

		$this->load->model('core/extension');

		$data['captchas'] = array();

		// Get a list of installed captchas
		$extensions = $this->model_core_extension->getInstalled('captcha');

		foreach ($extensions as $code) {
			$this->load->language('extension/captcha/' . $code, 'extension');

			if ($this->config->get('captcha_' . $code . '_status')) {
				$data['captchas'][] = array(
					'text'  => $this->language->get('extension')->get('heading_title'),
					'value' => $code
				);
			}
		}

		if (isset($this->request->post['system_captcha_page'])) {
			$data['system_captcha_page'] = $this->request->post['system_captcha_page'];
		} elseif ($this->config->has('system_captcha_page')) {
		   	$data['system_captcha_page'] = $this->config->get('system_captcha_page');
		} else {
			$data['system_captcha_page'] = array();
		}

		$data['captcha_pages'] = array();

		// $data['captcha_pages'][] = array(
		// 	'text'  => $this->language->get('text_register'),
		// 	'value' => 'register'
		// );

		// $data['captcha_pages'][] = array(
		// 	'text'  => $this->language->get('text_login'),
		// 	'value' => 'login'
		// );

		// $data['captcha_pages'][] = array(
		// 	'text'  => $this->language->get('text_forgotten'),
		// 	'value' => 'forgotten'
		// );

		// $data['captcha_pages'][] = array(
		// 	'text'  => $this->language->get('text_guest'),
		// 	'value' => 'guest'
		// );

		// $data['captcha_pages'][] = array(
		// 	'text'  => $this->language->get('text_information'),
		// 	'value' => 'information'
		// );

		$data['captcha_pages'][] = array(
			'text'  => $this->language->get('text_product'),
			'value' => 'product'
		);

		// $data['captcha_pages'][] = array(
		// 	'text'  => $this->language->get('text_review'),
		// 	'value' => 'review'
		// );

		// $data['captcha_pages'][] = array(
		// 	'text'  => $this->language->get('text_return'),
		// 	'value' => 'return'
		// );

		$data['captcha_pages'][] = array(
			'text'  => $this->language->get('text_callback'),
			'value' => 'callback'
		);

		$this->load->model('core/api');

		$data['apis'] = $this->model_core_api->getApis();

		$data['content'] = $this->load->view('setting/system', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'setting/system')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['system_encryption']) < 32) || (utf8_strlen($this->request->post['system_encryption']) > 1024)) {
			$this->error['encryption'] = $this->language->get('error_encryption');
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		$disallowed = array(
			'php',
			'php4',
			'php3',
		);

		$extensions = explode("\n", $this->request->post['system_file_ext_allowed']);

		foreach ($extensions as $extension) {
			if (in_array(trim($extension), $disallowed)) {
				$this->error['extension'] = $this->language->get('error_extension');

				break;
			}
		}

		$disallowed = array(
			'php',
			'php4',
			'php3',
		);

		$mimes = explode("\n", $this->request->post['system_file_mime_allowed']);

		foreach ($mimes as $mime) {
			if (in_array(trim($mime), $disallowed)) {
				$this->error['mime'] = $this->language->get('error_mime');

				break;
			}
		}

		if (!$this->request->post['system_error_filename']) {
			$this->error['log'] = $this->language->get('error_log_required');
		} elseif (preg_match('/\.\.[\/\\\]?/', $this->request->post['system_error_filename'])) {
			$this->error['log'] = $this->language->get('error_log_invalid');
		} elseif (substr($this->request->post['system_error_filename'], strrpos($this->request->post['system_error_filename'], '.')) != '.log') {
			$this->error['log'] = $this->language->get('error_log_extension');
		}

		return !$this->error;
	}
}
