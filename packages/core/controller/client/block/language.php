<?php
namespace Controller\Block;

class Language extends Controller {
	public function index() {
		$this->load->language('block/language');

		$data['code'] = $this->config->get('config_language');

		$url_data = $this->request->get;

		if (isset($url_data['route'])) {
			$route = $url_data['route'];
		} else {
			$route = $this->config->get('action_default');
		}

		unset($url_data['_route_']);
		unset($url_data['route']);
		unset($url_data['language']);

		$url = '';

		if ($url_data) {
			$url = '&' . urldecode(http_build_query($url_data));
		}

		$data['languages'] = array();

		$this->load->model('localisation/language');
		$this->load->model('tool/image');

		$results = $this->model_localisation_language->getLanguages();

		foreach ($results as $result) {
			if ($result['status']) {
				$result['image'] = $this->model_tool_image->link('language/' . $result['code'] . '.png');
				$result['href'] = $this->url->link('block/language/language', 'code=' . $result['code'] . '&redirect=' . htmlspecialchars($this->url->link($route, 'language_id=' . $result['language_id'] . '&language=' . $result['code'] . $url), ENT_COMPAT, 'UTF-8'));

				$data['languages'][$result['code']] = $result;
			}
		}

		return $this->load->view('block/language', $data);
	}

	public function language() {
		if (isset($this->request->get['code'])) {
			$code = $this->request->get['code'];
		} else {
			$code = $this->config->get('config_language');
		}

		if (isset($this->request->get['redirect'])) {
			$redirect = htmlspecialchars_decode($this->request->get['redirect'], ENT_COMPAT);
		} else {
			$redirect = '';
		}

		setcookie('language', $code, time() + 60 * 60 * 24 * 30, '/', $this->request->server['HTTP_HOST']);

		if ($redirect && substr($redirect, 0, strlen($this->config->get('config_url'))) == $this->config->get('config_url')) {
			$this->response->redirect($redirect);
		} else {
			$this->response->redirect($this->url->link($this->config->get('action_default'), 'language=' . $code));
		}
	}
}