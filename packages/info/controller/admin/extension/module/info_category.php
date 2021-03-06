<?php
namespace Controller\Extension\Module;

class InfoCategory extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/info');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/module');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_core_module->addModule('info', $this->request->post);
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
				'href' => $this->url->link('extension/module/info')
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/info', 'module_id=' . $this->request->get['module_id'])
			);
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/info');
		} else {
			$data['action'] = $this->url->link('extension/module/info', 'module_id=' . $this->request->get['module_id']);
		}

		$data['cancel'] = $this->url->link('design/extension/module');

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

		$this->load->model('info/category');

		$filter_data = array(
			'sort'        => 'name',
			'order'       => 'ASC'
		);

		$data['categories'] = $this->model_info_category->getCategories($filter_data);

		if (isset($this->request->post['main_category_id'])) {
			$data['main_category_id'] = $this->request->post['main_category_id'];
		} elseif (!empty($module_info)) {
			$data['main_category_id'] = $module_info['main_category_id'];
		} else {
			$data['main_category_id'] = 0;
		}

		if (isset($this->request->post['limit'])) {
			$data['limit'] = $this->request->post['limit'];
		} elseif (!empty($module_info)) {
			$data['limit'] = $module_info['limit'];
		} else {
			$data['limit'] = 3;
		}

		if (isset($this->request->post['desc_limit'])) {
			$data['desc_limit'] = $this->request->post['desc_limit'];
		} elseif (!empty($module_info)) {
			$data['desc_limit'] = $module_info['desc_limit'];
		} else {
			$data['desc_limit'] = 300;
		}

		if (isset($this->request->post['show_title'])) {
			$data['show_title'] = $this->request->post['show_title'];
		} elseif (!empty($module_info)) {
			$data['show_title'] = $module_info['show_title'];
		} else {
			$data['show_title'] = '';
		}

		if (isset($this->request->post['template'])) {
			$data['template'] = $this->request->post['template'];
		} elseif (!empty($module_info)) {
			$data['template'] = $module_info['template'];
		} else {
			$data['template'] = '';
		}

		$data['sort_by_array'] = array (
			'a.sort_order'		=> $this->language->get('sort_by_sort_order'),
			'a.date_available'	=> $this->language->get('sort_by_date_available'),
			'ad.name'			=> $this->language->get('sort_by_name')
		);

		if (isset($this->request->post['sort_by'])) {
			$data['sort_by'] = $this->request->post['sort_by'];
		} elseif (!empty($module_info)) {
			$data['sort_by'] = $module_info['sort_by'];
		} else {
			$data['sort_by'] = 'a.sort_order';
		}

		$data['sort_direction_array'] = array (
			'desc'		=> $this->language->get('sort_direction_desc'),
			'asc'		=> $this->language->get('sort_direction_asc')
		);

		if (isset($this->request->post['sort_direction'])) {
			$data['sort_direction'] = $this->request->post['sort_direction'];
		} elseif (!empty($module_info)) {
			$data['sort_direction'] = $module_info['sort_direction'];
		} else {
			$data['sort_direction'] = 'desc';
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info)) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}

		$data['content'] = $this->load->view('extension/module/info', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function validate(){
		if (!$this->user->hasPermission('modify', 'extension/module/info')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if (!$this->request->post['limit']) {
			$this->error['limit'] = $this->language->get('error_limit');
		}

		return !$this->error;
	}
}