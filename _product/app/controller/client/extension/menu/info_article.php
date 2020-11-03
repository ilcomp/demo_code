<?php
namespace Controller\Extension\Menu;

class InfoArticle extends \Controller {
	public function index($menu_item) {
		if (isset($menu_item['get']['route']) && $menu_item['get']['route'] == 'info/article' && isset($menu_item['get']['info_article_id'])) {
			$data['active'] = isset($this->request->get['route']) && $this->request->get['route'] == 'info/article' && isset($this->request->get['info_article_id']) && $this->request->get['info_article_id'] == $menu_item['get']['info_article_id'];

			$data['article_id'] = $menu_item['get']['info_article_id'];

			$url = $menu_item['get'];
			unset($url['route']);
			unset($url['info_article_id']);

			if ($this->config->get('info_home_page') && $this->config->get('info_home_page') == $menu_item['get']['info_article_id']) {
				$data['href'] = $this->url->link('common/home', $url);
			} elseif ($this->config->get('info_contact_page') && $this->config->get('info_contact_page') == $menu_item['get']['info_article_id']) {
				$data['href'] = $this->url->link('common/contact', $url);
			}

			return $data;
		}
	}
}