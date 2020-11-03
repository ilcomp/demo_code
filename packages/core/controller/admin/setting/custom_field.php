<?php
namespace Controller\Setting;

class CustomField extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('setting/custom_field');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/custom_field');

		$this->getList();
	}

	public function add() {
		$this->load->language('setting/custom_field');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/custom_field');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_core_custom_field->addCustomField($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('setting/custom_field', 'user_token=' . $this->session->data['user_token'] . $url));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('setting/custom_field');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/custom_field');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_core_custom_field->editCustomField($this->request->get['custom_field_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('setting/custom_field', 'user_token=' . $this->session->data['user_token'] . $url));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('setting/custom_field');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/custom_field');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $custom_field_id) {
				$this->model_core_custom_field->deleteCustomField($custom_field_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('setting/custom_field', 'user_token=' . $this->session->data['user_token'] . $url));
		}

		$this->getList();
	}

	protected function getList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('setting/custom_field', 'user_token=' . $this->session->data['user_token'] . $url)
		);

		$data['actions']['add'] = $this->url->link('setting/custom_field/add', 'user_token=' . $this->session->data['user_token'] . $url);
		$data['actions']['delete'] = $this->url->link('setting/custom_field/delete', 'user_token=' . $this->session->data['user_token'] . $url);

		$data['custom_fields'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit'),
			'limit' => $this->config->get('config_limit')
		);

		$custom_field_total = $this->model_core_custom_field->getTotalCustomFields();

		$data['custom_fields'] = $this->model_core_custom_field->getCustomFields($filter_data);

		foreach ($data['custom_fields'] as &$custom_field) {
			$custom_field['edit'] = $this->url->link('setting/custom_field/edit', 'user_token=' . $this->session->data['user_token'] . '&custom_field_id=' . $custom_field['custom_field_id'] . $url);
		}
		unset($custom_field);

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

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link('setting/custom_field', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url);
		$data['sort_code'] = $this->url->link('setting/custom_field', 'user_token=' . $this->session->data['user_token'] . '&sort=code' . $url);
		$data['sort_location'] = $this->url->link('setting/custom_field', 'user_token=' . $this->session->data['user_token'] . '&sort=location' . $url);
		$data['sort_type'] = $this->url->link('setting/custom_field', 'user_token=' . $this->session->data['user_token'] . '&sort=type' . $url);
		$data['sort_status'] = $this->url->link('setting/custom_field', 'user_token=' . $this->session->data['user_token'] . '&sort=status' . $url);
		$data['sort_sort_order'] = $this->url->link('setting/custom_field', 'user_token=' . $this->session->data['user_token'] . '&sort=sort_order' . $url);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$data['pagination'] = $this->load->controller('block/pagination', array(
			'total' => $custom_field_total,
			'page'  => $page,
			'limit' => $this->config->get('config_limit'),
			'url'   => $this->url->link('setting/custom_field', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}')
		));

		$data['results'] = $this->load->controller('block/pagination/result', array(
			'total' => $custom_field_total,
			'page'  => $page,
			'limit' => $this->config->get('config_limit')
		));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['content'] = $this->load->view('setting/custom_field_list', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['custom_field_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = array();
		}

		if (isset($this->error['code'])) {
			$data['error_code'] = $this->error['code'];
		} else {
			$data['error_code'] = '';
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('setting/custom_field', 'user_token=' . $this->session->data['user_token'] . $url)
		);

		if (!isset($this->request->get['custom_field_id'])) {
			$data['action'] = $this->url->link('setting/custom_field/add', 'user_token=' . $this->session->data['user_token'] . $url);
		} else {
			$data['action'] = $this->url->link('setting/custom_field/edit', 'user_token=' . $this->session->data['user_token'] . '&custom_field_id=' . $this->request->get['custom_field_id'] . $url);
		}

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('setting/custom_field', 'user_token=' . $this->session->data['user_token'] . $url);

		$data['setting_type'] = $this->url->link('setting/custom_field/setting_type', 'user_token=' . $this->session->data['user_token']);

		if (isset($this->request->get['custom_field_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$custom_field_info = $this->model_core_custom_field->getCustomField($this->request->get['custom_field_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['custom_field_description'])) {
			$data['custom_field_description'] = $this->request->post['custom_field_description'];
		} elseif (!empty($custom_field_info)) {
			$data['custom_field_description'] = $this->model_core_custom_field->getCustomFieldDescriptions($this->request->get['custom_field_id']);
		} else {
			$data['custom_field_description'] = array();
		}

		if (isset($this->request->post['code'])) {
			$data['code'] = $this->request->post['code'];
		} elseif (!empty($custom_field_info)) {
			$data['code'] = $custom_field_info['code'];
		} else {
			$data['code'] = '';
		}

		if (isset($this->request->post['custom_field_location'])) {
			$data['custom_field_location'] = $this->request->post['custom_field_location'];
		} elseif (!empty($custom_field_info)) {
			$data['custom_field_location'] = $custom_field_info['custom_field_location'];
		} else {
			$data['custom_field_location'] = '';
		}

		if (isset($this->request->post['type'])) {
			$data['type'] = $this->request->post['type'];
		} elseif (!empty($custom_field_info)) {
			$data['type'] = $custom_field_info['type'];
		} else {
			$data['type'] = '';
		}

		if (isset($this->request->post['setting'])) {
			$data['setting'] = $this->request->post['setting'];
		} elseif (!empty($custom_field_info)) {
			$data['setting'] = $custom_field_info['setting'];
		} else {
			$data['setting'] = array();
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($custom_field_info)) {
			$data['status'] = $custom_field_info['status'];
		} else {
			$data['status'] = 0;
		}

		if (isset($this->request->post['multilanguage'])) {
			$data['multilanguage'] = $this->request->post['multilanguage'];
		} elseif (!empty($custom_field_info)) {
			$data['multilanguage'] = $custom_field_info['multilanguage'];
		} else {
			$data['multilanguage'] = 0;
		}

		if (isset($this->request->post['search'])) {
			$data['search'] = $this->request->post['search'];
		} elseif (!empty($custom_field_info)) {
			$data['search'] = $custom_field_info['search'];
		} else {
			$data['search'] = 0;
		}

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($custom_field_info)) {
			$data['sort_order'] = $custom_field_info['sort_order'];
		} else {
			$data['sort_order'] = 0;
		}

		$data['locations'] = $this->model_core_custom_field->getLocations();

		$data['types'] = $this->model_core_custom_field->getTypes();

		$data['content'] = $this->load->view('setting/custom_field_form', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	public function setting_type() {
		$this->load->language('setting/custom_field');

		$this->load->model('core/custom_field');

		$data['user_token'] = $this->session->data['user_token'];
		$data['values'] = isset($this->request->post['setting']) ? $this->request->post['setting'] : array();
		$data['type'] = isset($this->request->post['type']) ? $this->request->post['type'] : 'default';

		if (!is_array($data['values']))
			$data['values'] = json_decode(html_entity_decode($data['values'], ENT_QUOTES, 'UTF-8'), true);

		switch ($data['type']) {
			case 'editor':
				$data['dialogs'] = array('image', 'file', 'link');
				$data['editors'] = array('ckeditor', 'codemirror');

				if (!isset($data['values']['dialogs']))
					$data['values']['dialogs'] = array();

				if (!isset($data['values']['editors']))
					$data['values']['editors'] = array();

				break;
			case 'checkbox':
			case 'select':
			case 'radio':
				$this->load->model('localisation/listing');

				$data['listings'] = $this->model_localisation_listing->getListings();

				if (!isset($data['values']['listing_id']))
					$data['values']['listing_id'] = '';

				break;

			default:
				if (!isset($data['values']['default']))
					$data['values']['default'] = '';

				break;
		}

		if (is_file(DIR_TEMPLATE . $this->config->get('config_theme') . '/template/block/custom_field/' . $data['type'] . '_setting.twig')) {
			$views = $this->load->view('block/custom_field/' . $data['type'] . '_setting', $data);
		} elseif (is_file(DIR_TEMPLATE . $this->config->get('config_theme') . '/template/block/custom_field/default_setting.twig')) {
			$views = $this->load->view('block/custom_field/default_setting', $data);
		} else {
			$views = '';
		}

		$this->response->setOutput($views);
	}

	public function render($custom_fields) {
		$this->load->language('setting/custom_field');

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$data['user_token'] = $this->session->data['user_token'];
		$data['language_id'] = $this->config->get('config_language_id');

		$views = '';

		foreach ($custom_fields as $custom_field) {
			$data['custom_field'] = $custom_field;

			if (is_file(DIR_TEMPLATE . $this->config->get('config_theme') . '/template/block/custom_field/' . $custom_field['type'] . '_render.twig')) {
				$views .= $this->load->view('block/custom_field/' . $custom_field['type'] . '_render', $data);
			} elseif (is_file(DIR_TEMPLATE . $this->config->get('config_theme') . '/template/block/custom_field/default_render.twig')) {
				$views .= $this->load->view('block/custom_field/default_render', $data);
			}
		}

		return $views;
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'setting/custom_field')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['custom_field_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 3) || (utf8_strlen($value['name']) > 128)) {
				$this->error['name'][$language_id] = $this->language->get('error_name');
			}
		}

		if ((utf8_strlen($this->request->post['code']) < 3) || (utf8_strlen($this->request->post['code']) > 32)) {
			$this->error['code'] = $this->language->get('error_code');
		}

		if (isset($this->request->post['setting']['validation']) && @preg_match('/' . html_entity_decode($this->request->post['setting']['validation'], ENT_QUOTES, 'UTF-8') . '/', null) === false) {
			$this->error['validation'] = $this->language->get('error_validation');
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'setting/custom_field')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}