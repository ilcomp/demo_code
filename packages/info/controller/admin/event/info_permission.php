<?php
namespace Controller\Event;

class InfoPermission extends \Controller {
	public function index() {
		$this->event->register('model/info/category/getCategory/before', new \Action('event/info_permission/getCategory'), 0);
		$this->event->register('model/info/category/getCategories/before', new \Action('event/info_permission/getCategories'), 0);
		$this->event->register('model/info/article_modify/getArticle/before', new \Action('event/info_permission/getArticle'), 0);
		$this->event->register('model/info/article_modify/getArticles/before', new \Action('event/info_permission/getArticles'), 0);
		$this->event->register('model/info/article/getTotalArticles/before', new \Action('event/info_permission/getArticles'), 0);
		$this->event->register('model/info/article/getArticleRelated/before', new \Action('event/info_permission/getArticle'), 0);
	}

	public function getCategory($route, &$data) {
		$data[1]['language_id'] = $this->config->get('config_language_id');
	}

	public function getCategories($route, &$data) {
		$data[0]['language_id'] = $this->config->get('config_language_id');
	}

	public function getArticle($route, &$data) {
		$data[1]['language_id'] = $this->config->get('config_language_id');
	}

	public function getArticles($route, &$data) {
		$data[0]['language_id'] = $this->config->get('config_language_id');
	}
}