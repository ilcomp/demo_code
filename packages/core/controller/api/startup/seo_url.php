<?php
namespace Controller\Startup;

class SeoUrl extends \Controller {
	private $links = array();

	public function index() {
		// Add rewrite to URL class
		if ($this->config->get('system_seo_url')) {
			$this->url->addRewrite($this);
		}

		$url_info = parse_url($this->request->server['REQUEST_URI']);

		if (!isset($this->request->get['route']) && !empty($url_info['path']) && preg_match('/^(\/v[\d\.]+)?(\/.*)$/', $url_info['path'], $match)) {
			$this->request->get['version'] = $match[1];
			$this->request->get['route'] = trim($match[0], '/');
		}
	}

	public function rewrite($link) {
		if (isset($this->links[$link]))
			return $this->links[$link];

		$url = '';

		$url_info = parse_url(str_replace('&amp;', '&', $link));

		if ($url_info['scheme']) {
			$url .= $url_info['scheme'];
		}

		$url .= '://';

		if ($url_info['host']) {
			$url .= $url_info['host'];
		}

		if (isset($url_info['port'])) {
			$url .= ':' . $url_info['port'];
		}

		if ($url_info['path']) {
			$url .= str_replace('/index.php', '', rtrim($url_info['path'], '/'));
		}

		// Start replacing the URL query
		$data = array();

		parse_str($url_info['query'], $data);

		$language_id = false;

		if (isset($data['language'])) {
			$this->load->model('localisation/language');

			$languages = $this->model_localisation_language->getLanguages();

			$language_codes = array_column($languages, 'language_id', 'code');

			if (isset($language_codes[$data['language']])) {
				$language_id = $language_codes[$data['language']];
			}
		}

		if (!$language_id) {
			$language_id = $this->config->get('config_language_id');

			$data['language'] = $this->config->get('config_language');

			$url_info['query'] = urldecode(http_build_query($data));
		}

		$url_path = array();

		if (!(empty($data['language']) || $data['language'] == $this->config->get('config_language')))
			$url_path[] = $data['language'];

		unset($data['language']);

		if (!(empty($data['route']) || $data['route'] == $this->config->get('action_default')))
			$url_path[] = $data['route'];

		unset($data['route']);

		$url .= !empty($url_path) ? '/' . implode('/', $url_path) : '';

		$query = '';

		if ($data) {
			foreach ($data as $key => $value) {
				$query .= '&' . rawurlencode((string)$key) . '=' . rawurlencode(is_array($value) ? http_build_query($value) : (string)$value);
			}

			if ($query) {
				$query = '?' . str_replace('&', '&amp;', trim(str_replace('%2F', '/', $query), '&'));
			}
		}

		$this->links[$link] = $url . '/' . $query;

		return $url . '/' . $query;
	}
}
