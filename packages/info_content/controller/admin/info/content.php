<?php
namespace Controller\Info;

class Content extends \Controller {
	private $error = array();

	public function index() {
		$filter_article_id = isset($this->request->get['filter_article_id']) ? $this->request->get['filter_article_id'] : '';

		$this->load->model('info/article');

		$article_info = $this->model_info_article->getArticle($filter_article_id);

		if (!$article_info)
			return new \Action('error/not_found');

		$this->load->language('info/content');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('info/content');

		$this->getList($article_info);
	}

	public function add() {
		$filter_article_id = isset($this->request->get['filter_article_id']) ? $this->request->get['filter_article_id'] : '';

		$this->load->model('info/article');

		$article_info = $this->model_info_article->getArticle($filter_article_id);

		if (!$article_info)
			return new \Action('error/not_found');

		$this->load->language('info/content');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('info/content');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->request->post['article_id'] = $this->request->get['filter_article_id'];

			$content_id = $this->model_info_content->addContent($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = 'user_token=' . $this->session->data['user_token'];

			$url .= '&filter_article_id=' . $article_info['article_id'];

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->post['method']) && $this->request->post['method'] == 'save-exit') {
				$this->response->redirect($this->url->link('info/content', $url));
			} else {
				$this->response->redirect($this->url->link('info/content/edit', $url . '&info_content_id=' . $content_id));
			}
		}

		$this->getForm($article_info);
	}

	public function edit() {
		$filter_article_id = isset($this->request->get['filter_article_id']) ? $this->request->get['filter_article_id'] : '';

		$this->load->model('info/article');

		$article_info = $this->model_info_article->getArticle($filter_article_id);

		if (!$article_info)
			return new \Action('error/not_found');

		$this->load->language('info/content');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('info/content');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->request->post['article_id'] = $this->request->get['filter_article_id'];

			$this->model_info_content->editContent($this->request->get['info_content_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = 'user_token=' . $this->session->data['user_token'];

			$url .= '&filter_article_id=' . $article_info['article_id'];

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->post['method']) && $this->request->post['method'] == 'save-exit') {
				$this->response->redirect($this->url->link('info/content', $url));
			} else {
				$this->response->redirect($this->url->link('info/content/edit', $url . '&info_content_id=' . $this->request->get['info_content_id']));
			}
		}

		$this->getForm($article_info);
	}

	public function delete() {
		$filter_article_id = isset($this->request->get['filter_article_id']) ? $this->request->get['filter_article_id'] : '';

		$this->load->model('info/article');

		$article_info = $this->model_info_article->getArticle($filter_article_id);

		if (!$article_info)
			return new \Action('error/not_found');

		$this->load->language('info/content');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('info/content');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $content_id) {
				$this->model_info_content->deleteContent($content_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = 'user_token=' . $this->session->data['user_token'];

			$url .= '&filter_article_id=' . $article_info['article_id'];

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			$this->response->redirect($this->url->link('info/content', $url));
		}

		$this->getList($article_info);
	}

	protected function getList($article_info) {
		$data['heading_title'] = $article_info['name'];

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

		$url = 'user_token=' . $this->session->data['user_token'];

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $url)
		);

		$url .= '&filter_article_id=' . $article_info['article_id'];

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('info/content',  $url)
		);

		$data['actions']['add'] = $this->url->link('info/content/add', $url);
		$data['actions']['delete'] = $this->url->link('info/content/delete', $url);

		$filter_data = array(
			'filter' => array(
				'article_id' => $article_info['article_id']
			),
			'sort' => $sort,
			'order' => $order
		);

		$data['contents'] = $this->model_info_content->getContents($filter_data);

		foreach ($data['contents'] as &$content) {
			$content['status'] = $content['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled');
			$content['edit'] = $this->url->link('info/content/edit', $url . '&info_content_id=' . $content['content_id']);
		}
		unset($content);

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

		if ($order == 'ASC') {
			$url['order'] = 'DESC';
		} else {
			$url['order'] = 'ASC';
		}

		foreach (array('name', 'article', 'status', 'date_available', 'date_modified', 'sort_order') as $value) {
			$url['sort'] = $value;
			$data['sort_' . $value] = $this->url->link('info/content', $url);
		}

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['content'] = $this->load->view('info/content_list', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function getForm($article_info) {
		$data['heading_title'] = $article_info['name'];

		$data['text_form'] = !isset($this->request->get['info_content_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

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

		$url = 'user_token=' . $this->session->data['user_token'];

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $url)
		);

		$url .= '&filter_article_id=' . $article_info['article_id'];

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('info/content',  $url)
		);

		if (!isset($this->request->get['info_content_id'])) {
			$data['action'] = $this->url->link('info/content/add', $url);
		} else {
			$data['action'] = $this->url->link('info/content/edit', $url . '&info_content_id=' . $this->request->get['info_content_id']);
		}

		$data['actions']['add'] = $this->url->link('info/content/add', $url);
		$data['actions'][] = 'separator';
		$data['actions']['save'] = true;
		$data['actions']['save_exit'] = true;
		$data['actions'][] = 'separator';
		$data['actions']['cancel'] = $this->url->link('info/content', $url);

		if (isset($this->request->get['info_content_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$content_info = $this->model_info_content->getContent($this->request->get['info_content_id']);
		}

		if (isset($this->request->post['content_description'])) {
			$data['content_description'] = $this->request->post['content_description'];
		} elseif (!empty($content_info)) {
			$data['content_description'] = $this->model_info_content->getContentDescriptions($content_info['content_id']);
		} else {
			$data['content_description'] = array();
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($content_info)) {
			$data['status'] = $content_info['status'];
		} else {
			$data['status'] = true;
		}

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($content_info)) {
			$data['sort_order'] = $content_info['sort_order'];
		} else {
			$data['sort_order'] = 0;
		}

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['custom_field'])) {
			$custom_field_values = $this->request->post['custom_field'];
		} elseif (!empty($content_info)) {
			$custom_field_values = $this->model_info_content->getContentCustomFields($content_info['content_id']);
		} else {
			$custom_field_values = array();
		}

		$location = 'info_content_' . $article_info['article_id'];

		$this->load->model('core/custom_field');

		$custom_fields = $this->model_core_custom_field->getAsField($custom_field_values, $location);

		$data['custom_fields'] = $this->load->controller('setting/custom_field/render', $custom_fields);

		$data['additional_fields'] = '';

		$data['content'] = $this->load->view('info/content_form', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'info/content')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['content_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 1) || (utf8_strlen($value['name']) > 255)) {
				$this->error['name'][$language_id] = $this->language->get('error_name');
			}
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'info/content')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}