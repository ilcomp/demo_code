<?php
namespace Controller\Catalog;

class Stock extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('catalog/stock');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/stock');

		$this->getList();
	}

	public function add() {
		$this->load->language('catalog/stock');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/stock');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_stock->addStock($this->request->post);

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

			$this->response->redirect($this->url->link('catalog/stock', $url));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('catalog/stock');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/stock');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_stock->editStock($this->request->get['stock_id'], $this->request->post);

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

			$this->response->redirect($this->url->link('catalog/stock', $url));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('catalog/stock');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/stock');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $stock_id) {
				$this->model_catalog_stock->deleteStock($stock_id);
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

			$this->response->redirect($this->url->link('catalog/stock', $url));
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
			'href' => $this->url->link('common/dashboard')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('catalog/stock', $url)
		);

		$data['actions']['add'] = $this->url->link('catalog/stock/add', $url);
		$data['actions']['delete'] = $this->url->link('catalog/stock/delete', $url);

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit'),
			'limit' => $this->config->get('config_limit')
		);

		$stock_total = $this->model_catalog_stock->getTotalStocks();

		$data['stocks'] = $this->model_catalog_stock->getStocks($filter_data);

		foreach ($data['stocks'] as &$result) {
			$result['edit'] = $this->url->link('catalog/stock/edit', 'stock_id=' . $result['stock_id'] . $url);
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

		$data['sort_name'] = $this->url->link('catalog/stock', 'sort=name' . $url);
		$data['sort_code'] = $this->url->link('catalog/stock', 'sort=code' . $url);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$data['pagination'] = $this->load->controller('block/pagination', array(
			'total' => $stock_total,
			'page'  => $page,
			'limit' => $this->config->get('config_limit'),
			'url'   => $this->url->link('catalog/stock', $url . '&page={page}')
		));

		$data['results'] = $this->load->controller('block/pagination/result', array(
			'total' => $stock_total,
			'page'  => $page,
			'limit' => $this->config->get('config_limit')
		));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['content'] = $this->load->view('catalog/stock_list', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['stock_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

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
			'href' => $this->url->link('common/dashboard')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('catalog/stock', $url)
		);

		if (!isset($this->request->get['stock_id'])) {
			$data['action'] = $this->url->link('catalog/stock/add', $url);
		} else {
			$data['action'] = $this->url->link('catalog/stock/edit', 'stock_id=' . $this->request->get['stock_id'] . $url);
		}

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('catalog/stock', $url);

		if (isset($this->request->get['stock_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$stock_info = $this->model_catalog_stock->getStock($this->request->get['stock_id']);
		}

		if (isset($this->request->post['stock_description'])) {
			$data['stock_description'] = $this->request->post['stock_description'];
		} elseif (!empty($stock_info)) {
			$data['stock_description'] = $this->model_catalog_stock->getStockDescriptions($this->request->get['stock_id']);
		} else {
			$data['stock_description'] = array();
		}

		if (isset($this->request->post['location_id'])) {
			$data['location_id'] = $this->request->post['location_id'];
		} elseif (!empty($stock_info)) {
			$data['location_id'] = $stock_info['location_id'];
		} else {
			$data['location_id'] = 0;
		}

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$this->load->model('localisation/location');

		$data['locations'] = $this->model_localisation_location->getLocations();

		$data['content'] = $this->load->view('catalog/stock_form', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'catalog/stock')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['stock_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 1) || (utf8_strlen($value['name']) > 255)) {
				$this->error['name'][$language_id] = $this->language->get('error_name');
			}
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'catalog/stock')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	protected function validateRefresh() {
		if (!$this->user->hasPermission('modify', 'catalog/stock')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}