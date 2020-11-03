<?php
namespace Controller\Extension\Module;

class CatalogCategory extends \Controller {
	public function index($setting) {
		$this->load->language('extension/module/catalog_category');

		$this->load->model('catalog/category');

		$data['setting'] = $setting;

		if (!$setting['limit'])
			$setting['limit'] = 4;

		switch ($setting['filter']) {
			case 'featured':
				if (!empty($setting['category']))
					$category_ids = array_slice($setting['category'], 0, (int)$setting['limit']);

				$data['categories'] = array();

				foreach ($category_ids as $category_id) {
					$category = $this->model_catalog_category->getCategory($category_id);

					if ($category) {
						$data['categories'][$category['category_id']] = $category;
					}
				}

				break;
			case 'children':
				$data['categories'] = array();

				foreach ($setting['category'] as $category_id) {
					$categories = $this->model_catalog_category->getCategories(array(
						'filter' => array(
							'parent_id' => $category_id
						),
						'limit' => $setting['limit'] - count($data['categories'])
					));

					foreach ($categories as $category) {
						$data['categories'][$category['category_id']] = $category;
					}

					if ((int)$setting['limit'] >= count($data['categories']))
						break;
				}

				break;
			default:
				$data['categories'] = array();

				break;
		}

		foreach ($data['categories'] as &$category) {
			if (!$category['title'])
				$category['title'] = $category['name'];

			$category['href'] = $this->url->link('catalog/category', 'catalog_category_id=' . $category['category_id']);
		}
		unset($category);

		if ($setting['image_custom_field']) {
			$this->load->model('tool/image');
			$this->load->model('core/custom_field');

			$custom_field = $this->model_core_custom_field->getCustomField($setting['image_custom_field']);

			$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

			foreach ($data['categories'] as &$category) {
				$category['image'] = $this->model_catalog_category->getCategoryCustomField($category['category_id'], $setting['image_custom_field'], $language_id);

				if ($category['image'])
					$category['thumb'] = $this->model_tool_image->resize($category['image'], $setting['image_width'], $setting['image_height']);
				else
					$category['thumb'] = '';
			}
			unset($category);
		}

		if (!empty($data['categories']))
			return $this->load->view('extension/module/catalog_category', $data);
	}
}
