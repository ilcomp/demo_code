<?php
namespace Controller\Extension\Module;

class CatalogProduct extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/catalog_product');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/module');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$module_id = $this->model_core_module->addModule('catalog_product', $this->request->post);
			} else {
				$module_id = $this->request->get['module_id'];

				$this->model_core_module->editModule($module_id, $this->request->post);
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
				'href' => $this->url->link('extension/module/catalog_product')
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/catalog_product', 'module_id=' . $this->request->get['module_id'])
			);
		}

		$data['user_token'] = $this->session->data['user_token'];

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/catalog_product');
		} else {
			$data['action'] = $this->url->link('extension/module/catalog_product', 'module_id=' . $this->request->get['module_id']);
		}

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('design/extension/module');

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_core_module->getModule($this->request->get['module_id']);
		} else {
			$module_info = '';
		}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info['name'])) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}

		if (isset($this->request->post['title'])) {
			$data['title'] = $this->request->post['title'];
		} elseif (!empty($module_info['title'])) {
			$data['title'] = $module_info['title'];
		} else {
			$data['title'] = '';
		}

		if (isset($this->request->post['filter'])) {
			$data['filter'] = $this->request->post['filter'];
		} elseif (!empty($module_info['filter'])) {
			$data['filter'] = $module_info['filter'];
		} else {
			$data['filter'] = '';
		}

		if (isset($this->request->post['limit'])) {
			$data['limit'] = $this->request->post['limit'];
		} elseif (!empty($module_info['limit'])) {
			$data['limit'] = $module_info['limit'];
		} else {
			$data['limit'] = 5;
		}

		if (isset($this->request->post['width'])) {
			$data['image_width'] = $this->request->post['image_width'];
		} elseif (isset($module_info['image_width'])) {
			$data['image_width'] = $module_info['image_width'];
		} else {
			$data['image_width'] = 200;
		}

		if (isset($this->request->post['image_height'])) {
			$data['image_height'] = $this->request->post['image_height'];
		} elseif (isset($module_info['image_height'])) {
			$data['image_height'] = $module_info['image_height'];
		} else {
			$data['image_height'] = 200;
		}

		if (isset($this->request->post['image_method'])) {
			$data['image_method'] = $this->request->post['image_method'];
		} elseif (!empty($module_info['image_method'])) {
			$data['image_method'] = $module_info['image_method'];
		} else {
			$data['image_method'] = '';
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info['status'])) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}

		if (!empty($this->request->post['product'])) {
			$product_ids = $this->request->post['product'];
		} elseif (!empty($module_info['product'])) {
			$product_ids = $module_info['product'];
		} else {
			$product_ids = array();
		}

		$this->load->model('catalog/product');

		$data['products'] = array();

		foreach ($product_ids as $product_id) {
			$product = $this->model_catalog_product->getProduct($product_id);

			if ($product)
				$data['products'][] = $product;
		}

		$data['filters'] = array(
			'bestseller' => $this->language->get('text_bestseller'),
			'featured' => $this->language->get('text_featured'),
			'latest' => $this->language->get('text_latest'),
			'special' => $this->language->get('text_special'),
		);

		$data['content'] = $this->load->view('extension/module/catalog_product', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/catalog_product')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		return !$this->error;
	}
}
