<?php
namespace Controller\Design\Extension;

class Theme extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('design/extension/theme');

		$this->load->model('core/extension');

		$this->getList();
	}

	public function install() {
		$this->load->language('design/extension/theme');

		$this->load->model('core/extension');

		if ($this->validate()) {
			$this->model_core_extension->install('theme', $this->request->get['extension']);

			$this->load->model('core/user_group');

			$this->model_core_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/theme/' . $this->request->get['extension']);
			$this->model_core_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/theme/' . $this->request->get['extension']);

			// Call install method if it exsits
			$this->load->controller('extension/theme/' . $this->request->get['extension'] . '/install');

			$this->session->data['success'] = $this->language->get('text_success');
		}

		$this->getList();
	}

	public function uninstall() {
		$this->load->language('design/extension/theme');

		$this->load->model('core/extension');

		if ($this->validate()) {
			$this->model_core_extension->uninstall('theme', $this->request->get['extension']);

			// Call uninstall method if it exsits
			$this->load->controller('extension/theme/' . $this->request->get['extension'] . '/uninstall');

			$this->session->data['success'] = $this->language->get('text_success');
		}
		
		$this->getList();
	}

	protected function getList() {
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$extensions = $this->model_core_extension->getInstalled('theme');

		foreach ($extensions as $key => $value) {
			if (!is_file(DIR_CONTROLLER . 'extension/theme/' . $value . '.php') && !is_file(DIR_CONTROLLER . 'theme/' . $value . '.php')) {
				$this->model_core_extension->uninstall('theme', $value);

				unset($extensions[$key]);
			}
		}

		$this->load->model('core/store');
		$this->load->model('core/setting');

		$stores = $this->model_core_store->getStores();

		$data['extensions'] = array();
		
		// Compatibility code for old extension folders
		$files = glob(DIR_CONTROLLER . 'extension/theme/*.php');

		if ($files) {
			foreach ($files as $file) {
				$extension = basename($file, '.php');
				
				$this->load->language('extension/theme/' . $extension, 'extension');
					
				$store_data = array();
				
				$store_data[] = array(
					'name'   => $this->config->get('config_name'),
					'edit'   => $this->url->link('extension/theme/' . $extension, 'user_token=' . $this->session->data['user_token'] . '&store_id=0'),
					'status' => $this->config->get('theme_' . $extension . '_status') ? $this->language->get('text_enabled') : $this->language->get('text_disabled')
				);
									
				foreach ($stores as $store) {
					$store_data[] = array(
						'name'   => $store['name'],
						'edit'   => $this->url->link('extension/theme/' . $extension, 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $store['store_id']),
						'status' => $this->model_core_setting->getSettingValue('theme_' . $extension . '_status', $store['store_id']) ? $this->language->get('text_enabled') : $this->language->get('text_disabled')
					);
				}
				
				$data['extensions'][] = array(
					'name'      => $this->language->get('extension')->get('heading_title'),
					'install'   => $this->url->link('design/extension/theme/install', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension),
					'uninstall' => $this->url->link('design/extension/theme/uninstall', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension),
					'installed' => in_array($extension, $extensions),
					'store'     => $store_data
				);
			}
		}

		$data['content'] = $this->load->view('design/extension/theme', $data);

		if ($this->request->server['HTTP_ACCEPT'] == 'text/html') {
			$this->response->setOutput($data['content']);
		} else {
			$this->response->setOutput($this->load->controller('common/template', $data));
		}
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'design/extension/theme')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function image() {
		$this->load->model('tool/image');

		$theme = basename($this->request->get['theme']);

		$pathname = $theme . '/theme.png';

		if (is_file(DIR_TEMPLATE . $pathname)) {
			copy(DIR_TEMPLATE . $pathname, DIR_THEME . $pathname);

			$this->response->setOutput('theme/' . $pathname);
		} else {
			$this->response->setOutput($this->model_tool_image->link('no_image.png'));
		}
	}
}