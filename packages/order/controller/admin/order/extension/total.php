<?php
namespace Controller\Order\Extension;

class Total extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('order/extension/total');

		$this->load->model('core/extension');

		$this->getList();
	}

	public function install() {
		$this->load->language('order/extension/total');

		$this->load->model('core/extension');

		if ($this->validate()) {
			$this->model_core_extension->install('total', $this->request->get['extension']);

			$this->load->model('core/user_group');

			$this->model_core_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/total/' . $this->request->get['extension']);
			$this->model_core_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/total/' . $this->request->get['extension']);

			$this->load->controller('extension/total/' . $this->request->get['extension'] . '/install');

			$this->session->data['success'] = $this->language->get('text_success');
		}

		$this->getList();
	}

	public function uninstall() {
		$this->load->language('order/extension/total');

		$this->load->model('core/extension');

		if ($this->validate()) {
			$this->model_core_extension->uninstall('total', $this->request->get['extension']);

			$this->load->controller('extension/total/' . $this->request->get['extension'] . '/uninstall');

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

		$extensions = $this->model_core_extension->getInstalled('total');

		foreach ($extensions as $key => $value) {
			if (!is_file(DIR_CONTROLLER . 'extension/total/' . $value . '.php') && !is_file(DIR_CONTROLLER . 'total/' . $value . '.php')) {
				$this->model_core_extension->uninstall('total', $value);

				unset($extensions[$key]);
			}
		}

		$data['extensions'] = array();
		
		// Compatibility code for old extension folders
		$files = glob(DIR_CONTROLLER . 'extension/total/*.php');

		if ($files) {
			foreach ($files as $file) {
				$extension = basename($file, '.php');

				$this->load->language('extension/total/' . $extension, 'extension');

				$data['extensions'][] = array(
					'name'       => $this->language->get('extension')->get('heading_title'),
					'status'     => $this->config->get('total_' . $extension . '_status') ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
					'sort_order' => $this->config->get('total_' . $extension . '_sort_order'),
					'install'    => $this->url->link('order/extension/total/install', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension),
					'uninstall'  => $this->url->link('order/extension/total/uninstall', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension),
					'installed'  => in_array($extension, $extensions),
					'edit'       => $this->url->link('extension/total/' . $extension)
				);
			}
		}

		$data['content'] = $this->load->view('order/extension/total', $data);

		if ($this->request->server['HTTP_ACCEPT'] == 'text/html') {
			$this->response->setOutput($data['content']);
		} else {
			$this->response->setOutput($this->load->controller('common/template', $data));
		}
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'order/extension/total')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}