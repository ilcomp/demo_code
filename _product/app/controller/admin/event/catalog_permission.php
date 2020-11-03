<?php
namespace Controller\Event;

class CatalogPermission extends \Controller {
	public function index() {
		$this->event->register('model/catalog/category/getCategory/before', new \Action('event/catalog_permission/getCategory'), 0);
		$this->event->register('model/catalog/category/getCategories/before', new \Action('event/catalog_permission/getCategories'), 0);
		$this->event->register('model/catalog/product_modify/getProduct/before', new \Action('event/catalog_permission/getProduct'), 0);
		$this->event->register('model/catalog/product_modify/getProducts/before', new \Action('event/catalog_permission/getProducts'), 0);
		$this->event->register('model/catalog/product/getTotalProducts/before', new \Action('event/catalog_permission/getProducts'), 0);
		$this->event->register('model/catalog/product/getProductRelated/before', new \Action('event/catalog_permission/getProduct'), 0);
	}

	public function getCategory($route, &$data) {
		$data[1]['language_id'] = $this->config->get('config_language_id');
	}

	public function getCategories($route, &$data) {
		$data[0]['language_id'] = $this->config->get('config_language_id');
	}

	public function getProduct($route, &$data) {
		$data[1]['language_id'] = $this->config->get('config_language_id');
	}

	public function getProducts($route, &$data) {
		$data[0]['language_id'] = $this->config->get('config_language_id');
	}
}