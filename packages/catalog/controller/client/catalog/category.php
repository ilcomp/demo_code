<?php
namespace Controller\Catalog;

class Category extends \Controller {
	public function index() {
		$category_id = isset($this->request->get['catalog_category_id']) ? $this->request->get['catalog_category_id'] : 0;

		$this->load->language('catalog/category');

		$this->load->model('catalog/category');

		$category_info = $this->model_catalog_category->getCategory($category_id);

		if (!$category_info) {
			$this->config->set('error', $this->language->get('error_not_found'));

			return new \Action ('error/not_found');
		}

		$data = $category_info;

		$this->load->model('catalog/product');

		if (isset($this->request->get['filter'])) {
			$filter = $this->request->get['filter'];
		} else {
			$filter = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'sort_order';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (!empty($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		if (isset($this->request->get['limit'])) {
			$limit = (int)$this->request->get['limit'];
		} elseif ($this->config->get('catalog_product_limit')) {
			$limit = (int)$this->config->get('catalog_product_limit');
		} else {
			$limit = 12;
		}

		$data['mid'] =  $_SERVER["REMOTE_ADDR"];
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

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

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}

		$categories = $this->model_catalog_category->getCategoryPath($category_id);

		foreach ($categories as $category) {
			$data['breadcrumbs'][] = array(
				'text' => $category['title'] ? $category['title'] : $category['name'],
				'href' => $this->url->link('catalog/category', 'catalog_category_id=' . $category['path_id'] . $url)
			);
		}

		$this->document->setTitle($category_info['meta_title'] ? $category_info['meta_title'] : $category_info['name']);
		$this->document->setDescription($category_info['meta_description']);

		$data['heading_title'] = $category_info['title'] ? $category_info['title'] : $category_info['name'];

		$this->load->model('core/custom_field');

		$data['custom_fields'] = array();

		$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('catalog_category');
		$custom_field_values = $this->model_catalog_category->getCategoryCustomFields($category_info['category_id']);

		foreach ($custom_fields as $custom_field) {
			$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

			if (isset($custom_field_values[$custom_field['custom_field_id']]) && isset($custom_field_values[$custom_field['custom_field_id']][$language_id])) {
				$value = $custom_field_values[$custom_field['custom_field_id']][$language_id];
			} elseif (isset($custom_field['setting']['default']))
				$value = $custom_field['setting']['default'];
			else
				$value = '';

			$data['custom_fields'][$custom_field['code']] = $this->model_core_custom_field->getAsValue($value, $custom_field);
		}

		$data['compare_count'] = isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0;

		$data['compare'] = $this->url->link('catalog/compare');

		$url = '';

		if (isset($this->request->get['filter'])) {
			$url .= '&filter=' . $this->request->get['filter'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}

		$data['products'] = array();

		$filter_data = array(
			'sort'                => $sort,
			'order'               => $order,
			'start'               => ($page - 1) * $limit,
			'limit'               => $limit
		);

		if ($category_id != $this->config->get('catalog_category_general')) {
			$filter_data['filter'] = array(
				'category_id'  => $category_id,
				'sub_category' => $this->config->get('catalog_product_sub_category') ? true : false,
			);
		}

		$product_total = $this->model_catalog_product->getTotalProducts($filter_data);

		$products = $this->model_catalog_product->getProducts($filter_data);

		$data['product_list'] = $this->load->controller('catalog/product_list', array(
			'products'=> $products,
			'category_id' => $category_id
		));

		$url = $this->request->get;
		unset($url['route']);
		unset($url['_route_']);
		unset($url['sort']);
		unset($url['order']);
		$url = http_build_query($url);

		$data['sorts'] = array();

		foreach (array('sort_order', 'name', 'price') as $item) {
			$data['sorts'][] = array(
				'text'  => $this->language->get('text_' . $item),
				'sort' => $item,
				'order' => 'ASC',
				'href'  => $this->url->link('catalog/category', $url . '&sort=' . $item . '&order=ASC')
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_' . $item),
				'sort' => $item,
				'order' => 'DESC',
				'href'  => $this->url->link('catalog/category', $url . '&sort=' . $item . '&order=DESC')
			);
		}

		$url = $this->request->get;
		unset($url['route']);
		unset($url['_route_']);
		unset($url['limit']);
		$url = http_build_query($url);

		$data['limits'] = array();

		$limits = array_unique(array($this->config->get('catalog_product_limit'), 25, 50, 75, 100));

		sort($limits);

		foreach($limits as $value) {
			$data['limits'][] = array(
				'text'  => $value,
				'value' => $value,
				'href'  => $this->url->link('catalog/category', $url . '&limit=' . $value)
			);
		}

		$url = $this->request->get;
		unset($url['route']);
		unset($url['_route_']);
		$url['page'] = '{page}';

		$data['pagination'] = $this->load->controller('block/pagination', array(
			'total' => $product_total,
			'page'  => $page,
			'limit' => $limit,
			'url'   => $this->url->link('catalog/category',  $url)
		));

		$data['results'] = $this->load->controller('block/pagination/result', array(
			'total' => $product_total,
			'page'  => $page,
			'limit' => $limit
		));

		$this->document->addLink($this->url->link('catalog/category', 'catalog_category_id=' . $category_info['category_id']), 'canonical');

		$data['sort'] = $sort;
		$data['order'] = $order;
		$data['limit'] = $limit;
		$data['page'] = $page;
		$data['product_total'] = $product_total;

		$data['language'] = $this->config->get('config_language');

		$data['continue'] = $this->url->link('common/home');

		$data['content'] = $this->load->view('catalog/category', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}
}