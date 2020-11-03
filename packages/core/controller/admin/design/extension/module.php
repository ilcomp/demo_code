<?php
namespace Controller\Design\Extension;

class Module extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('design/extension/module');

		$this->load->model('core/extension');

		$this->load->model('core/module');

		$this->getList();
	}

	public function install() {
		$this->load->language('design/extension/module');

		$this->load->model('core/extension');

		$this->load->model('core/module');

		if ($this->validate()) {
			$this->model_core_extension->install('module', $this->request->get['extension']);

			$this->load->model('core/user_group');

			$this->model_core_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/' . $this->request->get['extension']);
			$this->model_core_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/' . $this->request->get['extension']);

			// Call install method if it exsits
			$this->load->controller('extension/module/' . $this->request->get['extension'] . '/install');

			$this->session->data['success'] = $this->language->get('text_success');
		} else {
			$this->session->data['error'] = $this->error['warning'];
		}

		$this->getList();
	}

	public function uninstall() {
		$this->load->language('design/extension/module');

		$this->load->model('core/extension');

		$this->load->model('core/module');

		if ($this->validate()) {
			$this->model_core_extension->uninstall('module', $this->request->get['extension']);

			$this->model_core_module->deleteModulesByCode($this->request->get['extension']);

			// Call uninstall method if it exsits
			$this->load->controller('extension/module/' . $this->request->get['extension'] . '/uninstall');

			$this->session->data['success'] = $this->language->get('text_success');
		}

		$this->getList();
	}

	public function add() {
		$this->load->language('design/extension/module');

		$this->load->model('core/extension');

		$this->load->model('core/module');

		if ($this->validate()) {
			$this->load->language('module' . '/' . $this->request->get['extension']);

			$this->model_core_module->addModule($this->request->get['extension'], $this->language->get('heading_title'));

			$this->session->data['success'] = $this->language->get('text_success');
		}

		$this->getList();
	}

	public function delete() {
		$this->load->language('design/extension/module');

		$this->load->model('core/extension');

		$this->load->model('core/module');

		if (isset($this->request->get['module_id']) && $this->validate()) {
			$this->model_core_module->deleteModule($this->request->get['module_id']);

			$this->session->data['success'] = $this->language->get('text_success');
		}

		$this->getList();
	}

	protected function getList() {
		$data['text_layout'] = sprintf($this->language->get('text_layout'), $this->url->link('design/layout', 'user_token=' . $this->session->data['user_token']));

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

		$extensions = $this->model_core_extension->getInstalled('module');

		foreach ($extensions as $key => $value) {
			if (!is_file(DIR_CONTROLLER . 'extension/module/' . $value . '.php') && !is_file(DIR_CONTROLLER . 'module/' . $value . '.php')) {
				$this->model_core_extension->uninstall('module', $value);

				unset($extensions[$key]);

				$this->model_core_module->deleteModulesByCode($value);
			}
		}

		$data['extensions'] = array();

		// Create a new language container so we don't pollute the current one
		$language = new \Language($this->config->get('config_language'));

		// Compatibility code for old extension folders
		$files = glob(DIR_CONTROLLER . 'extension/module/*.php');

		if ($files) {
			foreach ($files as $file) {
				$extension = basename($file, '.php');

				$this->load->language('extension/module/' . $extension, 'extension');

				$module_data = array();

				$modules = $this->model_core_module->getModulesByCode($extension);

				foreach ($modules as $module) {
					if ($module['setting']) {
						$setting_info = json_decode($module['setting'], true);
					} else {
						$setting_info = array();
					}

					$module_data[] = array(
						'module_id' => $module['module_id'],
						'name'      => $module['name'],
						'status'    => (isset($setting_info['status']) && $setting_info['status']) ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
						'edit'      => $this->url->link('extension/module/' . $extension, 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $module['module_id']),
						'delete'    => $this->url->link('design/extension/module/delete', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $module['module_id'])
					);
				}

				$data['extensions'][] = array(
					'name'      => $this->language->get('extension')->get('heading_title'),
					'status'    => $this->config->get('module_' . $extension . '_status') ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
					'module'    => $module_data,
					'install'   => $this->url->link('design/extension/module/install', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension),
					'uninstall' => $this->url->link('design/extension/module/uninstall', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension),
					'installed' => in_array($extension, $extensions),
					'edit'      => $this->url->link('extension/module/' . $extension, 'user_token=' . $this->session->data['user_token'])
				);
			}
		}

		$sort_order = array();

		foreach ($data['extensions'] as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $data['extensions']);

		$data['content'] = $this->load->view('design/extension/module', $data);

		if ($this->request->server['HTTP_ACCEPT'] == 'text/html') {
			$this->response->setOutput($data['content']);
		} else {
			$this->response->setOutput($this->load->controller('common/template', $data));
		}
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'design/extension/module')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}