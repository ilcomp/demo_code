<?php
namespace Controller\Extension\Module;

class Slider extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/slider');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/module');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_core_module->addModule('slider', $this->request->post);
			} else {
				$this->model_core_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('design/extension/module'));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('design/extension/module')
		);

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/slider')
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/slider', 'module_id=' . $this->request->get['module_id'])
			);
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/slider');
		} else {
			$data['action'] = $this->url->link('extension/module/slider', 'module_id=' . $this->request->get['module_id']);
		}

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('design/extension/module');

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_core_module->getModule($this->request->get['module_id']);
		}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info)) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}

		if (isset($this->request->post['width'])) {
			$data['width'] = $this->request->post['width'];
		} elseif (!empty($module_info)) {
			$data['width'] = $module_info['width'];
		} else {
			$data['width'] = '';
		}

		if (isset($this->request->post['height'])) {
			$data['height'] = $this->request->post['height'];
		} elseif (!empty($module_info)) {
			$data['height'] = $module_info['height'];
		} else {
			$data['height'] = '';
		}

		if (isset($this->request->post['width_desktop'])) {
			$data['width_desktop'] = $this->request->post['width_desktop'];
		} elseif (!empty($module_info)) {
			$data['width_desktop'] = $module_info['width_desktop'];
		} else {
			$data['width_desktop'] = '';
		}

		if (isset($this->request->post['height_desktop'])) {
			$data['height_desktop'] = $this->request->post['height_desktop'];
		} elseif (!empty($module_info)) {
			$data['height_desktop'] = $module_info['height_desktop'];
		} else {
			$data['height_desktop'] = '';
		}

		if (isset($this->request->post['class'])) {
			$data['class'] = $this->request->post['class'];
		} elseif (!empty($module_info)) {
			$data['class'] = $module_info['class'];
		} else {
			$data['class'] = '';
		}

		if (isset($this->request->post['slides'])) {
			$data['slides'] = $this->request->post['slides'];
		} elseif (!empty($module_info)) {
			$data['slides'] = $module_info['slides'];
		} else {
			$data['slides'] = array();
		}

		$this->load->model('tool/image');

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		$data['option_values'] = array();

		foreach ($data['slides'] as &$slide) {
			if (is_file(DIR_IMAGE . html_entity_decode($slide['image'], ENT_QUOTES, 'UTF-8'))) {
				$slide['thumb'] = $this->model_tool_image->resize(html_entity_decode($slide['image'], ENT_QUOTES, 'UTF-8'), 100, 100);
			} else {
				$slide['image'] = '';
				$slide['thumb'] = $data['placeholder'];
			}

			if (is_file(DIR_IMAGE . html_entity_decode($slide['image_desktop'], ENT_QUOTES, 'UTF-8'))) {
				$slide['thumb_desktop'] = $this->model_tool_image->resize(html_entity_decode($slide['image_desktop'], ENT_QUOTES, 'UTF-8'), 100, 100);
			} else {
				$slide['image_desktop'] = '';
				$slide['thumb_desktop'] = $data['placeholder'];
			}
		}
		unset($slide);

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info)) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$data['content'] = $this->load->view('extension/module/slider', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/slider')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		return !$this->error;
	}
}