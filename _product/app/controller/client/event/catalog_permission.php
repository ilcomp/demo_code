<?php
namespace Controller\Event;

class CatalogPermission extends \Controller {
	public function index() {
		$this->event->register('model/catalog/category/getCategory/before', new \Action('event/catalog_permission/getCategory'), 0);
		$this->event->register('model/catalog/category/getCategories/before', new \Action('event/catalog_permission/getCategories'), 0);
		$this->event->register('model/catalog/product/getProduct/before', new \Action('event/catalog_permission/getProduct'), 0);
		$this->event->register('model/catalog/product/getProducts/before', new \Action('event/catalog_permission/getProducts'), 0);
		$this->event->register('model/catalog/product/getTotalProducts/before', new \Action('event/catalog_permission/getProducts'), 0);
		$this->event->register('model/catalog/product/getProductRelated/before', new \Action('event/catalog_permission/getProduct'), 0);
	}

	public function getCategory($route, &$data) {
		$filter = array(
			'product' => true,
			'store_id' => $this->config->get('config_store_id'),
			'status' => 1
		);

		if (!isset($data[1]['filter']))
			$data[1]['filter'] = array();

		$data[1]['filter'] = array_merge($data[1]['filter'], $filter);
	}

	public function getCategories($route, &$data) {
		$filter = array(
			'product' => true,
			'store_id' => $this->config->get('config_store_id'),
			'status' => 1
		);

		if (!isset($data[0]['filter']))
			$data[0]['filter'] = array();

		$data[0]['filter'] = array_merge($data[0]['filter'], $filter);
	}

	public function getProduct($route, &$data) {
		$filter = array(
			'store_id' => $this->config->get('config_store_id'),
			'price_id' => $this->config->get('catalog_price_id'),
			'status' => 1,
			'date_available' => true
		);

		if (!isset($data[1]['filter']))
			$data[1]['filter'] = array();

		$data[1]['filter'] = array_merge($data[1]['filter'], $filter);
	}

	public function getProducts($route, &$data) {
		$filter = array(
			'store_id' => $this->config->get('config_store_id'),
			'price_id' => $this->config->get('catalog_price_id'),
			'status' => 1,
			'date_available' => true
		);

		if (!isset($data[0]['filter']))
			$data[0]['filter'] = array();

		$data[0]['filter'] = array_merge($data[0]['filter'], $filter);
	}
}