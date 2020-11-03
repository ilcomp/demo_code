<?php
namespace Controller\Catalog;

class ProductList extends \Controller {
	public function index($data = array()) {
		$this->load->language('catalog/product');

		$this->load->model('catalog/product');
		$this->load->model('core/custom_field');
		$this->load->model('tool/image');

		if (empty($data['image_width']))
			$data['image_width'] = $this->config->get('catalog_image_list_width');

		if (empty($data['image_height']))
			$data['image_height'] = $this->config->get('catalog_image_list_height');

		if (!isset($data['heading_title']))
			$data['heading_title'] = '';

		$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('catalog_product_attribute');

		foreach ($data['products'] as &$product) {
			if (!isset($product['attributes']))
				$product['attributes'] = array();

			$custom_field_values = $this->model_catalog_product->getProductCustomFields($product['product_id']);

			foreach ($custom_fields as $custom_field) {
				$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

				if (isset($custom_field_values[$custom_field['custom_field_id']]) && isset($custom_field_values[$custom_field['custom_field_id']][$language_id])) {
					$value = $custom_field_values[$custom_field['custom_field_id']][$language_id];
				} elseif (isset($custom_field['setting']['default']))
					$value = $custom_field['setting']['default'];
				else
					$value = '';

				$product['attributes'][$custom_field['code']] = $this->model_core_custom_field->getAsValue($value, $custom_field);
			}

			$image = $this->model_catalog_product->getProductImageMain($product['product_id']);

			if ($image) {
				$product['image'] = $image['image'];
				$product['thumb'] = $this->model_tool_image->resize($image['image'], $data['image_width'], $data['image_height']);
			} else {
				$product['image'] = '';
				$product['thumb'] = $this->model_tool_image->resize('placeholder.png', $data['image_width'], $data['image_height']);
			}

			if (empty($product['title']))
				$product['title'] = $product['name'];

			$product['href'] = $this->url->link('catalog/product', 'catalog_product_id=' . $product['product_id']);

			if (!empty($product['price']))
				$product['price'] = $this->currency->convert($product['price'], $product['currency_id'], $this->session->data['currency']);
		}
		unset($product);

		if ($this->config->get('config_theme') == 'default') {
			$theme = $this->config->get('theme_default');
		} else {
			$theme = $this->config->get('config_theme');
		}

		$template = (isset($data['template']) && is_file(DIR_TEMPLATE . $theme . '/template/' . $data['template'] . '.twig')) ? $data['template'] : 'catalog/product_list';

		return (!empty($data['products']) ? $this->load->view($template, $data) : '');
	}
}