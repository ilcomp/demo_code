<?php
namespace Controller\Extension\Extension;

class Analytics extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/extension/analytics');

		$this->load->model('core/extension');

		$this->getList();
	}

	public function install() {
		$this->load->language('extension/extension/analytics');

		$this->load->model('core/extension');

		if ($this->validate()) {
			$this->model_core_extension->install('analytics', $this->request->get['extension']);

			$this->load->model('core/user_group');

			$this->model_core_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/analytics/' . $this->request->get['extension']);
			$this->model_core_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/analytics/' . $this->request->get['extension']);

			// Call install method if it exsits
			$this->load->controller('extension/analytics/' . $this->request->get['extension'] . '/install');

			$this->session->data['success'] = $this->language->get('text_success');
		}

		$this->getList();
	}

	public function uninstall() {
		$this->load->language('extension/extension/analytics');

		$this->load->model('core/extension');

		if ($this->validate()) {
			$this->model_core_extension->uninstall('analytics', $this->request->get['extension']);

			// Call uninstall method if it exsits
			$this->load->controller('extension/analytics/' . $this->request->get['extension'] . '/uninstall');

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

		$extensions = $this->model_core_extension->getInstalled('analytics');

		foreach ($extensions as $key => $value) {
			if (!is_file(DIR_CONTROLLER . 'extension/analytics/' . $value . '.php') && !is_file(DIR_CONTROLLER . 'analytics/' . $value . '.php')) {
				$this->model_core_extension->uninstall('analytics', $value);

				unset($extensions[$key]);
			}
		}
		
		$this->load->model('core/store');
		$this->load->model('core/setting');

		$stores = $this->model_core_store->getStores();
		
		$data['extensions'] = array();

		// Compatibility code for old extension folders
		$files = glob(DIR_CONTROLLER . 'extension/analytics/*.php');

		if ($files) {
			foreach ($files as $file) {
				$extension = basename($file, '.php');
				
				// Compatibility code for old extension folders
				$this->load->language('extension/analytics/' . $extension, 'extension');
				
				$store_data = array();

				$store_data[] = array(
					'name'   => $this->config->get('config_name'),
					'edit'   => $this->url->link('extension/analytics/' . $extension, 'user_token=' . $this->session->data['user_token'] . '&store_id=0'),
					'status' => $this->config->get('analytics_' . $extension . '_status') ? $this->language->get('text_enabled') : $this->language->get('text_disabled')
				);
				
				foreach ($stores as $store) {
					$store_data[] = array(
						'name'   => $store['name'],
						'edit'   => $this->url->link('extension/analytics/' . $extension, 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $store['store_id']),
						'status' => $this->model_core_setting->getSettingValue('analytics_' . $extension . '_status', $store['store_id']) ? $this->language->get('text_enabled') : $this->language->get('text_disabled')
					);
				}

				$data['extensions'][] = array(
					'name'      => $this->language->get('extension')->get('heading_title'),
					'install'   => $this->url->link('extension/extension/analytics/install', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension),
					'uninstall' => $this->url->link('extension/extension/analytics/uninstall', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension),
					'installed' => in_array($extension, $extensions),
					'store'     => $store_data
				);
			}
		}

		$this->response->setOutput($this->load->view('extension/extension/analytics', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/extension/analytics')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
