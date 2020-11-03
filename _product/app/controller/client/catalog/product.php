<?php
namespace Controller\Catalog;

class Product extends \Controller {
	public function index($data = array()) {
		$product_id = isset($this->request->get['catalog_product_id']) ? $this->request->get['catalog_product_id'] : 0;

		$this->load->language('catalog/product');

		$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct($product_id);

		if (!$product_info) {
			$this->config->set('error', $this->language->get('error_not_found'));

			return new \Action ('error/not_found');
		}

		$data = $product_info;
		
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$this->load->model('catalog/category');

		$url = $this->request->get;
		unset($url['route']);
		unset($url['_route_']);
		unset($url['catalog_product_id']);

		$category_id = $this->model_catalog_product->getProductCategoryIdMain($product_info['product_id']);

		if ($category_id) {
			$categories = $this->model_catalog_category->getCategoryPath($category_id);

			foreach ($categories as $category) {
				$url['catalog_category_id'] = $category['path_id'];

				$data['breadcrumbs'][] = array(
					'text' => $category['title'] ? $category['title'] : $category['name'],
					'href' => $this->url->link('catalog/category', $url)
				);
			}
		}

		$data['category_id'] = $category_id;

		$url = $this->request->get;
		unset($url['route']);
		unset($url['_route_']);
		$url['catalog_product_id'] = $product_info['product_id'];

		$data['breadcrumbs'][] = array(
			'text' => $product_info['title'] ? $product_info['title'] : $product_info['name'],
			'href' => $this->url->link('catalog/product', $url)
		);

		$this->document->setTitle($product_info['meta_title'] ? $product_info['meta_title'] : $product_info['name']);
		$this->document->setDescription($product_info['meta_description']);
		$this->document->addLink($this->url->link('catalog/product', 'catalog_product_id=' . $product_info['product_id']), 'canonical');

		$data['heading_title'] = $product_info['title'] ? $product_info['title'] : $product_info['name'];

		$data['actions']['format_price'] = $this->url->link('catalog/product/format_price');

		$data['share'] = $this->url->link('catalog/product', 'catalog_product_id=' . (int)$product_info['product_id']);

		$this->load->model('tool/image');

		$data['images'] = array();

		$data['images'] = $this->model_catalog_product->getProductImages($product_info['product_id']);

		foreach ($data['images'] as &$image) {
			$image['popup'] = $this->model_tool_image->resize($image['image'], $this->config->get('catalog_image_popup_width'), $this->config->get('catalog_image_popup_height'));
			$image['thumb'] = $this->model_tool_image->resize($image['image'], $this->config->get('catalog_image_thumb_width'), $this->config->get('catalog_image_thumb_height'));
			$image['additional'] = $this->model_tool_image->resize($image['image'], $this->config->get('catalog_image_additional_width'), $this->config->get('catalog_image_additional_height'));
		}
		unset($image);

		if (!empty($data['price']))
			$data['price'] = $this->currency->convert($data['price'], $data['currency_id'], $this->session->data['currency']);

		$products = $this->model_catalog_product->getProductRelated($product_info['product_id']);

		$data['product_list'] = $this->load->controller('catalog/product_list', array(
			'products'=> $products,
			'heading_title' => $this->language->get('text_related'),
			'image_width'=> $this->config->get('catalog_image_related_width'),
			'image_height'=> $this->config->get('catalog_image_related_height'),
			'type' => 'related'
		));

		$data['form'] = $this->load->controller('form/product');

		$this->load->model('core/custom_field');

		$custom_field_values = $this->model_catalog_product->getProductCustomFields($product_info['product_id']);

		$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('catalog_product');

		$data['custom_fields'] = array();

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

		$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('catalog_product_attribute');

		$data['attributes'] = array();

		foreach ($custom_fields as $custom_field) {
			$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

			if (isset($custom_field_values[$custom_field['custom_field_id']]) && isset($custom_field_values[$custom_field['custom_field_id']][$language_id])) {
				$value = $custom_field_values[$custom_field['custom_field_id']][$language_id];
			} elseif (isset($custom_field['setting']['default']))
				$value = $custom_field['setting']['default'];
			else
				$value = '';

			$data['attributes'][$custom_field['code']] = $this->model_core_custom_field->getAsValue($value, $custom_field);
		}

		$data['tags'] = array();

		if ($product_info['tag']) {
			$tags = explode(',', $product_info['tag']);

			foreach ($tags as $tag) {
				$data['tags'][] = array(
					'tag'  => trim($tag),
					'href' => $this->url->link('common/search', 'tag=' . trim($tag))
				);
			}
		}

		$data['content'] = $this->load->view('catalog/product', $data);

		if ($this->request->server['HTTP_ACCEPT'] == 'text/html') {
			$this->response->setOutput($data['content']);
		} else {
			$this->response->setOutput($this->load->controller('common/template', $data));
		}
	}

	public function calculate() {
		if (empty($this->request->post['catalog_product_id']))
			new \Action ('error/not_found');

		if (!isset($this->request->post['quantity']) || $this->request->post['quantity'] < 1)
			$this->request->post['quantity'] = 1;

		$this->load->model('catalog/product');

		$json = $this->model_catalog_product->getPriceTotal($this->request->post);

		if (!$json) {
			$this->config->set('error', $this->language->get('error_not_found'));

			new \Action ('error/not_found');
		}

		if ($this->request->server['HTTP_ACCEPT'] == 'application/json') {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput($json);
		} else {
			return array('form_result' => $json);
		}
	}
}