<?php
namespace Controller\Design;

class Theme extends \Controller {
	public function index() {
		$this->load->language('design/theme');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('design/theme')
		);

		$data['user_token'] = $this->session->data['user_token'];

		$data['stores'] = array();

		$this->load->model('core/store');

		$results = $this->model_core_store->getStores();
		
		foreach ($results as $result) {
			$data['stores'][] = array(
				'store_id' => $result['store_id'],
				'name'     => $result['name']
			);
		}

		$data['content'] = $this->load->view('design/theme', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	public function history() {
		$this->load->language('design/theme');

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['histories'] = array();

		$this->load->model('design/theme');
		$this->load->model('core/store');

		$history_total = $this->model_design_theme->getTotalThemes();

		$results = $this->model_design_theme->getThemes(($page - 1) * 10, 10);

		foreach ($results as $result) {
			$store_info = $this->model_core_store->getStore($result['store_id']);

			if ($store_info) {
				$store = $store_info['name'];
			} else {
				$store = '';
			}

			$data['histories'][] = array(
				'store_id'   => $result['store_id'],
				'store'      => ($result['store_id'] ? $store : $this->language->get('text_default')),
				'route'      => $result['route'],
				'theme'      => $result['theme'],
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'edit'       => $this->url->link('design/theme/template'),
				'delete'     => $this->url->link('design/theme/delete', 'user_token=' . $this->session->data['user_token'] . '&theme_id=' . $result['theme_id'])
			);
		}

		$data['pagination'] = $this->load->controller('block/pagination', array(
			'total' => $history_total,
			'page'  => $page,
			'limit' => 10,
			'url'   => $this->url->link('design/theme/history', 'user_token=' . $this->session->data['user_token'] . '&page={page}')
		));

		$data['results'] = $this->load->controller('block/pagination/result', array(
			'total' => $history_total,
			'page'  => $page,
			'limit' => 10
		));

		$this->response->setOutput($this->load->view('design/theme_history', $data));
	}

	public function path() {
		$this->load->language('design/theme');

		$json = array();

		if (isset($this->request->get['store_id'])) {
			$store_id = $this->request->get['store_id'];
		} else {
			$store_id = 0;
		}

		$this->load->model('core/setting');

		$theme = $this->model_core_setting->getSettingValue('config_theme', $store_id);

		// This is only here for compatibility with old themes.
		if ($theme == 'theme_default') {
			$theme = $this->model_core_setting->getSettingValue('theme_default_directory', $store_id);
		}

		if (isset($this->request->get['path'])) {
			$path = $this->request->get['path'];
		} else {
			$path = '';
		}

		if (substr(str_replace('\\', '/', realpath(DIR_TEMPLATE . 'client_default/template/' . $path)), 0, strlen(DIR_TEMPLATE)) == DIR_TEMPLATE) {
			$path_data = array();

			// We grab the files from the default theme directory first as the custom themes drops back to the default theme if selected theme files can not be found.
			$files = glob(rtrim(DIR_TEMPLATE . 'client_{default,' . $theme . '}/template/' . $path, '/') . '/*', GLOB_BRACE);

			if ($files) {
				foreach($files as $file) {
					if (!in_array(basename($file), $path_data))  {
						if (is_dir($file)) {
							$json['directory'][] = array(
								'name' => basename($file),
								'path' => trim($path . '/' . basename($file), '/')
							);
						}

						if (is_file($file)) {
							$json['file'][] = array(
								'name' => basename($file),
								'path' => trim($path . '/' . basename($file), '/')
							);
						}

						$path_data[] = basename($file);
					}
				}
			}
		}

		if (!empty($this->request->get['path'])) {
			$json['back'] = array(
				'name' => $this->language->get('button_back'),
				'path' => urlencode(substr($path, 0, strrpos($path, '/'))),
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}

	public function template() {
		$this->load->language('design/theme');

		$json = array();

		if (isset($this->request->get['store_id'])) {
			$store_id = $this->request->get['store_id'];
		} else {
			$store_id = 0;
		}

		$this->load->model('core/setting');

		$theme = $this->model_core_setting->getSettingValue('config_theme', $store_id);

		// This is only here for compatibility with old themes.
		if ($theme == 'theme_default') {
			$theme = $this->model_core_setting->getSettingValue('theme_default_directory', $store_id);
		}

		if (isset($this->request->get['path'])) {
			$path = $this->request->get['path'];
		} else {
			$path = '';
		}

		$this->load->model('design/theme');

		$theme_info = $this->model_design_theme->getTheme($store_id, $theme, $path);

		if ($theme_info) {
			$json['code'] = html_entity_decode($theme_info['code']);
		} elseif (is_file(DIR_TEMPLATE . $theme . '/template/' . $path) && (substr(str_replace('\\', '/', realpath(DIR_TEMPLATE . $theme . '/template/' . $path)), 0, strlen(DIR_TEMPLATE)) == DIR_TEMPLATE)) {
			$json['code'] = file_get_contents(DIR_TEMPLATE . $theme . '/template/' . $path);
		} elseif (is_file(DIR_TEMPLATE . 'client_default/template/' . $path) && (substr(str_replace('\\', '/', realpath(DIR_TEMPLATE . 'client_default/template/' . $path)), 0, strlen(DIR_TEMPLATE)) == DIR_TEMPLATE)) {
			$json['code'] = file_get_contents(DIR_TEMPLATE . 'client_default/template/' . $path);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}

	public function save() {
		$this->load->language('design/theme');

		$json = array();

		if (isset($this->request->get['store_id'])) {
			$store_id = $this->request->get['store_id'];
		} else {
			$store_id = 0;
		}

		$this->load->model('core/setting');

		$theme = $this->model_core_setting->getSettingValue('config_theme', $store_id);

		// This is only here for compatibility with old themes.
		if ($theme == 'theme_default') {
			$theme = $this->model_core_setting->getSettingValue('theme_default_directory', $store_id);
		}

		if (isset($this->request->get['path'])) {
			$path = $this->request->get['path'];
		} else {
			$path = '';
		}

		// Check user has permission
		if (!$this->user->hasPermission('modify', 'design/theme')) {
			$json['error'] = $this->language->get('error_permission');
		} 

		if (substr($path, -5) != '.twig') {
			$json['error'] = $this->language->get('error_twig');
		} 

		if (!$json) {
			$this->load->model('design/theme');

			$pos = strpos($path, '.');

			$this->model_design_theme->editTheme($store_id, $theme, ($pos !== false) ? substr($path, 0, $pos) : $path, $this->request->post['code']);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}

	public function reset() {
		$this->load->language('design/theme');

		$json = array();

		if (isset($this->request->get['store_id'])) {
			$store_id = $this->request->get['store_id'];
		} else {
			$store_id = 0;
		}

		$this->load->model('core/setting');

		$theme = $this->model_core_setting->getSettingValue('config_theme', $store_id);
		
		// This is only here for compatibility with old themes.
		if ($theme == 'theme_default') {
			$theme = $this->model_core_setting->getSettingValue('theme_default_directory', $store_id);
		}

		if (isset($this->request->get['path'])) {
			$path = $this->request->get['path'];
		} else {
			$path = '';
		}

		if (is_file(DIR_APP . 'theme/' . $theme . '/template/' . $path) && (substr(str_replace('\\', '/', realpath(DIR_APP . 'theme/' . $theme . '/template/' . $path)), 0, strlen(DIR_APP . 'view')) == DIR_APP . 'view')) {
			$json['code'] = file_get_contents(DIR_APP . 'theme/' . $theme . '/template/' . $path);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}

	public function delete() {
		$this->load->language('design/theme');

		$json = array();

		if (isset($this->request->get['theme_id'])) {
			$theme_id = $this->request->get['theme_id'];
		} else {
			$theme_id = 0;
		}

		// Check user has permission
		if (!$this->user->hasPermission('modify', 'design/theme')) {
			$json['error'] = $this->language->get('error_permission');
		} 

		if (!$json) {
			$this->load->model('design/theme');

			$this->model_design_theme->deleteTheme($theme_id);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}
}
