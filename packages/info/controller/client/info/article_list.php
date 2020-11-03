<?php
namespace Controller\Info;

class ArticleList extends \Controller {
	public function index($data) {
		$this->load->language('info/article');

		if (!isset($data['heading_title']))
			$data['heading_title'] = '';

		foreach ($data['articles'] as &$article) {
			if (empty($article['title']))
				$article['title'] = $article['name'];

			$article['href'] = $this->url->link('info/article', 'info_article_id=' . $article['article_id']);
		}
		unset($article);

		if ($this->config->get('config_theme') == 'default') {
			$theme = $this->config->get('theme_default');
		} else {
			$theme = $this->config->get('config_theme');
		}

		$template = (isset($data['template']) && is_file(DIR_TEMPLATE . $theme . '/template/' . $data['template'] . '.twig')) ? $data['template'] : 'info/article_list';

		return (!empty($data['articles']) ? $this->load->view($template, $data) : '');
	}
}