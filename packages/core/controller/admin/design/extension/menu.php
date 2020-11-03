<?php
namespace Controller\Design\Extension;

class Menu extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('design/extension/menu');

		$this->load->model('core/extension');

		$this->getList();
	}

	public function install() {
		$this->load->language('design/extension/menu');

		$this->load->model('core/extension');

		if ($this->validate()) {
			$this->model_core_extension->install('menu', $this->request->get['extension']);

			$this->load->model('core/user_group');

			$this->model_core_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/menu/' . $this->request->get['extension']);
			$this->model_core_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/menu/' . $this->request->get['extension']);

			// Call install method if it exsits
			$this->load->controller('extension/menu/' . $this->request->get['extension'] . '/install');

			$this->session->data['success'] = $this->language->get('text_success');
		}

		$this->getList();
	}

	public function uninstall() {
		$this->load->language('design/extension/menu');

		$this->load->model('core/extension');

		if ($this->validate()) {
			$this->model_core_extension->uninstall('menu', $this->request->get['extension']);

			// Call uninstall method if it exsits
			$this->load->controller('extension/menu/' . $this->request->get['extension'] . '/uninstall');

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

		$extensions = $this->model_core_extension->getInstalled('menu');

		foreach ($extensions as $key => $value) {
			if (!is_file(DIR_CONTROLLER . 'extension/menu/' . $value . '.php') && !is_file(DIR_CONTROLLER . 'menu/' . $value . '.php')) {
				$this->model_core_extension->uninstall('menu', $value);

				unset($extensions[$key]);
			}
		}

		$data['extensions'] = array();
		
		// Compatibility code for old extension folders
		$files = glob(DIR_CONTROLLER . 'extension/menu/*.php');

		if ($files) {
			foreach ($files as $file) {
				$extension = basename($file, '.php');

				$this->load->language('extension/menu/' . $extension, 'extension');

				$data['extensions'][] = array(
					'name'      => $this->language->get('extension')->get('heading_title'),
					'status'    => $this->config->get('menu_' . $extension . '_status') ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
					'install'   => $this->url->link('design/extension/menu/install', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension),
					'uninstall' => $this->url->link('design/extension/menu/uninstall', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension),
					'installed' => in_array($extension, $extensions),
					'edit'      => $this->url->link('extension/menu/' . $extension, 'user_token=' . $this->session->data['user_token'])
				);
			}
		}

		$sort_order = array();

		foreach ($data['extensions'] as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $data['extensions']);

		$data['content'] = $this->load->view('design/extension/menu', $data);

		if ($this->request->server['HTTP_ACCEPT'] == 'text/html') {
			$this->response->setOutput($data['content']);
		} else {
			$this->response->setOutput($this->load->controller('common/template', $data));
		}
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'design/extension/menu')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
