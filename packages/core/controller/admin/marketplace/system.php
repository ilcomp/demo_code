<?php
namespace Controller\Marketplace;

class System extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('marketplace/system');

		$this->load->model('core/extension');

		$this->getList();
	}

	public function update() {
		$this->load->language('marketplace/system');

		$this->load->model('core/extension');

		if ($this->validate()) {
			// Call update method if it exsits
			$this->load->controller('extension/system/' . $this->request->get['extension'] . '/update');

			$this->session->data['success'] = $this->language->get('text_success');
		}

		$this->getList();
	}

	public function install() {
		$this->load->language('marketplace/system');

		$this->load->model('core/extension');

		if ($this->validate()) {
			$this->model_core_extension->install('system', $this->request->get['extension']);

			$this->load->model('core/user_group');

			$this->model_core_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/system/' . $this->request->get['extension']);
			$this->model_core_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/system/' . $this->request->get['extension']);

			// Call install method if it exsits
			$this->load->controller('extension/system/' . $this->request->get['extension'] . '/install');

			$this->session->data['success'] = $this->language->get('text_success');
		}

		$this->getList();
	}

	public function uninstall() {
		$this->load->language('marketplace/system');

		$this->load->model('core/extension');

		if ($this->validate()) {
			$this->model_core_extension->uninstall('system', $this->request->get['extension']);

			// Call uninstall method if it exsits
			$this->load->controller('extension/system/' . $this->request->get['extension'] . '/uninstall');

			$this->session->data['success'] = $this->language->get('text_success');
		}

		$this->getList();
	}

	protected function getList() {
		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('marketplace/system', 'user_token=' . $this->session->data['user_token'])
		);

		$data['user_token'] = $this->session->data['user_token'];

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

		$extensions = $this->model_core_extension->getInstalled('system');

		foreach ($extensions as $key => $value) {
			if (!is_file(DIR_CONTROLLER . 'extension/system/' . $value . '.php') && !is_file(DIR_CONTROLLER . 'system/' . $value . '.php')) {
				$this->model_core_extension->uninstall('system', $value);

				unset($extensions[$key]);
			}
		}
		
		$this->load->model('core/store');
		$this->load->model('core/setting');

		$stores = $this->model_core_store->getStores();
		
		$data['extensions'] = array();

		// Compatibility code for old extension folders
		$files = glob(DIR_CONTROLLER . 'extension/system/*.php');

		if ($files) {
			foreach ($files as $file) {
				$extension = basename($file, '.php');
				
				// Compatibility code for old extension folders
				$this->load->language('extension/system/' . $extension, 'extension');
				
				$store_data = array();

				$store_data[] = array(
					'name'   => $this->config->get('config_name'),
					'edit'   => $this->url->link('extension/system/' . $extension, 'user_token=' . $this->session->data['user_token'] . '&store_id=0'),
					'status' => $this->config->get('' . $extension . '_status') ? $this->language->get('text_enabled') : $this->language->get('text_disabled')
				);
				
				foreach ($stores as $store) {
					$store_data[] = array(
						'name'   => $store['name'],
						'edit'   => $this->url->link('extension/system/' . $extension, 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $store['store_id']),
						'status' => $this->model_core_setting->getSettingValue('' . $extension . '_status', $store['store_id']) ? $this->language->get('text_enabled') : $this->language->get('text_disabled')
					);
				}

				$data['extensions'][] = array(
					'name'      => $this->language->get('extension')->get('heading_title'),
					'update'   => $this->url->link('marketplace/system/update', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension),
					'install'   => $this->url->link('marketplace/system/install', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension),
					'uninstall' => $this->url->link('marketplace/system/uninstall', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension),
					'installed' => in_array($extension, $extensions),
					'store'     => $store_data
				);
			}
		}

		$data['content'] = $this->load->view('marketplace/system', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'marketplace/system')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
