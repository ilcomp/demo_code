<?php
namespace Controller\Localisation;

class WeightClass extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('localisation/weight_class');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('localisation/weight_class');

		$this->getList();
	}

	public function add() {
		$this->load->language('localisation/weight_class');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('localisation/weight_class');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_localisation_weight_class->addWeightClass($this->request->post);

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

			$this->response->redirect($this->url->link('localisation/weight_class', 'user_token=' . $this->session->data['user_token'] . $url));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('localisation/weight_class');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('localisation/weight_class');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_localisation_weight_class->editWeightClass($this->request->get['weight_class_id'], $this->request->post);

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

			$this->response->redirect($this->url->link('localisation/weight_class', 'user_token=' . $this->session->data['user_token'] . $url));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('localisation/weight_class');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('localisation/weight_class');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $weight_class_id) {
				$this->model_localisation_weight_class->deleteWeightClass($weight_class_id);
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

			$this->response->redirect($this->url->link('localisation/weight_class', 'user_token=' . $this->session->data['user_token'] . $url));
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
			'href' => $this->url->link('localisation/weight_class', 'user_token=' . $this->session->data['user_token'] . $url)
		);

		$data['actions']['add'] = $this->url->link('localisation/weight_class/add', 'user_token=' . $this->session->data['user_token'] . $url);
		$data['actions']['delete'] = $this->url->link('localisation/weight_class/delete', 'user_token=' . $this->session->data['user_token'] . $url);

		$data['weight_classes'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('admin_limit'),
			'limit' => $this->config->get('admin_limit')
		);

		$weight_class_total = $this->model_localisation_weight_class->getTotalWeightClasses();

		$data['weight_classes'] = $this->model_localisation_weight_class->getWeightClasses($filter_data);

		foreach ($data['weight_classes'] as &$result) {
			$result['name'] = $result['name'] . (($result['weight_class_id'] == $this->config->get('config_weight_class_id')) ? $this->language->get('text_default') : null);
			$result['edit'] = $this->url->link('localisation/weight_class/edit', 'user_token=' . $this->session->data['user_token'] . '&weight_class_id=' . $result['weight_class_id'] . $url);
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

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link('localisation/weight_class', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url);
		$data['sort_unit'] = $this->url->link('localisation/weight_class', 'user_token=' . $this->session->data['user_token'] . '&sort=unit' . $url);
		$data['sort_value'] = $this->url->link('localisation/weight_class', 'user_token=' . $this->session->data['user_token'] . '&sort=value' . $url);

		$url = $this->request->get;
		unset($url['route']);
		unset($url['_route_']);
		$url['page'] = '{page}';

		$data['pagination'] = $this->load->controller('block/pagination', array(
			'total' => $weight_class_total,
			'page'  => $page,
			'limit' => $this->config->get('config_limit'),
			'url'   => $this->url->link('localisation/weight_class', $url)
		));

		$data['results'] = $this->load->controller('block/pagination/result', array(
			'total' => $weight_class_total,
			'page'  => $page,
			'limit' => $this->config->get('config_limit')
		));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['content'] = $this->load->view('localisation/weight_class_list', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['weight_class_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

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

		if (isset($this->error['unit'])) {
			$data['error_unit'] = $this->error['unit'];
		} else {
			$data['error_unit'] = array();
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
			'href' => $this->url->link('localisation/weight_class', 'user_token=' . $this->session->data['user_token'] . $url)
		);

		if (!isset($this->request->get['weight_class_id'])) {
			$data['action'] = $this->url->link('localisation/weight_class/add', 'user_token=' . $this->session->data['user_token'] . $url);
		} else {
			$data['action'] = $this->url->link('localisation/weight_class/edit', 'user_token=' . $this->session->data['user_token'] . '&weight_class_id=' . $this->request->get['weight_class_id'] . $url);
		}

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('localisation/weight_class', 'user_token=' . $this->session->data['user_token'] . $url);

		if (isset($this->request->get['weight_class_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$weight_class_info = $this->model_localisation_weight_class->getWeightClass($this->request->get['weight_class_id']);
		}

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['weight_class_description'])) {
			$data['weight_class_description'] = $this->request->post['weight_class_description'];
		} elseif (!empty($weight_class_info)) {
			$data['weight_class_description'] = $this->model_localisation_weight_class->getWeightClassDescriptions($this->request->get['weight_class_id']);
		} else {
			$data['weight_class_description'] = array();
		}

		if (isset($this->request->post['value'])) {
			$data['value'] = $this->request->post['value'];
		} elseif (!empty($weight_class_info)) {
			$data['value'] = $weight_class_info['value'];
		} else {
			$data['value'] = '';
		}

		$data['content'] = $this->load->view('localisation/weight_class_form', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'localisation/weight_class')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['weight_class_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 3) || (utf8_strlen($value['name']) > 32)) {
				$this->error['name'][$language_id] = $this->language->get('error_name');
			}

			if (!$value['unit'] || (utf8_strlen($value['unit']) > 4)) {
				$this->error['unit'][$language_id] = $this->language->get('error_unit');
			}
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'localisation/weight_class')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$this->load->model('catalog/product');

		foreach ($this->request->post['selected'] as $weight_class_id) {
			if ($this->config->get('config_weight_class_id') == $weight_class_id) {
				$this->error['warning'] = $this->language->get('error_default');
			}

			$product_total = $this->model_catalog_product->getTotalProductsByWeightClassId($weight_class_id);

			if ($product_total) {
				$this->error['warning'] = sprintf($this->language->get('error_product'), $product_total);
			}
		}

		return !$this->error;
	}
}