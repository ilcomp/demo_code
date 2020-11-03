<?php
namespace Controller\Catalog;

class Product extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('catalog/product');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/product_modify');

		$this->getList();
	}

	public function add() {
		$this->load->language('catalog/product');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/product_modify');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$product_id = $this->model_catalog_product_modify->addProduct($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = 'user_token=' . $this->session->data['user_token'];

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_category_id'])) {
				$url .= '&filter_category_id=' . (int)$this->request->get['filter_category_id'];
			}

			if (isset($this->request->get['filter_price'])) {
				$url .= '&filter_price=' . $this->request->get['filter_price'];
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->post['method']) && $this->request->post['method'] == 'save-exit') {
				$this->response->redirect($this->url->link('catalog/product', $url));
			} else {
				$this->response->redirect($this->url->link('catalog/product/edit', $url . '&catalog_product_id=' . $product_id));
			}
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('catalog/product');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/product_modify');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_product_modify->editProduct($this->request->get['catalog_product_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = 'user_token=' . $this->session->data['user_token'];

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_category_id'])) {
				$url .= '&filter_category_id=' . (int)$this->request->get['filter_category_id'];
			}

			if (isset($this->request->get['filter_price'])) {
				$url .= '&filter_price=' . $this->request->get['filter_price'];
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->post['method']) && $this->request->post['method'] == 'save-exit') {
				$this->response->redirect($this->url->link('catalog/product', $url));
			} else {
				$this->response->redirect($this->url->link('catalog/product/edit', $url . '&catalog_product_id=' . $this->request->get['catalog_product_id']));
			}
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('catalog/product');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/product_modify');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $product_id) {
				$this->model_catalog_product_modify->deleteProduct($product_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_category_id'])) {
				$url .= '&filter_category_id=' . (int)$this->request->get['filter_category_id'];
			}

			if (isset($this->request->get['filter_price'])) {
				$url .= '&filter_price=' . $this->request->get['filter_price'];
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . $url));
		}

		$this->getList();
	}

	public function copy() {
		$this->load->language('catalog/product');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/product_modify');

		if (isset($this->request->post['selected']) && $this->validateCopy()) {
			foreach ($this->request->post['selected'] as $product_id) {
				$this->model_catalog_product_modify->copyProduct($product_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_category_id'])) {
				$url .= '&filter_category_id=' . (int)$this->request->get['filter_category_id'];
			}

			if (isset($this->request->get['filter_price'])) {
				$url .= '&filter_price=' . $this->request->get['filter_price'];
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . $url));
		}

		$this->getList();
	}

	protected function getList() {
		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = '';
		}
		if (isset($this->request->get['filter_category_id'])) {
			$filter_category_id = $this->request->get['filter_category_id'];
		} else {
			$filter_category_id = '';
		}

		if (isset($this->request->get['filter_price'])) {
			$filter_price = $this->request->get['filter_price'];
		} else {
			$filter_price = '';
		}

		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = '';
		}

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

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_category_id'])) {
			$url .= '&filter_category_id=' . (int)$this->request->get['filter_category_id'];
		}

		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

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
			'href' => $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . $url)
		);

		$data['actions']['add'] = $this->url->link('catalog/product/add', 'user_token=' . $this->session->data['user_token'] . $url);
		$data['actions']['copy'] = $this->url->link('catalog/product/copy', 'user_token=' . $this->session->data['user_token'] . $url);
		$data['actions']['delete'] = $this->url->link('catalog/product/delete', 'user_token=' . $this->session->data['user_token'] . $url);

		$filter_data = array(
			'filter'             => array(),
			'sort'               => $sort,
			'order'              => $order,
			'start'              => ($page - 1) * $this->config->get('config_limit'),
			'limit'              => $this->config->get('config_limit')
		);

		if (!empty($filter_name))
			$filter_data['filter']['name'] = $filter_name;

		if ($filter_category_id != '') {
			$filter_data['filter']['category_id'] = $filter_category_id;
			$filter_data['filter']['sub_category'] = 1;
		}

		if ($filter_price != '')
			$filter_data['filter']['price'] = $filter_price;

		if ($filter_status != '')
			$filter_data['filter']['status'] = $filter_status;

		$product_total = $this->model_catalog_product_modify->getTotalProducts($filter_data);

		$data['products'] = $this->model_catalog_product_modify->getProducts($filter_data);

		$this->load->model('catalog/category');

		$catalog_url = new \Url(HTTP_APPLICATION_CLIENT);

		foreach ($data['products'] as &$result) {
			$result['prices'] = $this->model_catalog_product_modify->getProductPrices($result['product_id']);

			foreach ($result['prices'] as &$price) {
				$price = (float)$price;
			}
			unset($price);

			$result['categories'] = array();

			$categories = $this->model_catalog_product_modify->getProductCategories($result['product_id']);

			foreach ($categories as $category) {
				$paths = $this->model_catalog_category->getCategoryPath($category['category_id']);

				$result['categories'][] = implode(' -> ', array_column($paths, 'name'));
			}

			$result['status'] = $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled');
			$result['view'] = $catalog_url->link('catalog/product', 'catalog_product_id=' . $result['product_id']);
			$result['edit'] = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&catalog_product_id=' . $result['product_id'] . $url);
		}
		unset($result);

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

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_category_id'])) {
			$url .= '&filter_category_id=' . (int)$this->request->get['filter_category_id'];
		}

		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url);
		$data['sort_model'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . '&sort=model' . $url);
		$data['sort_price'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . '&sort=price' . $url);
		$data['sort_status'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . '&sort=status' . $url);
		$data['sort_order'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . '&sort=sort_order' . $url);

		$url = $this->request->get;
		unset($url['route']);
		unset($url['_route_']);
		$url['page'] = '{page}';

		$data['pagination'] = $this->load->controller('block/pagination', array(
			'total' => $product_total,
			'page'  => $page,
			'limit' => $this->config->get('config_limit'),
			'url'   => $this->url->link('catalog/product', $url)
		));

		$data['results'] = $this->load->controller('block/pagination/result', array(
			'total' => $product_total,
			'page'  => $page,
			'limit' => $this->config->get('config_limit')
		));

		$data['filter_name'] = $filter_name;
		$data['filter_category_id'] = $filter_category_id;
		$data['filter_price'] = $filter_price;
		$data['filter_status'] = $filter_status;

		$filter_category = $this->model_catalog_category->getCategoryPath($filter_category_id);

		$data['filter_category'] = implode(' &gt; ', array_column($filter_category, 'name'));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['content'] = $this->load->view('catalog/product_list', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['catalog_product_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

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

		if (isset($this->error['keyword'])) {
			$data['error_keyword'] = $this->error['keyword'];
		} else {
			$data['error_keyword'] = '';
		}

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_category_id'])) {
			$url .= '&filter_category_id=' . (int)$this->request->get['filter_category_id'];
		}

		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

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
			'href' => $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . $url)
		);

		if (!isset($this->request->get['catalog_product_id'])) {
			$data['action'] = $this->url->link('catalog/product/add', 'user_token=' . $this->session->data['user_token'] . $url);
		} else {
			$data['action'] = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&catalog_product_id=' . $this->request->get['catalog_product_id'] . $url);
		}

		if (isset($this->request->get['catalog_product_id'])) {
			$catalog_url = new \Url(HTTP_APPLICATION_CLIENT);

			$data['actions']['view'] = $catalog_url->link('catalog/product', 'catalog_product_id=' . $this->request->get['catalog_product_id']);
			$data['actions'][] = 'separator';
		}

		$data['actions']['add'] = $this->url->link('catalog/product/add', 'user_token=' . $this->session->data['user_token'] . $url);
		$data['actions'][] = 'separator';
		$data['actions']['save'] = true;
		$data['actions']['save_exit'] = true;
		$data['actions'][] = 'separator';
		$data['actions']['cancel'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . $url);

		if (isset($this->request->get['catalog_product_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$product_info = $this->model_catalog_product_modify->getProduct($this->request->get['catalog_product_id']);
		} else {
			$product_info = array();
		}

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->get['catalog_product_id'])) {
			$data['product_id'] = (int)$this->request->get['catalog_product_id'];
		} else {
			$data['product_id'] = 0;
		}

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['product_description'])) {
			$data['product_description'] = $this->request->post['product_description'];
		} elseif (!empty($product_info)) {
			$data['product_description'] = $this->model_catalog_product_modify->getProductDescriptions($product_info['product_id']);
		} else {
			$data['product_description'] = array();
		}

		$this->load->model('core/store');

		$data['stores'] = array();

		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		);

		$stores = $this->model_core_store->getStores();

		foreach ($stores as $store) {
			$data['stores'][] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			);
		}

		if (isset($this->request->post['product_store'])) {
			$data['product_store'] = (array)$this->request->post['product_store'];
		} elseif (!empty($product_info)) {
			$data['product_store'] = (array)$this->model_catalog_product_modify->getProductStores($product_info['product_id']);
		} else {
			$data['product_store'] = array(0);
		}

		if (isset($this->request->post['product_price'])) {
			$data['product_price'] = (array)$this->request->post['product_price'];
		} elseif (!empty($product_info)) {
			$data['product_price'] = (array)$this->model_catalog_product_modify->getProductPrices($product_info['product_id']);
		} else {
			$data['product_price'] = array();
		}

		$this->load->model('catalog/price');

		$data['prices'] = $this->model_catalog_price->getPrices();

		if (isset($this->request->post['date_available'])) {
			$data['date_available'] = $this->request->post['date_available'];
		} elseif (!empty($product_info)) {
			$data['date_available'] = ($product_info['date_available'] != '0000-00-00 00:00') ? preg_replace('/:\d\d$/', '', $product_info['date_available']) : '';
		} else {
			$data['date_available'] = date('Y-m-d H:i');
		}

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($product_info)) {
			$data['sort_order'] = $product_info['sort_order'];
		} else {
			$data['sort_order'] = 500;
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($product_info)) {
			$data['status'] = $product_info['status'];
		} else {
			$data['status'] = true;
		}

		// Categories
		$this->load->model('catalog/category_modify');

		$data['categories'] = $this->model_catalog_category_modify->getCategories(array('filter' => array('path_name' => '')));

		if (isset($this->request->post['product_category'])) {
			$categories = (array)$this->request->post['product_category'];
		} elseif (!empty($product_info)) {
			$categories = (array)$this->model_catalog_product_modify->getProductCategories($product_info['product_id']);
		} else {
			$categories = array();
		}

		$data['product_categories'] = array();

		foreach ($categories as $category) {
			$category_info = $this->model_catalog_category_modify->getCategory($category['category_id']);

			if ($category_info) {
				if ($category['main']) {
					$data['main_category_id'] = $category['category_id'];
				} else {
					$category_path = $this->model_catalog_category_modify->getCategoryPath($category['category_id']);

					$data['product_categories'][] = array(
						'category_id' => $category_info['category_id'],
						'name'        => implode(' &gt; ', array_column($category_path, 'name'))
					);
				}
			}
		}

		// Image
		$this->load->model('tool/image');

		$thumb_width = $this->config->get('admin_image_thumb_width') ? $this->config->get('admin_image_thumb_width') : 100;
		$thumb_height = $this->config->get('admin_image_thumb_height') ? $this->config->get('admin_image_thumb_height') : 100;

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', $thumb_width, $thumb_height);

		// Images
		if (isset($this->request->post['product_image'])) {
			$product_images = $this->request->post['product_image'];
		} elseif (!empty($product_info)) {
			$product_images = $this->model_catalog_product_modify->getProductImages($product_info['product_id']);
		} else {
			$product_images = array();
		}

		$data['product_images'] = array();

		foreach ($product_images as $product_image) {
			if (is_file(DIR_IMAGE . $product_image['image'])) {
				$product_image['image'] = $product_image['image'];
				$product_image['thumb'] = $this->model_tool_image->resize($product_image['image'], $thumb_width, $thumb_height);
			} else {
				$product_image['image'] = '';
				$product_image['thumb'] = $data['placeholder'];
			}

			$data['product_images'][] = $product_image;
		}

		if (isset($this->request->post['product_related'])) {
			$products = $this->request->post['product_related'];
		} elseif (!empty($product_info)) {
			$products = $this->model_catalog_product_modify->getProductRelatedId($product_info['product_id']);
		} else {
			$products = array();
		}

		$data['product_relateds'] = array();

		foreach ($products as $product_id) {
			$related_info = $this->model_catalog_product_modify->getProduct($product_id);

			if ($related_info) {
				$data['product_relateds'][] = array(
					'product_id' => $related_info['product_id'],
					'name'       => $related_info['name']
				);
			}
		}

		if (isset($this->request->post['product_seo_url'])) {
			$data['product_seo_url'] = $this->request->post['product_seo_url'];
		} elseif (!empty($product_info)) {
			$data['product_seo_url'] = $this->model_catalog_product_modify->getProductSeoUrls($product_info['product_id']);
		} else {
			$data['product_seo_url'] = array();
		}

		if (isset($this->request->post['custom_field'])) {
			$custom_field_values = $this->request->post['custom_field'];
		} elseif (!empty($product_info)) {
			$custom_field_values = $this->model_catalog_product_modify->getProductCustomFields($product_info['product_id']);
		} else {
			$custom_field_values = array();
		}

		$this->load->model('core/custom_field');

		$custom_fields = $this->model_core_custom_field->getAsField($custom_field_values, 'catalog_product');

		$data['custom_fields'] = $this->load->controller('setting/custom_field/render', $custom_fields);

		$product_attribute = $this->model_core_custom_field->getAsField($custom_field_values, 'catalog_product_attribute');

		$data['product_attribute'] = $this->load->controller('setting/custom_field/render', $product_attribute);

		$data['additional_fields'] = '';

		$data['content'] = $this->load->view('catalog/product_form', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'catalog/product')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['product_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 1) || (utf8_strlen($value['name']) > 255)) {
				$this->error['name'][$language_id] = $this->language->get('error_name');
			}
		}

		if ($this->request->post['product_seo_url']) {
			$this->load->model('design/seo_url');

			foreach ($this->request->post['product_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if ($keyword && (!isset($this->request->get['catalog_product_id']) || $keyword != $this->request->get['catalog_product_id'])) {
						$seo_urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword, array('store_id' => $store_id, 'language_id' => $language_id));

						foreach ($seo_urls as $seo_url) {
							if (!isset($this->request->get['catalog_product_id']) || $seo_url['query'] != 'catalog_product_id=' . $this->request->get['catalog_product_id']) {
								$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_keyword');

								break;
							}
						}
					}
				}
			}
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'catalog/product')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	protected function validateCopy() {
		if (!$this->user->hasPermission('modify', 'catalog/product')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('catalog/product');

			if (isset($this->request->get['filter_name'])) {
				$filter_name = $this->request->get['filter_name'];
			} else {
				$filter_name = '';
			}

			if (isset($this->request->get['limit'])) {
				$limit = $this->request->get['limit'];
			} else {
				$limit = 5;
			}

			$filter_data = array(
				'filter' => array(
					'name' => '%' . $filter_name
				),
				'start'        => 0,
				'limit'        => $limit
			);

			$results = $this->model_catalog_product->getProducts($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'product_id' => $result['product_id'],
					'name'       => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'model'      => isset($result['model']) ? $result['model'] : ''
				);
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}
}