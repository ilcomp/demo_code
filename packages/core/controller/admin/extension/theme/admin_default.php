<?php
namespace Controller\Extension\Theme;

class AdminDefault extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/theme/admin_default');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_core_setting->editSetting('theme_admin_default', $this->request->post, $this->request->get['store_id']);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('design/extension/theme'));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('design/extension/theme')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/theme/admin_default', 'store_id=' . $this->request->get['store_id'])
		);

		$data['action'] = $this->url->link('extension/theme/admin_default', 'store_id=' . $this->request->get['store_id']);

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('design/extension/theme');

		if (isset($this->request->get['store_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$setting_info = $this->model_core_setting->getSetting('theme_admin_default', $this->request->get['store_id']);
		}

		if (isset($this->request->post['theme_admin_default_status'])) {
			$data['theme_admin_default_status'] = $this->request->post['theme_admin_default_status'];
		} elseif (isset($setting_info['theme_admin_default_status'])) {
			$data['theme_admin_default_status'] = $setting_info['theme_admin_default_status'];
		} else {
			$data['theme_admin_default_status'] = '';
		}

		$images_name = array('location');

		$data['theme_admin_default_image'] = array();

		foreach ($images_name as $value) {
			$data['theme_admin_default_image'][$value]['name'] = $this->language->get('entry_image_' . $value);

			if (isset($this->request->post['theme_admin_default_image_' . $value . '_width'])) {
				$data['theme_admin_default_image'][$value]['width'] = $this->request->post['theme_admin_default_image_' . $value . '_width'];
			} elseif (isset($setting_info['theme_admin_default_image_' . $value . '_width'])) {
				$data['theme_admin_default_image'][$value]['width'] = $setting_info['theme_admin_default_image_' . $value . '_width'];
			} else {
				$data['theme_admin_default_image'][$value]['width'] = 300;
			}

			if (isset($this->request->post['theme_admin_default_image_' . $value . '_height'])) {
				$data['theme_admin_default_image'][$value]['height'] = $this->request->post['theme_admin_default_image_' . $value . '_height'];
			} elseif (isset($setting_info['theme_admin_default_image_' . $value . '_height'])) {
				$data['theme_admin_default_image'][$value]['height'] = $setting_info['theme_admin_default_image_' . $value . '_height'];
			} else {
				$data['theme_admin_default_image'][$value]['height'] = 300;
			}
		}
		
		// if (isset($this->request->post['theme_admin_default_image_cart_width'])) {
		// 	$data['theme_admin_default_image_cart_width'] = $this->request->post['theme_admin_default_image_cart_width'];
		// } elseif (isset($setting_info['theme_admin_default_image_cart_width'])) {
		// 	$data['theme_admin_default_image_cart_width'] = $setting_info['theme_admin_default_image_cart_width'];
		// } else {
		// 	$data['theme_admin_default_image_cart_width'] = 47;
		// }
		
		// if (isset($this->request->post['theme_admin_default_image_cart_height'])) {
		// 	$data['theme_admin_default_image_cart_height'] = $this->request->post['theme_admin_default_image_cart_height'];
		// } elseif (isset($setting_info['theme_admin_default_image_cart_height'])) {
		// 	$data['theme_admin_default_image_cart_height'] = $setting_info['theme_admin_default_image_cart_height'];
		// } else {
		// 	$data['theme_admin_default_image_cart_height'] = 47;
		// }

		$data['content'] = $this->load->view('extension/theme/admin_default', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/theme/admin_default')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
