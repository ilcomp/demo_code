<?php
namespace Controller\Info;

class Article extends \Controller {
	public function index() {
		$article_id = isset($this->request->get['info_article_id']) ? $this->request->get['info_article_id'] : 0;

		$this->load->language('info/article');

		$this->load->model('info/article');

		$article_info = $this->model_info_article->getArticle($article_id);

		if (!$article_info) {
			$this->config->set('error', $this->language->get('error_not_found'));

			return new \Action('error/not_found');
		}

		if ($article_id == $this->config->get('info_home_page'))
			$this->response->redirect($this->url->link('common/home'), 301);

		if ($article_id == $this->config->get('info_contact_page'))
			$this->response->redirect($this->url->link('common/contact'), 301);

		$data = $article_info;

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$this->load->model('info/category');

		$url = $this->request->get;
		unset($url['route']);
		unset($url['_route_']);
		unset($url['info_article_id']);

		$category_id = $this->model_info_article->getArticleMainCategoryId($article_id);

		if ($category_id) {
			$categories = $this->model_info_category->getCategoryPath($category_id);

			foreach ($categories as $category) {
				$url['info_category_id'] = $category['path_id'];

				$data['breadcrumbs'][] = array(
					'text' => $category['title'] ? $category['title'] : $category['name'],
					'href' => $this->url->link('info/category', $url)
				);
			}
		}

		$data['category_id'] = $category_id;

		$category_info = $this->model_info_category->getCategory($category_id);

		if (!$category_info)
			$category_info['setting'] = array();

		if (!isset($category_info['setting']['show_preview']))
			$category_info['setting']['show_preview'] = 1;

		if (!isset($category_info['setting']['template_article']))
			$category_info['setting']['template_article'] = 'static';

		$url = $this->request->get;
		unset($url['route']);
		unset($url['_route_']);
		$url['info_article_id'] = $article_info['article_id'];

		$data['breadcrumbs'][] = array(
			'text' => $article_info['title'] ? $article_info['title'] : $article_info['name'],
			'href' => $this->url->link('info/article', $url)
		);

		$this->document->setTitle($article_info['meta_title'] ? $article_info['meta_title'] : $article_info['name']);
		$this->document->setDescription($article_info['meta_description']);
		$this->document->addLink($this->url->link('info/article', 'info_article_id=' . $article_id), 'canonical');

		$data['heading_title'] = $article_info['title'] ? $article_info['title'] : $article_info['name'];

		$data['share'] = $this->url->link('info/article', 'info_article_id=' . (int)$article_info['article_id']);

		$data['date'] = strftime($this->language->get('date_format'), strtotime($article_info['date_available']));

		$this->load->model('core/custom_field');

		$data['custom_fields'] = array();

		$categories = $this->model_info_category->getCategoryPath($category_id);

		$category = array_shift($categories);

		if ($category)
			$location = 'info_article_' . $category['path_id'];
		else
			$location = 'info_article';

		$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation($location);
		$custom_field_values = $this->model_info_article->getArticleCustomFields($article_info['article_id']);

		foreach ($custom_fields as $custom_field) {
			$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

			if (isset($custom_field_values[$custom_field['custom_field_id']]) && isset($custom_field_values[$custom_field['custom_field_id']][$language_id])) {
				$value = $custom_field_values[$custom_field['custom_field_id']][$language_id];
			} elseif (isset($custom_field['setting']['default']))
				$value = $custom_field['setting']['default'];
			else
				$value = '';

			$data['custom_fields'][$custom_field['code']] = $this->model_core_custom_field->getAsValue($value, $custom_field);
		}

		$data['tags'] = array();

		if ($article_info['tag']) {
			$tags = explode(',', $article_info['tag']);

			foreach ($tags as $tag) {
				$data['tags'][] = array(
					'tag'  => trim($tag),
					'href' => $this->url->link('common/search', 'tag=' . trim($tag))
				);
			}
		}

		$data['content'] = $this->load->view('info/article', $data);

		if ($this->request->server['HTTP_ACCEPT'] == 'text/html') {
			$this->response->setOutput($data['content']);
		} else {
			$this->response->setOutput($this->load->controller('common/template', $data));
		}
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('info/article_modify');

			$filter_data = array(
				'filter' => array(
					'search' => $this->request->get['filter_name']
				),
				'sort'        => 'name',
				'order'       => 'ASC',
				'start'       => 0,
				'limit'       => 5
			);

			if (isset($this->request->get['filter_category_id'])) {
				$filter_data['filter']['category_id'] = $this->request->get['filter_category_id'];
				$filter_data['filter']['sub_category'] = true;
			}

			$results = $this->model_info_article_modify->getArticles($filter_data);

			foreach ($results as $result) {
				if (!$result['title'])
					$result['title'] = $result['name'];

				$json[] = array(
					'article_id' => $result['article_id'],
					'name'       => $result['name'],
					'title'      => $result['title']
				);
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}
}