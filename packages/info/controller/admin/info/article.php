<?php
namespace Controller\Info;

class Article extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('info/article');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('info/article_modify');

		$this->getList();
	}

	public function add() {
		$this->load->language('info/article');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('info/article_modify');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$article_id = $this->model_info_article_modify->addArticle($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = 'user_token=' . $this->session->data['user_token'];

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}

			if (isset($this->request->get['filter_category_id'])) {
				$url .= '&filter_category_id=' . $this->request->get['filter_category_id'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->post['method']) && $this->request->post['method'] == 'save-exit') {
				$this->response->redirect($this->url->link('info/article', $url));
			} else {
				$this->response->redirect($this->url->link('info/article/edit', $url . '&info_article_id=' . $article_id));
			}
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('info/article');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('info/article_modify');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_info_article_modify->editArticle($this->request->get['info_article_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = 'user_token=' . $this->session->data['user_token'];

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}

			if (isset($this->request->get['filter_category_id'])) {
				$url .= '&filter_category_id=' . $this->request->get['filter_category_id'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->post['method']) && $this->request->post['method'] == 'save-exit') {
				$this->response->redirect($this->url->link('info/article', $url));
			} else {
				$this->response->redirect($this->url->link('info/article/edit', $url . '&info_article_id=' . $this->request->get['info_article_id']));
			}
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('info/article');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('info/article_modify');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $article_id) {
				$this->model_info_article_modify->deleteArticle($article_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}

			if (isset($this->request->get['filter_category_id'])) {
				$url .= '&filter_category_id=' . $this->request->get['filter_category_id'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('info/article', 'user_token=' . $this->session->data['user_token'] . $url));
		}

		$this->getList();
	}

	public function copy() {
		$this->load->language('info/article');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('info/article_modify');

		if (isset($this->request->post['selected']) && $this->validateCopy()) {
			foreach ($this->request->post['selected'] as $article_id) {
				$this->model_info_article_modify->copyArticle($article_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}

			if (isset($this->request->get['filter_category_id'])) {
				$url .= '&filter_category_id=' . $this->request->get['filter_category_id'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('info/article', 'user_token=' . $this->session->data['user_token'] . $url));
		}

		$this->getList();
	}

	protected function getList() {
		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = '';
		}

		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = '';
		}

		if (isset($this->request->get['filter_category_id'])) {
			$filter_category_id = $this->request->get['filter_category_id'];
		} else {
			$filter_category_id = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_category_id'])) {
			$url .= '&filter_category_id=' . $this->request->get['filter_category_id'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('info/article', 'user_token=' . $this->session->data['user_token'] . $url)
		);

		$data['actions']['add'] = $this->url->link('info/article/add', 'user_token=' . $this->session->data['user_token'] . $url);
		$data['actions']['delete'] = $this->url->link('info/article/delete', 'user_token=' . $this->session->data['user_token'] . $url);

		$filter_data = array(
			'filter' => array(),
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit'),
			'limit' => $this->config->get('config_limit')
		);

		if (!empty($filter_name))
			$filter_data['filter']['name'] = $filter_name;

		if ($filter_category_id == -1) {
			$filter_data['filter']['category_id'] = 0;
		} elseif ($filter_category_id != '') {
			$filter_data['filter']['category_id'] = $filter_category_id;
			$filter_data['filter']['sub_category'] = 1;
		}

		if ($filter_status != '')
			$filter_data['filter']['status'] = $filter_status;

		$article_total = $this->model_info_article_modify->getTotalArticles($filter_data);

		$data['articles'] = $this->model_info_article_modify->getArticles($filter_data);

		$this->load->model('info/category');

		$client_url = new \Url(HTTP_APPLICATION_CLIENT);

		foreach ($data['articles'] as &$article) {
			$article['categories'] = array();

			$categories = $this->model_info_article_modify->getArticleCategories($article['article_id']);

			foreach ($categories as $category) {
				$paths = $this->model_info_category->getCategoryPath($category['category_id']);

				$article['categories'][] = implode(' -> ', array_column($paths, 'name'));
			}

			$article['status'] = $article['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled');
			$article['view'] = $client_url->link('info/article', 'info_article_id=' . $article['article_id']);
			$article['edit'] = $this->url->link('info/article/edit', 'user_token=' . $this->session->data['user_token'] . '&info_article_id=' . $article['article_id'] . $url);
		}
		unset($article);

		$data['user_token'] = $this->session->data['user_token'];

		$filter_data = array(
			'sort'        => 'name',
			'order'       => 'ASC'
		);

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = $this->request->get;
		unset($url['route']);
		unset($url['_route_']);
		unset($url['page']);

		if ($order == 'ASC') {
			$url['order'] = 'DESC';
		} else {
			$url['order'] = 'ASC';
		}

		foreach (array('name', 'category', 'status', 'date_available', 'date_modified', 'sort_order') as $value) {
			$url['sort'] = $value;
			$data['sort_' . $value] = $this->url->link('info/article', $url);
		}

		$url = $this->request->get;
		unset($url['route']);
		unset($url['_route_']);
		$url['page'] = '{page}';

		$data['pagination'] = $this->load->controller('block/pagination', array(
			'total' => $article_total,
			'page'  => $page,
			'limit' => $this->config->get('config_limit'),
			'url'   => $this->url->link('info/article', $url)
		));

		$data['results'] = $this->load->controller('block/pagination/result', array(
			'total' => $article_total,
			'page'  => $page,
			'limit' => $this->config->get('config_limit')
		));

		$data['filter_name'] = $filter_name;
		$data['filter_status'] = $filter_status;
		$data['filter_category_id'] = $filter_category_id;
		$data['sort'] = $sort;
		$data['order'] = $order;

		$filter_category = $this->model_info_category->getCategoryPath($filter_category_id);

		$data['filter_category'] = implode(' &gt; ', array_column($filter_category, 'name'));

		$data['content'] = $this->load->view('info/article_list', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['info_article_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = array();
		}

		if (isset($this->error['keyword'])) {
			$data['error_keyword'] = $this->error['keyword'];
		} else {
			$data['error_keyword'] = '';
		}

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_category_id'])) {
			$url .= '&filter_category_id=' . $this->request->get['filter_category_id'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('info/article', 'user_token=' . $this->session->data['user_token'] . $url)
		);

		if (!isset($this->request->get['info_article_id'])) {
			$data['action'] = $this->url->link('info/article/add', 'user_token=' . $this->session->data['user_token'] . $url);
		} else {
			$data['action'] = $this->url->link('info/article/edit', 'user_token=' . $this->session->data['user_token'] . '&info_article_id=' . $this->request->get['info_article_id'] . $url);
		}

		if (isset($this->request->get['info_article_id'])) {
			$catalog_url = new \Url(HTTP_APPLICATION_CLIENT);

			$data['actions']['view'] = $catalog_url->link('info/article', 'info_article_id=' . $this->request->get['info_article_id']);
			$data['actions'][] = 'separator';
		}

		$data['actions']['add'] = $this->url->link('info/article/add', 'user_token=' . $this->session->data['user_token'] . $url);
		$data['actions'][] = 'separator';
		$data['actions']['save'] = true;
		$data['actions']['save_exit'] = true;
		$data['actions'][] = 'separator';
		$data['actions']['cancel'] = $this->url->link('info/article', 'user_token=' . $this->session->data['user_token'] . $url);

		$client_url = new \Url(HTTP_APPLICATION_CLIENT);

		$data['view'] = isset($this->request->get['info_article_id']) ? $client_url->link('info/article', 'info_article_id=' . $this->request->get['info_article_id']) : '';

		if (isset($this->request->get['info_article_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$article_info = $this->model_info_article_modify->getArticle($this->request->get['info_article_id']);
		}

		// Categories
		$this->load->model('info/category_modify');

		$data['categories'] = $this->model_info_category_modify->getCategories(array('filter' => array('path_name' => '')));

		if (isset($this->request->post['article_category'])) {
			$categories = (array)$this->request->post['article_category'];
		} elseif (!empty($article_info)) {
			$categories = (array)$this->model_info_article_modify->getArticleCategories($article_info['article_id']);
		} else {
			$categories = array();

			if (isset($this->request->get['filter_category_id'])) {
				$categories[] = array(
					'category_id' => $this->request->get['filter_category_id'],
					'main' => 1
				);
			}
		}

		$data['article_categories'] = array();

		foreach ($categories as $category) {
			$category_info = $this->model_info_category_modify->getCategory($category['category_id']);

			if ($category_info) {
				if ($category['main']) {
					$data['main_category_id'] = $category['category_id'];
				} else {
					$category_path = $this->model_info_category_modify->getCategoryPath($category['category_id']);

					$data['article_categories'][] = array(
						'category_id' => $category_info['category_id'],
						'name'        => implode(' &gt; ', array_column($category_path, 'name'))
					);
				}
			}
		}

		if (isset($this->request->post['article_description'])) {
			$data['article_description'] = $this->request->post['article_description'];
		} elseif (!empty($article_info)) {
			$data['article_description'] = $this->model_info_article_modify->getArticleDescriptions($article_info['article_id']);
		} else {
			$data['article_description'] = array();
		}

		if (isset($this->request->post['article_store'])) {
			$data['article_store'] = $this->request->post['article_store'];
		} elseif (!empty($article_info)) {
			$data['article_store'] = $this->model_info_article_modify->getArticleStores($article_info['article_id']);
		} else {
			$data['article_store'] = array(0);
		}

		if (isset($this->request->post['date_available'])) {
			$data['date_available'] = $this->request->post['date_available'];
		} elseif (!empty($article_info)) {
			$data['date_available'] = ($article_info['date_available'] != '0000-00-00') ? $article_info['date_available'] : '';
		} else {
			$data['date_available'] = date('Y-m-d H:i:s');
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($article_info)) {
			$data['status'] = $article_info['status'];
		} else {
			$data['status'] = true;
		}

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($article_info)) {
			$data['sort_order'] = $article_info['sort_order'];
		} else {
			$data['sort_order'] = 500;
		}

		if (isset($this->request->post['article_seo_url'])) {
			$data['article_seo_url'] = $this->request->post['article_seo_url'];
		} elseif (isset($this->request->get['info_article_id'])) {
			$data['article_seo_url'] = $this->model_info_article_modify->getArticleSeoUrls($this->request->get['info_article_id']);
		} else {
			$data['article_seo_url'] = array();
		}

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$this->load->model('core/store');

		$data['stores'] = array();

		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		);

		$stores = $this->model_core_store->getStores();

		foreach ($stores as $store) {
			$data['stores'][] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			);
		}

		if (isset($this->request->post['custom_field'])) {
			$custom_field_values = $this->request->post['custom_field'];
		} elseif (!empty($article_info)) {
			$custom_field_values = $this->model_info_article_modify->getArticleCustomFields($article_info['article_id']);
		} else {
			$custom_field_values = array();
		}

		if (!empty($data['main_category_id'])) {
			$categories = $this->model_info_category_modify->getCategoryPath($data['main_category_id']);

			$category = array_shift($categories);
		} else {
			$category = '';
		}

		if ($category)
			$location = 'info_article_' . $category['path_id'];

		if (!empty($article_info) && $this->config->get('info_home_page') == $article_info['article_id']) {
			$location = 'info_home';
		} elseif (!empty($article_info) && $this->config->get('info_contact_page') == $article_info['article_id']) {
			$location = 'info_contact';
		} elseif (!empty($category)) {
			$location = 'info_article_' . $category['path_id'];
		} else {
			$location = 'info_article';
		}

		$this->load->model('core/custom_field');

		$custom_fields = $this->model_core_custom_field->getAsField($custom_field_values, $location);

		$data['custom_fields'] = $this->load->controller('setting/custom_field/render', $custom_fields);

		$data['additional_fields'] = '';

		$data['content'] = $this->load->view('info/article_form', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'info/article')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['article_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 1) || (utf8_strlen($value['name']) > 64)) {
				$this->error['name'][$language_id] = $this->language->get('error_name');
			}
		}

		if ($this->request->post['article_seo_url']) {
			$this->load->model('design/seo_url');

			foreach ($this->request->post['article_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$seo_urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword, array('store_id' => $store_id, 'language_id' => $language_id));

						foreach ($seo_urls as $seo_url) {
							if (!isset($this->request->get['info_article_id']) || $seo_url['query'] != 'info_article_id=' . $this->request->get['info_article_id']) {
								$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_keyword');
							}
						}
					}
				}
			}
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'info/article')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	protected function validateCopy() {
		if (!$this->user->hasPermission('modify', 'info/article')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('info/article_modify');

			$filter_data = array(
				'filter' => array(
					'name' => $this->request->get['filter_name'],
					'category_id' => isset($this->request->get['filter_category_id']) ? (int)$this->request->get['filter_category_id'] : 0
				),
				'sort'        => 'name',
				'order'       => 'ASC',
				'start'       => 0,
				'limit'       => 5
			);

			$results = $this->model_info_article_modify->getArticles($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'article_id'	=> $result['article_id'],
					'name'				=> strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
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

	public function autocomplete_link() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('info/article_modify');
			$this->load->model('localisation/language');

			$languages = $this->model_localisation_language->getLanguages();

			$languages = array_column($languages, 'code', 'language_id');

			$filter_data = array(
				'filter' => array(
					'name' => $this->request->get['filter_name']
				),
				'language_id' => $this->request->get['language_id'],
				'sort'        => 'name',
				'order'       => 'ASC',
				'start'       => 0,
				'limit'       => 5
			);

			$results = $this->model_info_article_modify->getArticles($filter_data);

			$client = new \Url('');

			foreach ($results as $result) {
				$url = array();
				$url['info_article_id'] = $result['article_id'];

				if (isset($languages[$result['language_id']]))
					$url['language'] = $languages[$result['language_id']];

				$json[] = array(
					'article_id'     => $result['article_id'],
					'link'           => str_replace('&amp;', '&', $client->link('info/article', $url)),
					'name'           => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
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