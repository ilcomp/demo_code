<?php
namespace Controller\Startup;

class Startup extends \Controller {
	public function index() {
		$this->load->model('core/setting');

		$settings = $this->model_core_setting->getSettingInfo();

		// Store
		$this->config->set('config_store_id', $settings['store_id']);
		$this->config->set('config_url', HTTP_SERVER);

		// Settings
		foreach ($settings['setting'] as $key => $value) {
			$this->config->set($key, $value);

			if (strpos($key, 'app_api') === 0)
				$this->config->set(str_replace('app_api', 'config', $key), $value);
		}

		// Session
		$this->session->set_expire(432000);

		if (isset($this->request->cookie[$this->config->get('session_name')])) {
			$session_id = $this->request->cookie[$this->config->get('session_name')];
		} else {
			$session_id = '';
		}

		$this->session->start($session_id);

		setcookie($this->config->get('session_name'), $this->session->getId(), (ini_get('session.cookie_lifetime') ? (time() + ini_get('session.cookie_lifetime')) : 0), ini_get('session.cookie_path'), ini_get('session.cookie_domain'), ini_get('session.cookie_secure'), ini_get('session.cookie_httponly'));
		
		// Encryption
		$this->registry->set('encryption', new \Encryption($this->config->get('system_encryption')));

		// Language
		if (isset($this->request->get['language'])) {
			$code = $this->request->get['language'];
		} elseif (isset($this->request->cookie['language'])) {
			$code = $this->request->cookie['language'];
		} else {
			$code = $this->config->get('config_language');
		}

		$language_codes = array_column($settings['languages'], 'language_id', 'code');

		$this->load->controller('startup/seo_url');

		// Replace the default language object
		$language = new \Language($code);
		$language->load($code);
		$this->registry->set('language', $language);

		// Set the config language_id
		$this->config->set('config_language_id', isset($language_codes[$code]) ? $language_codes[$code] : 0);
		$this->config->set('config_language', $code);
	}
}