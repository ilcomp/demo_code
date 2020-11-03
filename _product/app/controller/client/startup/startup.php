<?php
namespace Controller\Startup;

class Startup extends \Controller {
	public function index() {
		$this->load->model('core/setting');

		$request['host'] = $_SERVER['HTTP_HOST'];

		$settings = $this->model_core_setting->getSettingInfo($request);

		// Store
		$this->config->set('config_store_id', $settings['store_id']);
		$this->config->set('config_url', HTTP_SERVER);

		// Settings
		foreach ($settings['setting'] as $key => $value) {
			$this->config->set($key, $value);

			if (strpos($key, 'app_client') === 0 && $value) {
				$this->config->set(str_replace('app_client', 'config', $key), $value);
			}
		}

		$this->config->set('template_cache', $this->config->get('developer_theme'));

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
		$this->registry->set('encryption', new \Encryption());

		// Client Theme
		if (!$this->config->get('config_theme') || !is_dir(DIR_TEMPLATE . $this->config->get('config_theme'))) {
			$this->config->set('config_theme', $this->config->get('theme_default'));
		}

		$this->load->controller('event/theme_' . $this->config->get('config_theme'));

		// Language
		$code = '';

		$language_codes = array_column($settings['languages'], 'language_id', 'code');

		// Language Cookie
		if (isset($this->request->cookie['language']) && array_key_exists($this->request->cookie['language'], $language_codes)) {
			$code = $this->request->cookie['language'];
		}

		$this->load->controller('startup/seo_url');

		// No cookie then use the language in the url
		if (!$code && isset($this->request->get['language']) && array_key_exists($this->request->get['language'], $language_codes)) {
			$code = $this->request->get['language'];
		}

		// Language Detection
		if (!$code && !empty($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
			$detect = '';

			$browser_codes = array();

			$browser_languages = explode(',', strtolower($this->request->server['HTTP_ACCEPT_LANGUAGE']));

			// Try using local to detect the language
			foreach ($browser_languages as $browser_language) {
				$position = strpos($browser_language, ';q=');

				if ($position !== false) {
					$browser_codes[][substr($browser_language, 0, $position)] = (float)substr($browser_language, $position + 3);
				} else {
					$browser_codes[][$browser_language] = 1.0;
				}
			}

			$sort_order = array();

			foreach ($browser_codes as $key => $value) {
				$sort_order[$key] = $value[key($value)];
			}

			array_multisort($sort_order, SORT_ASC, $browser_codes);

			$browser_codes = array_reverse($browser_codes);

			foreach (array_values($browser_codes) as $browser_code) {
				foreach ($settings['languages'] as $key => $value) {
					if ($value['status']) {
						$locale = explode(',', $value['locale']);

						if (in_array(key($browser_code), $locale)) {
							$detect = $value['code'];

							break 2;
						}
					}
				}
			}

			$code = ($detect) ? $detect : '';
		}

		// Language not avaliable then use default
		if (!array_key_exists($code, $language_codes)) {
			$code = $this->config->get('config_language');
		}

		// Set the config language_id
		$this->config->set('config_language_id', isset($language_codes[$code]) ? $language_codes[$code] : 0);
		$this->config->set('config_language', $code);

		// Redirect to the new language
		if (isset($this->request->get['language']) && $this->request->get['language'] != $code) {
			if (isset($this->request->get['route'])) {
				$route = $this->request->get['route'];
			} else {
				$route = $this->config->get('action_default');
			}

			$url = $this->request->get;

			unset($url['_route_']);
			unset($url['route']);
			$url['language'] = $code;

			$this->response->redirect($this->url->link($route, $url));
		}

		$this->load->controller('startup/seo_url/redirect');

		// Set a new language cookie if the code does not match the current one
		if (!isset($this->request->cookie['language']) || $this->request->cookie['language'] != $code) {
			setcookie('language', $code, time() + 60 * 60 * 24 * 30, '/', $this->request->server['HTTP_HOST']);
		}

		// Replace the default language object
		$language = new \Language($code);
		$language->load($code);
		$this->registry->set('language', $language);
	}
}