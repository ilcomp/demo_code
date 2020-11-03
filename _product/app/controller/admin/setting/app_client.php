<?php
namespace Controller\Setting;

class AppClient extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('setting/app_client');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_core_setting->editSetting('app_client', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('setting/app_client', 'user_token=' . $this->session->data['user_token']));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		if (isset($this->error['owner'])) {
			$data['error_owner'] = $this->error['owner'];
		} else {
			$data['error_owner'] = '';
		}

		if (isset($this->error['country'])) {
			$data['error_country'] = $this->error['country'];
		} else {
			$data['error_country'] = '';
		}

		if (isset($this->error['zone'])) {
			$data['error_zone'] = $this->error['zone'];
		} else {
			$data['error_zone'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('setting/app_client', 'user_token=' . $this->session->data['user_token'])
		);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['action'] = $this->url->link('setting/app_client', 'user_token=' . $this->session->data['user_token']);

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('setting/app_client', 'user_token=' . $this->session->data['user_token']);

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->post['app_client_theme'])) {
			$data['app_client_theme'] = $this->request->post['app_client_theme'];
		} else {
			$data['app_client_theme'] = $this->config->get('app_client_theme');
		}

		$data['store_url'] = HTTP_APPLICATION_CLIENT;

		$data['themes'] = array();

		$this->load->model('core/extension');

		$extensions = $this->model_core_extension->getInstalled('theme');

		foreach ($extensions as $code) {
			if (strpos($code, 'client_') === 0 && $this->config->get('theme_' . $code . '_status')) {
				$this->load->language('extension/theme/' . $code, 'extension');

				$data['themes'][] = array(
					'text'  => $this->language->get('extension')->get('heading_title'),
					'value' => $code
				);
			}
		}

		if (isset($this->request->post['app_client_name'])) {
			$data['app_client_name'] = $this->request->post['app_client_name'];
		} else {
			$data['app_client_name'] = $this->config->get('app_client_name');
		}

		if (isset($this->request->post['app_client_owner'])) {
			$data['app_client_owner'] = $this->request->post['app_client_owner'];
		} else {
			$data['app_client_owner'] = $this->config->get('app_client_owner');
		}

		if (isset($this->request->post['app_client_email'])) {
			$data['app_client_email'] = $this->request->post['app_client_email'];
		} else {
			$data['app_client_email'] = $this->config->get('app_client_email');
		}

		if (isset($this->request->post['app_client_telephone'])) {
			$data['app_client_telephone'] = $this->request->post['app_client_telephone'];
		} else {
			$data['app_client_telephone'] = $this->config->get('app_client_telephone');
		}

		if (isset($this->request->post['app_client_country_id'])) {
			$data['app_client_country_id'] = $this->request->post['app_client_country_id'];
		} else {
			$data['app_client_country_id'] = $this->config->get('app_client_country_id');
		}

		$this->load->model('localisation/country');

		$data['countries'] = $this->model_localisation_country->getCountries();

		if (isset($this->request->post['app_client_zone_id'])) {
			$data['app_client_zone_id'] = $this->request->post['app_client_zone_id'];
		} else {
			$data['app_client_zone_id'] = $this->config->get('app_client_zone_id');
		}

		if (isset($this->request->post['app_client_timezone'])) {
			$data['app_client_timezone'] = $this->request->post['app_client_timezone'];
		} elseif ($this->config->has('app_client_timezone')) {
			$data['app_client_timezone'] = $this->config->get('app_client_timezone');
		} else {
			$data['app_client_timezone'] = 'UTC';
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

		date_default_timezone_set($this->config->get('app_client_timezone'));

		if (isset($this->request->post['app_client_language'])) {
			$data['app_client_language'] = $this->request->post['app_client_language'];
		} else {
			$data['app_client_language'] = $this->config->get('app_client_language');
		}

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['app_client_fraud_status_id'])) {
			$data['app_client_fraud_status_id'] = $this->request->post['app_client_fraud_status_id'];
		} else {
			$data['app_client_fraud_status_id'] = $this->config->get('app_client_fraud_status_id');
		}

		$this->load->model('tool/image');

		$thumb_width = $this->config->get('admin_image_thumb_width') ? $this->config->get('admin_image_thumb_width') : 100;
		$thumb_height = $this->config->get('admin_image_thumb_height') ? $this->config->get('admin_image_thumb_height') : 100;

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', $thumb_width, $thumb_height);

		if (isset($this->request->post['app_client_logo'])) {
			$data['app_client_logo'] = $this->request->post['app_client_logo'];
		} else {
			$data['app_client_logo'] = $this->config->get('app_client_logo');
		}

		if (isset($this->request->post['app_client_logo']) && is_file(DIR_IMAGE . $this->request->post['app_client_logo'])) {
			$data['logo'] = $this->model_tool_image->resize($this->request->post['app_client_logo'], $thumb_width, $thumb_height);
		} elseif ($this->config->get('app_client_logo') && is_file(DIR_IMAGE . $this->config->get('app_client_logo'))) {
			$data['logo'] = $this->model_tool_image->resize($this->config->get('app_client_logo'), $thumb_width, $thumb_height);
		} else {
			$data['logo'] = $data['placeholder'];
		}

		if (isset($this->request->post['app_client_icon'])) {
			$data['app_client_icon'] = $this->request->post['app_client_icon'];
		} else {
			$data['app_client_icon'] = $this->config->get('app_client_icon');
		}

		if (isset($this->request->post['app_client_icon']) && is_file(DIR_IMAGE . $this->request->post['app_client_icon'])) {
			$data['icon'] = $this->model_tool_image->resize($this->request->post['app_client_icon'], $thumb_width, $thumb_height);
		} elseif ($this->config->get('app_client_icon') && is_file(DIR_IMAGE . $this->config->get('app_client_icon'))) {
			$data['icon'] = $this->model_tool_image->resize($this->config->get('app_client_icon'), $thumb_width, $thumb_height);
		} else {
			$data['icon'] = $data['placeholder'];
		}

		$data['content'] = $this->load->view('setting/app_client', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'setting/app_client')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['app_client_name']) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if ((utf8_strlen($this->request->post['app_client_owner']) < 3) || (utf8_strlen($this->request->post['app_client_owner']) > 64)) {
			$this->error['owner'] = $this->language->get('error_owner');
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}
}