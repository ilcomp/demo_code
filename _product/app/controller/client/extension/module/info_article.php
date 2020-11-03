<?php
namespace Controller\Extension\Module;

class InfoArticle extends \Controller {
	public function index($setting) {
		$this->load->language('extension/module/info_article');

		$this->load->model('info/article');

		$data = $setting;

		$data['type'] = $data['filter'];

		if (!$setting['limit'])
			$setting['limit'] = 4;

		if (!empty($data['title']))
			$data['heading_title'] = $data['title'];

		switch ($setting['filter']) {
			case 'bestseller':
				$data['articles'] = $this->model_info_article->getBestSellerArticles($setting['limit']);

				break;
			case 'featured':
				if (!empty($setting['article']))
					$article_ids = array_slice($setting['article'], 0, (int)$setting['limit']);

				$data['articles'] = array();

				foreach ($article_ids as $article_id) {
					$article = $this->model_info_article->getArticle($article_id);

					if ($article)
						$data['articles'][$article['article_id']] = $article;
				}

				break;
			case 'latest':
				$filter_data = array(
					'filter' => array(
						'category_id' => $setting['main_category_id'],
						'sub_category' => true
					),
					'sort'  => 'date_added',
					'order' => 'DESC',
					'start' => 0,
					'limit' => $setting['limit']
				);

				$data['articles'] = $this->model_info_article->getArticles($filter_data);

				$this->load->model('info/category');

				$data['category'] = $this->model_info_category->getCategory($setting['main_category_id']);

				if ($data['category'])
					$data['category']['href'] = $this->url->link('info/category', 'info_category_id=' . $setting['main_category_id']);

				break;
			default:
				if (!isset($data['articles']))
					$data['articles'] = array();

				break;
		}

		if (!empty($data['articles']))
			return $this->load->controller('info/article_list', $data);
	}
}