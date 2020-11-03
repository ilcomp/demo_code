<?php
namespace Controller\Extension\Module;

class CatalogProduct extends \Controller {
	public function index($setting) {
		$this->load->language('extension/module/catalog_product');

		$this->load->model('catalog/product');

		$data = $setting;

		$data['type'] = $data['filter'];

		if (!$setting['limit'])
			$setting['limit'] = 4;

		if (!empty($data['title']))
			$data['heading_title'] = $data['title'];

		switch ($setting['filter']) {
			case 'bestseller':
				$data['products'] = $this->model_catalog_product->getBestSellerProducts($setting['limit']);

				break;
			case 'featured':
				if (!empty($setting['product']))
					$product_ids = array_slice($setting['product'], 0, (int)$setting['limit']);

				$data['products'] = array();

				foreach ($product_ids as $product_id) {
					$product = $this->model_catalog_product->getProduct($product_id);

					if ($product)
						$data['products'][$product['product_id']] = $product;
				}

				break;
			case 'latest':
				$filter_data = array(
					'sort'  => 'date_added',
					'order' => 'DESC',
					'start' => 0,
					'limit' => $setting['limit']
				);

				$data['products'] = $this->model_catalog_product->getProducts($filter_data);

				break;
			case 'special':
				$filter_data = array(
					'filter' => array('special' => true),
					'sort'  => 'name',
					'order' => 'ASC',
					'start' => 0,
					'limit' => $setting['limit']
				);

				$data['products'] = $this->model_catalog_product->getProducts($filter_data);

				break;
			default:
				if (!isset($data['products']))
					$data['products'] = array();

				break;
		}

		if (!empty($data['products']))
			return $this->load->controller('catalog/product_list', $data);
	}
}
