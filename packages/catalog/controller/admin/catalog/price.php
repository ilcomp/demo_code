<?php
namespace Controller\Catalog;

class Price extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('catalog/price');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/price');

		$this->getList();
	}

	public function add() {
		$this->load->language('catalog/price');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/price');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_price->addPrice($this->request->post);

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

			$this->response->redirect($this->url->link('catalog/price', 'user_token=' . $this->session->data['user_token'] . $url));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('catalog/price');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/price');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_price->editPrice($this->request->get['price_id'], $this->request->post);

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

			$this->response->redirect($this->url->link('catalog/price', 'user_token=' . $this->session->data['user_token'] . $url));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('catalog/price');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/price');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $price_id) {
				$this->model_catalog_price->deletePrice($price_id);
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

			$this->response->redirect($this->url->link('catalog/price', 'user_token=' . $this->session->data['user_token'] . $url));
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
			'href' => $this->url->link('catalog/price', 'user_token=' . $this->session->data['user_token'] . $url)
		);

		$data['actions']['add'] = $this->url->link('catalog/price/add', 'user_token=' . $this->session->data['user_token'] . $url);
		$data['actions']['delete'] = $this->url->link('catalog/price/delete', 'user_token=' . $this->session->data['user_token'] . $url);

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit'),
			'limit' => $this->config->get('config_limit')
		);

		$price_total = $this->model_catalog_price->getTotalPrices();

		$data['prices'] = $this->model_catalog_price->getPrices($filter_data);

		foreach ($data['prices'] as &$result) {
			$result['edit'] = $this->url->link('catalog/price/edit', 'user_token=' . $this->session->data['user_token'] . '&price_id=' . $result['price_id'] . $url);
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

		$data['sort_name'] = $this->url->link('catalog/price', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url);
		$data['sort_code'] = $this->url->link('catalog/price', 'user_token=' . $this->session->data['user_token'] . '&sort=code' . $url);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$data['pagination'] = $this->load->controller('block/pagination', array(
			'total' => $price_total,
			'page'  => $page,
			'limit' => $this->config->get('config_limit'),
			'url'   => $this->url->link('catalog/price', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}')
		));

		$data['results'] = $this->load->controller('block/pagination/result', array(
			'total' => $price_total,
			'page'  => $page,
			'limit' => $this->config->get('config_limit')
		));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['content'] = $this->load->view('catalog/price_list', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['price_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

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
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('catalog/price', 'user_token=' . $this->session->data['user_token'] . $url)
		);

		if (!isset($this->request->get['price_id'])) {
			$data['action'] = $this->url->link('catalog/price/add', 'user_token=' . $this->session->data['user_token'] . $url);
		} else {
			$data['action'] = $this->url->link('catalog/price/edit', 'user_token=' . $this->session->data['user_token'] . '&price_id=' . $this->request->get['price_id'] . $url);
		}

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('catalog/price', 'user_token=' . $this->session->data['user_token'] . $url);

		if (isset($this->request->get['price_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$price_info = $this->model_catalog_price->getPrice($this->request->get['price_id']);
		}

		if (isset($this->request->post['price_description'])) {
			$data['price_description'] = $this->request->post['price_description'];
		} elseif (!empty($price_info)) {
			$data['price_description'] = $this->model_catalog_price->getPriceDescriptions($price_info['price_id']);
		} else {
			$data['price_description'] = array();
		}

		if (isset($this->request->post['currency_id'])) {
			$data['currency_id'] = $this->request->post['currency_id'];
		} elseif (!empty($price_info)) {
			$data['currency_id'] = $price_info['currency_id'];
		} else {
			$data['currency_id'] = 0;
		}

		if (isset($this->request->post['main'])) {
			$data['main'] = $this->request->post['main'];
		} elseif (!empty($price_info)) {
			$data['main'] = $price_info['main'];
		} else {
			$data['main'] = 0;
		}

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$this->load->model('localisation/currency');

		$data['currencies'] = $this->model_localisation_currency->getCurrencies();

		$data['content'] = $this->load->view('catalog/price_form', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'catalog/price')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['price_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 1) || (utf8_strlen($value['name']) > 255)) {
				$this->error['name'][$language_id] = $this->language->get('error_name');
			}
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'catalog/price')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$this->load->model('core/store');
		$this->load->model('sale/order');

		foreach ($this->request->post['selected'] as $price_id) {
			$price_info = $this->model_catalog_price->getPrice($price_id);

			if ($price_info) {
				if ($this->config->get('catalog_price') == $price_info['code']) {
					$this->error['warning'] = $this->language->get('error_default');
				}

				$store_total = $this->model_core_store->getTotalStoresByPrice($price_info['code']);

				if ($store_total) {
					$this->error['warning'] = sprintf($this->language->get('error_store'), $store_total);
				}
			}

			$order_total = $this->model_sale_order->getTotalOrdersByPriceId($price_id);

			if ($order_total) {
				$this->error['warning'] = sprintf($this->language->get('error_order'), $order_total);
			}
		}

		return !$this->error;
	}

	protected function validateRefresh() {
		if (!$this->user->hasPermission('modify', 'catalog/price')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}