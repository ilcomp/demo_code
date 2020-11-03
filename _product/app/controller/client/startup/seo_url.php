<?php
namespace Controller\Startup;

class SeoUrl extends \Controller {
	private $regex = array();
	private $links = array();

	public function index() {
		// Add rewrite to URL class
		if ($this->config->get('system_seo_url')) {
			$this->url->addRewrite($this);
		}

		$this->load->model('design/seo_url');
		$this->load->model('design/seo_regex');

		// Load all regexes in the var so we are not accessing the db so much.
		$results = $this->model_design_seo_regex->getSeoRegexes(array('sort' => 'sort_order'));

		$this->regex = array_column($results, 'regex');

		$url_info = parse_url($this->request->server['REQUEST_URI']);

		// Decode URL
		if (!empty($url_info['path'])) {
			$parts = explode('/', trim($url_info['path'], '/'));

			// remove any empty arrays from trailing
			if (utf8_strlen(end($parts)) == 0) {
				array_pop($parts);
			}

			$route = false;

			foreach ($parts as $part_id => $part) {
				$results = $this->model_design_seo_url->getSeoUrlsByKeyword($part, array('store_id' => $this->config->get('config_store_id')));

				if (!empty($results)) {
					foreach ($results as $result) {
						if (!$part_id && strpos($result['query'], 'route=') === 0) {
							$route = substr($result['query'], 6);

							$this->request->get['route'] = $route;
						}

						parse_str($result['push'], $data);

						foreach ($data as $key => $value) {
							if (!$route || $key != 'route')
								$this->request->get[$key] = $value;
						}
					}

				} else {
					$this->request->get['route'] = 'error/not_found';

					return;
				}
			}
		}
	}

	public function redirect() {
		if (isset($this->request->get['route']) && $this->request->get['route'] != 'error/not_found') {
			$url = $this->request->get;
			unset($url['route']);

			$link = $this->url->link($this->request->get['route'], urldecode(http_build_query($url)));
			$url_info = parse_url(str_replace(array('index.php', '&amp;'), array('', '&'), $link));

			$url_info2 = parse_url($this->request->server['REQUEST_URI']);

			if (trim($url_info['path'],'/') != trim($url_info2['path'],'/')) {
				// if ($this->config->get('system_seo_url_consolidate') == '404')
				// 	$this->request->get['route'] = 'error/not_found';
				// elseif ($this->config->get('system_seo_url_consolidate') == '301')
					$this->response->redirect($link, 301);
				// else
				// 	$this->response->addHeader('Link: <' . $link . '>; rel="canonical"');
			}
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

		$this->load->model('design/seo_url');

		$url_path = false;

		foreach ($this->regex as $regex) {
			if (preg_match('/' . $regex . '/', $url_info['query'], $matches)) {
				array_shift($matches);

				foreach ($matches as $match) {
					$results = $this->model_design_seo_url->getSeoUrlsByQuery($match, array('store_id' => $this->config->get('config_store_id'), 'language_id' => $language_id));

					if (!empty($results)) {
						if ($url_path === false) $url_path = array();

						foreach ($results as $seo) {
							if (!empty($seo['keyword'])) {
								$url_path[$seo['query']] = $seo['keyword'];
							}
						}

						parse_str($match, $remove);

						// Remove all the matched url elements
						foreach (array_keys($remove) as $key) {
							if (isset($data[$key])) {
								unset($data[$key]);
							}
						}
					}
				}
			}
		}

		if ($url_path !== false) {
			$url .= !empty($url_path) ? '/' . implode('/', $url_path) : '';

			if ($data) {
				foreach ($data as $key => $value) {
					if ($value == '')
						unset($data[$key]);
				}

				$query = http_build_query($data);

				if ($query) {
					$query = '?' . str_replace('&', '&amp;', trim(str_replace('%2F', '/', $query), '&'));
				}
			} else {
				$query = '';
			}

			$this->links[$link] = $url . '/' . $query;

			return $url . '/' . $query;
		} else {
			$this->links[$link] = $link;

			return $link;
		}
	}
}