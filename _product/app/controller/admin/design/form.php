<?php
namespace Controller\Design;

class Form extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('design/form');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('design/form');

		$this->getList();
	}

	public function add() {
		$this->load->language('design/form');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('design/form');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_design_form->addForm($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('design/form'));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('design/form');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('design/form');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_design_form->editForm($this->request->get['form_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('design/form'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('design/form');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('design/form');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $form_id) {
				$this->model_design_form->deleteForm($form_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('design/form'));
		}

		$this->getList();
	}

	protected function getList() {
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('design/form')
		);

		$data['actions']['add'] = $this->url->link('design/form/add');
		$data['actions']['delete'] = $this->url->link('design/form/delete');

		$data['forms'] = array();

		$data['forms'] = $this->model_design_form->getForms();

		foreach ($data['forms'] as &$result) {
			$result['status'] = $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled');
			$result['edit'] = $this->url->link('design/form/edit', 'user_token=' . $this->session->data['user_token'] . '&form_id=' . $result['form_id']);
		}
		unset($result);

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

		$data['content'] = $this->load->view('design/form_list', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function getForm() {
		$this->load->model('localisation/language');

		$data['text_form'] = !isset($this->request->get['form_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

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
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('design/form')
		);

		if (!isset($this->request->get['form_id'])) {
			$data['action'] = $this->url->link('design/form/add');
		} else {
			$data['action'] = $this->url->link('design/form/edit', 'user_token=' . $this->session->data['user_token'] . '&form_id=' . $this->request->get['form_id']);
		}

		$data['get_setting'] = $this->url->link('design/form/get_setting');

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('design/form');

		if (isset($this->request->get['form_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$form_info = $this->model_design_form->getForm($this->request->get['form_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('core/extension');

		$this->load->model('design/form');

		$data['types'] = $this->model_design_form->getTypes();

		if (isset($this->request->post['form_field'])) {
			$form_fields = $this->request->post['form_field'];
		} elseif (isset($this->request->get['form_id'])) {
			$form_fields = $this->model_design_form->getFormFields($this->request->get['form_id'], array('description' => true));
		} else {
			$form_fields = array();
		}

		$data['form_fields'] = array();

		$sort_order = array();

		foreach ($form_fields as $key => $form_field) {
			$data_form_field = $form_field;
			$data_form_field['form_row']= $key;

			$form_field['setting_view'] = $this->load->controller('extension/form/' . $form_field['code'] . '/get_setting', $data_form_field);

			$data['form_fields'][] = $form_field;

			$sort_order[] = $form_field['sort_order'];
		}

		array_multisort($sort_order, $data['form_fields']);

		if (isset($this->request->post['description'])) {
			$data['description'] = $this->request->post['description'];
		} elseif (!empty($form_info)) {
			$data['description'] = $this->model_design_form->getFormDescriptions($this->request->get['form_id']);
		} else {
			$data['description'] = array();
		}

		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} elseif (!empty($form_info)) {
			$data['email'] = $form_info['email'];
		} else {
			$data['email'] = '';
		}

		$emails = $this->config->get('mail_alert_email') ? explode(',', $this->config->get('mail_alert_email')) : array();
		array_unshift($emails, $this->config->get('mail_email'));

		$data['placeholder_email'] = implode(',', $emails);

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($form_info)) {
			$data['status'] = $form_info['status'];
		} else {
			$data['status'] = true;
		}

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$data['content'] = $this->load->view('design/form_form', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function validateForm() {
		foreach ($this->request->post['description'] as $language_id => $description) {
			if ((utf8_strlen($description['name']) < 3) || (utf8_strlen($description['name']) > 255)) {
				$this->error['name'][$language_id] = $this->language->get('error_name');
			}
		}

		return !$this->error;
	}

	protected function validateDelete() {

		return !$this->error;
	}

	public function get_setting() {
		$form_code = isset($this->request->post['form_code']) ? $this->request->post['form_code'] : 'custom_link';
		$form_row = isset($this->request->post['form_row']) ? $this->request->post['form_row'] : 0;
		$language_id = isset($this->request->post['language_id']) ? $this->request->post['language_id'] : 0;

		if (isset($this->request->post['form_field'][$language_id]) && isset($this->request->post['form_field'][$language_id][$form_row]) && is_array($this->request->post['form_field'][$language_id][$form_row])) {
			$data_form_field = $this->request->post['form_field'][$language_id][$form_row];
			$data_form_field['form_row'] = $form_row;
			$data_form_field['language_id'] = $language_id;

			$this->response->setOutput($this->load->controller('extension/form/' . $form_code . '/get_setting', $data_form_field));
		}
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('design/form');

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name']
			);

			$results = $this->model_design_form->getForms($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'form_id'	=> $result['form_id'],
					'name'		=> strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
				);
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}

	public function install() {
		$table = new \Model\DBTable($this->registry);

		$table->create('form');
	}

	public function uninstall() {
		$table = new \Model\DBTable($this->registry);

		$table->delete('form');
	}
}