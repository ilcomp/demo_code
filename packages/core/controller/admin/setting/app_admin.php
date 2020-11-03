<?php
namespace Controller\Setting;

class AppAdmin extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('setting/setting');
		$this->load->language('setting/app_admin');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_core_setting->editSetting('app_admin', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('setting/app_admin', 'user_token=' . $this->session->data['user_token']));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['limit'])) {
			$data['error_limit'] = $this->error['limit'];
		} else {
			$data['error_limit'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('setting/app_admin', 'user_token=' . $this->session->data['user_token'])
		);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['action'] = $this->url->link('setting/app_admin', 'user_token=' . $this->session->data['user_token']);

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('setting/app_admin', 'user_token=' . $this->session->data['user_token']);

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->post['app_admin_theme'])) {
			$data['app_admin_theme'] = $this->request->post['app_admin_theme'];
		} else {
			$data['app_admin_theme'] = $this->config->get('app_admin_theme');
		}

		$data['store_url'] = HTTP_APPLICATION_CLIENT;

		$data['themes'] = array();

		$this->load->model('core/extension');

		$extensions = $this->model_core_extension->getInstalled('theme');

		foreach ($extensions as $code) {
			if (strpos($code, 'admin_') === 0 && $this->config->get('theme_' . $code . '_status')) {
				$this->load->language('extension/theme/' . $code, 'extension');

				$data['themes'][] = array(
					'text'  => $this->language->get('extension')->get('heading_title'),
					'value' => $code
				);
			}
		}

		if (isset($this->request->post['app_admin_language'])) {
			$data['app_admin_language'] = $this->request->post['app_admin_language'];
		} else {
			$data['app_admin_language'] = $this->config->get('app_admin_language');
		}

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['app_admin_limit'])) {
			$data['app_admin_limit'] = $this->request->post['app_admin_limit'];
		} else {
			$data['app_admin_limit'] = $this->config->get('app_admin_limit');
		}

		$images_name = array('list', 'thumb');

		$data['app_admin_image'] = array();

		foreach ($images_name as $value) {
			$data['app_admin_image'][$value]['name'] = $this->language->get('entry_image_' . $value);

			if (isset($this->request->post['app_admin_image_' . $value . '_width'])) {
				$data['app_admin_image'][$value]['width'] = $this->request->post['app_admin_image_' . $value . '_width'];
			} elseif ($this->config->get('app_admin_image_' . $value . '_width')) {
				$data['app_admin_image'][$value]['width'] = $this->config->get('app_admin_image_' . $value . '_width');
			} else {
				$data['app_admin_image'][$value]['width'] = 300;
			}

			if (isset($this->request->post['app_admin_image_' . $value . '_height'])) {
				$data['app_admin_image'][$value]['height'] = $this->request->post['app_admin_image_' . $value . '_height'];
			} elseif ($this->config->get('app_admin_image_' . $value . '_height')) {
				$data['app_admin_image'][$value]['height'] = $this->config->get('app_admin_image_' . $value . '_height');
			} else {
				$data['app_admin_image'][$value]['height'] = 300;
			}
		}

		$data['content'] = $this->load->view('setting/app_admin', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'setting/app_admin')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['app_admin_limit']) {
			$this->error['limit'] = $this->language->get('error_limit');
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}
}
