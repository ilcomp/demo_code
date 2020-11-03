<?php
namespace Controller\Info;

class Category extends \Controller {
	public function index() {
		$category_id = isset($this->request->get['info_category_id']) ? $this->request->get['info_category_id'] : 0;
		$page = !empty($this->request->get['page']) ? $this->request->get['page'] : 1;

		$this->load->model('info/category');

		$this->load->language('info/category');

		$category_info = $this->model_info_category->getCategory($category_id);

		if (!$category_info) {
			$this->config->set('error', $this->language->get('error_not_found'));

			return new \Action('error/not_found');
		}

		if (isset($category_info['redirect'])) {
			$this->response->redirect($category_info['redirect']);
		}

		$data = $category_info;

		$data['continue'] = $this->url->link('common/home');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$categories = $this->model_info_category->getCategoryPath($category_id);

		foreach ($categories as $category) {
			$data['breadcrumbs'][] = array(
				'text' => $category['title'] ? $category['title'] : $category['name'],
				'href' => $this->url->link('info/category', 'info_category_id=' . $category['path_id'])
			);
		}

		$this->document->setTitle($category_info['meta_title'] ? $category_info['meta_title'] : $category_info['name']);
		$this->document->setDescription($category_info['meta_description']);
		$this->document->addLink($this->url->link('info/category', 'info_category_id=' . $category_id), 'canonical');

		$data['heading_title'] = $category_info['title'] ? $category_info['title'] : $category_info['name'];

		$this->load->model('core/custom_field');

		$data['custom_fields'] = array();

		$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('info_category');
		$custom_field_values = $this->model_info_category->getCategoryCustomFields($category_info['category_id']);

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

		$limit = (int)$category_info['setting']['limit'];
		$sort = $category_info['setting']['sort_by'];
		$order = $category_info['setting']['sort_direction'];

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $limit,
			'limit' => $limit,
			'filter' => array(
				'category_id' => $category_id,
				'sub_category' => true
			)
		);

		$this->load->model('info/article');

		$article_total = $this->model_info_article->getTotalArticles($filter_data);

		$articles = $this->model_info_article->getArticles($filter_data);

		$data['article_list'] = $this->load->controller('info/article_list', array(
			'articles' => $articles,
			'category_id' => $category_id
		));

		if ($limit > 0) {
			$url = $this->request->get;
			unset($url['route']);
			unset($url['_route_']);
			$url['page'] = '{page}';

			$data['pagination'] = $this->load->controller('block/pagination', array(
				'total' => $article_total,
				'page'  => $page,
				'limit' => $limit,
				'url'   => $this->url->link('info/category',  $url)
			));

			$data['results'] = $this->load->controller('block/pagination/result', array(
				'total' => $article_total,
				'page'  => $page,
				'limit' => $limit
			));
		} else {
			$data['pagination'] = '';
			$data['results'] = '';
		}

		$data['page'] = $page;
		$data['limit'] = $limit;
		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['content'] = $this->load->view('info/category', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}
}
