<?php
namespace Model\Reqistry;

class SeoUrl extends \Model {
	private $regex = array();

	public function getRequest() {
		// Add rewrite to url class
		if ($this->config->get('system_seo_url')) {
			$this->url->addRewrite($this);
		}

		// Load all regexes in the var so we are not accessing the db so much.
		$query = $this->db->query("SELECT regex FROM " . DB_PREFIX . "seo_regex GROUP BY regex ORDER BY sort_order ASC");

		$this->regex = $query->rows;

		// Decode URL
		if (isset($this->request->get['_route_'])) {
			$parts = explode('/', $this->request->get['_route_']);

			// remove any empty arrays from trailing
			if (utf8_strlen(end($parts)) == 0) {
				array_pop($parts);
			}

			foreach ($parts as $part) {
				$query = $this->db->query("SELECT `push` FROM " . DB_PREFIX . "seo_url WHERE keyword = '" . $this->db->escape($part) . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

				if ($query->num_rows) {
					foreach ($query->rows as $result) {
						parse_str($result['push'], $data);

						foreach ($data as $key => $value) {
							$this->request->get[$key] = $value;
						}
					}

				} else {
					$this->request->get['route'] = 'error/not_found';

					return;
				}
			}
		}

		if (isset($this->request->get['language']) && $this->request->get['language'] != $this->config->get('config_language')) {
			$code = $this->request->get['language'];

			$this->load->model('localisation/language');

			$languages = $this->model_localisation_language->getLanguages();

			$language_codes = array_column($languages, 'language_id', 'code');

			if (!array_key_exists($code, $language_codes)) {
				$code = $this->config->get('config_language');
			}

			$language = new Language($code);
			$language->load($code);
			$this->registry->set('language', $language);

			// Set the config language_id
			$this->config->set('config_language_id', $language_codes[$code]);
			$this->config->set('config_language', $code);
		}

		if (isset($this->request->get['route'])) {
			$url = $this->request->get;
			$ulr2 = isset($this->request->get['_route_']) ? $this->request->get['_route_'] : '';
			unset($url['_route_']);
			unset($url['route']);

			$link = $this->url->link($this->request->get['route'], urldecode(http_build_query($url)));
			$url_info = parse_url(str_replace(array('index.php', '&amp;'), array('', '&'), $link));

			if (trim($url_info['path'],'/') && trim($url_info['path'],'/') != trim($ulr2,'/')) {
				if ($this->config->get('system_seo_url_consolidate') == '404')
					$this->request->get['route'] = 'error/not_found';
				elseif ($this->config->get('system_seo_url_consolidate') == '301')
					$this->response->redirect($link, 301);
				else
					$this->response->addHeader('Link: <' . $link . '>; rel="canonical"');
			}
		}
	}

	public function rewrite($link) {
		$url = false;

		$url_info = parse_url(str_replace('&amp;', '&', $link));

		parse_str($url_info['query'], $data);

		$language_id = false;

		if (isset($data['language'])) {
			$languages = $this->model_localisation_language->getLanguages();

			$language_codes = array_column($languages, 'language_id', 'code');

			if (isset($language_codes[$data['language']])) {
				$language_id = $language_codes[$data['language']];
			}
		}

		if (!$language_id) {
			$language_id = $this->config->get('config_language_id');
			$data['language'] = $this->config->get('config_language');
		}

		// General page
		$find = false;
		foreach (array_reverse($this->regex) as $result) {
			if (preg_match('/' . $result['regex'] . '/', $url_info['query'], $matches)) {
				array_shift($matches);

				if (!empty($matches)) {
					$match = array_pop($matches);

					$query = $this->db->query("SELECT `push` FROM " . DB_PREFIX . "seo_url WHERE `query` = '" . $this->db->escape($match) . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "' AND language_id = '" . (int)$language_id . "'");

					if ($query->num_rows) {
						foreach (array_reverse($query->rows) as $seo) {
							parse_str($seo['push'], $find);
							if (isset($find['route'])) unset($find['route']);

							if ($find) {
								$data = array_merge($data, $find);

								$url_info['query'] = urldecode(http_build_query($data));
								break;
							}
						}

						if ($find) break;
					}
				}
			}
		}

		foreach ($this->regex as $result) {
			if (preg_match('/' . $result['regex'] . '/', $url_info['query'], $matches)) {
				array_shift($matches);

				foreach ($matches as $match) {
					$query = $this->db->query("SELECT `keyword` FROM " . DB_PREFIX . "seo_url WHERE `query` = '" . $this->db->escape($match) . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "' AND language_id = '" . (int)$language_id . "'");

					if ($query->num_rows) {
						if ($url === false) $url = '';

						foreach ($query->rows as $seo) {
							if (!empty($seo['keyword'])) {
								$url .= '/' . $seo['keyword'];
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

		if ($url !== false) {
			$query = '';

			if ($data) {
				foreach ($data as $key => $value) {
					$query .= '&' . rawurlencode((string)$key) . '=' . rawurlencode(is_array($value) ? http_build_query($value) : (string)$value);
				}

				if ($query) {
					$query = '?' . str_replace('&', '&amp;', trim(str_replace('%2F', '/', $query), '&'));
				}
			}

			return $url_info['scheme'] . '://' . $url_info['host'] . (isset($url_info['port']) ? ':' . $url_info['port'] : '') . str_replace('/index.php', '', $url_info['path']) . $url . '/' . $query;
		} else {
			return $link;
		}
	}
}
