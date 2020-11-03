<?php
namespace Controller\Event;

class InfoPermission extends \Controller {
	public function index() {
		$this->event->register('model/info/category/getCategory/before', new \Action('event/info_permission/getCategory'), 0);
		$this->event->register('model/info/category/getCategories/before', new \Action('event/info_permission/getCategories'), 0);
		$this->event->register('model/info/article/getArticle/before', new \Action('event/info_permission/getArticle'), 0);
		$this->event->register('model/info/article/getArticles/before', new \Action('event/info_permission/getArticles'), 0);
		$this->event->register('model/info/article/getTotalArticles/before', new \Action('event/info_permission/getArticles'), 0);
		$this->event->register('model/info/article/getArticleRelated/before', new \Action('event/info_permission/getArticle'), 0);
	}

	public function getCategory($route, &$data) {
		$filter = array(
			'article' => true,
			'store_id' => $this->config->get('config_store_id'),
			'status' => 1
		);

		if (!isset($data[1]['filter']))
			$data[1]['filter'] = array();

		$data[1]['filter'] = array_merge($data[1]['filter'], $filter);
	}

	public function getCategories($route, &$data) {
		$filter = array(
			'article' => true,
			'store_id' => $this->config->get('config_store_id'),
			'status' => 1
		);

		if (!isset($data[0]['filter']))
			$data[0]['filter'] = array();

		$data[0]['filter'] = array_merge($data[0]['filter'], $filter);
	}

	public function getArticle($route, &$data) {
		$filter = array(
			'store_id' => $this->config->get('config_store_id'),
			'status' => 1,
			'date_available' => true
		);

		if (!isset($data[1]['filter']))
			$data[1]['filter'] = array();

		$data[1]['filter'] = array_merge($data[1]['filter'], $filter);
	}

	public function getArticles($route, &$data) {
		$filter = array(
			'store_id' => $this->config->get('config_store_id'),
			'status' => 1,
			'date_available' => true
		);

		if (!isset($data[0]['filter']))
			$data[0]['filter'] = array();

		$data[0]['filter'] = array_merge($data[0]['filter'], $filter);
	}
}