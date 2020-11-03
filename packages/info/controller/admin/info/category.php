<?php
namespace Controller\Info;

class Category extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('info/category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('info/category_modify');

		$this->getList();
	}

	public function add() {
		$this->load->language('info/category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('info/category_modify');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$category_id = $this->model_info_category_modify->addCategory($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = 'user_token=' . $this->session->data['user_token'];

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->post['method']) && $this->request->post['method'] == 'save-exit') {
				$this->response->redirect($this->url->link('info/category', $url));
			} else {
				$this->response->redirect($this->url->link('info/category/edit', $url . '&info_category_id=' . $category_id));
			}
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('info/category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('info/category_modify');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_info_category_modify->editCategory($this->request->get['info_category_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = 'user_token=' . $this->session->data['user_token'];

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
				$this->response->redirect($this->url->link('info/category', $url));
			} else {
				$this->response->redirect($this->url->link('info/category/edit', $url . '&info_category_id=' . $this->request->get['info_category_id']));
			}
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('info/category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('info/category_modify');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $category_id) {
				$this->model_info_category_modify->deleteCategory($category_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('info/category', 'user_token=' . $this->session->data['user_token'] . $url));
		}

		$this->getList();
	}

	public function repair() {
		$this->load->language('info/category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('info/category_modify');

		if ($this->validateRepair()) {
			$this->model_info_category_modify->repairCategories();

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('info/category', 'user_token=' . $this->session->data['user_token'] . $url));
		}

		$this->getList();
	}

	protected function getList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'path_name';
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
			'href' => $this->url->link('info/category', 'user_token=' . $this->session->data['user_token'] . $url)
		);

		$data['actions']['add'] = $this->url->link('info/category/add', 'user_token=' . $this->session->data['user_token'] . $url);
		$data['actions']['delete'] = $this->url->link('info/category/delete', 'user_token=' . $this->session->data['user_token'] . $url);
		$data['actions']['repair'] = $this->url->link('info/category/repair', 'user_token=' . $this->session->data['user_token'] . $url);

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit'),
			'limit' => $this->config->get('config_limit')
		);

		$category_total = $this->model_info_category_modify->getTotalCategories();

		$this->load->model('info/article');

		$data['categories'] = $this->model_info_category_modify->getCategories($filter_data);

		foreach ($data['categories'] as &$result) {
			$result['edit'] = $this->url->link('info/category/edit', 'user_token=' . $this->session->data['user_token'] . '&info_category_id=' . $result['category_id'] . $url);
			$result['delete'] = $this->url->link('info/category/delete', 'user_token=' . $this->session->data['user_token'] . '&info_category_id=' . $result['category_id'] . $url);
		}
		unset($result);

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

		foreach (array('path_name', 'sort_order') as $value) {
			$url['sort'] = $value;
			$data['sort_' . $value] = $this->url->link('info/category', $url);
		}

		$url = $this->request->get;
		unset($url['route']);
		unset($url['_route_']);
		$url['page'] = '{page}';

		$data['pagination'] = $this->load->controller('block/pagination', array(
			'total' => $category_total,
			'page'  => $page,
			'limit' => $this->config->get('config_limit'),
			'url'   => $this->url->link('info/category', $url)
		));

		$data['results'] = $this->load->controller('block/pagination/result', array(
			'total' => $category_total,
			'page'  => $page,
			'limit' => $this->config->get('config_limit')
		));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['content'] = $this->load->view('info/category_list', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['info_category_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

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

		if (isset($this->error['limit'])) {
			$data['error_limit'] = $this->error['limit'];
		} else {
			$data['error_limit'] = '';
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		if (isset($this->request->get['path'])) {
			$url .= '&path=' . $this->request->get['path'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('info/category', 'user_token=' . $this->session->data['user_token'] . $url)
		);

		if (!isset($this->request->get['info_category_id'])) {
			$data['action'] = $this->url->link('info/category/add', 'user_token=' . $this->session->data['user_token'] . $url);
		} else {
			$data['action'] = $this->url->link('info/category/edit', 'user_token=' . $this->session->data['user_token'] . '&info_category_id=' . $this->request->get['info_category_id'] . $url);
		}

		$data['actions']['add'] = $this->url->link('info/category/add', 'user_token=' . $this->session->data['user_token'] . $url);
		$data['actions'][] = 'separator';
		$data['actions']['save'] = true;
		$data['actions']['save_exit'] = true;
		$data['actions'][] = 'separator';
		$data['actions']['cancel'] = $this->url->link('info/category', 'user_token=' . $this->session->data['user_token'] . $url);

		if (isset($this->request->get['info_category_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$category_info = $this->model_info_category_modify->getCategory($this->request->get['info_category_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['category_description'])) {
			$data['category_description'] = $this->request->post['category_description'];
		} elseif (isset($this->request->get['info_category_id'])) {
			$data['category_description'] = $this->model_info_category_modify->getCategoryDescriptions($this->request->get['info_category_id']);
		} else {
			$data['category_description'] = array();
		}

		if (isset($this->request->post['parent_id'])) {
			$data['parent_id'] = $this->request->post['parent_id'];
		} elseif (!empty($category_info)) {
			$data['parent_id'] = $category_info['parent_id'];
		} else {
			$data['parent_id'] = 0;
		}

		$data['path'] = $this->model_info_category_modify->getCategoryPath($data['parent_id']);

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

		if (isset($this->request->post['category_store'])) {
			$data['category_store'] = $this->request->post['category_store'];
		} elseif (isset($this->request->get['info_category_id'])) {
			$data['category_store'] = $this->model_info_category_modify->getCategoryStores($this->request->get['info_category_id']);
		} else {
			$data['category_store'] = array(0);
		}

		if (isset($this->request->post['category_seo_url'])) {
			$data['category_seo_url'] = $this->request->post['category_seo_url'];
		} elseif (isset($this->request->get['info_category_id'])) {
			$data['category_seo_url'] = $this->model_info_category_modify->getCategorySeoUrls($this->request->get['info_category_id']);
		} else {
			$data['category_seo_url'] = array();
		}

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($category_info)) {
			$data['sort_order'] = $category_info['sort_order'];
		} else {
			$data['sort_order'] = 0;
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($category_info)) {
			$data['status'] = $category_info['status'];
		} else {
			$data['status'] = 1;
		}

		if (isset($this->request->post['setting']['show_preview'])) {
			$data['show_preview'] = isset($this->request->post['setting']['show_preview']) ? $this->request->post['setting']['show_preview'] : 0;
		} elseif (!empty($category_info)) {
			$data['show_preview'] = isset($category_info['setting']['show_preview']) ? $category_info['setting']['show_preview'] : 0;
		} else {
			$data['show_preview'] = 0;
		}

		if (isset($this->request->post['setting']['limit'])) {
			$data['limit'] = intval($this->request->post['setting']['limit']);
		} elseif (!empty($category_info)) {
			$data['limit'] = $category_info['setting']['limit'];
		} else {
			$data['limit'] = 10;
		}

		$data['sort_by_array'] = array (
			'sort_order'     => $this->language->get('sort_by_sort_order'),
			'date_available' => $this->language->get('sort_by_date_available'),
			'name'           => $this->language->get('sort_by_name')
		);

		if (isset($this->request->post['setting']['sort_by'])) {
			$data['sort_by'] = $this->request->post['setting']['sort_by'];
		} elseif (!empty($category_info)) {
			$data['sort_by'] = $category_info['setting']['sort_by'];
		} else {
			$data['sort_by'] = 'i.date_available';
		}

		$data['sort_direction_array'] = array (
			'desc'		=> $this->language->get('sort_direction_desc'),
			'asc'		=> $this->language->get('sort_direction_asc')
		);

		if (isset($this->request->post['setting']['sort_direction'])) {
			$data['sort_direction'] = $this->request->post['setting']['sort_direction'];
		} elseif (!empty($category_info)) {
			$data['sort_direction'] = $category_info['setting']['sort_direction'];
		} else {
			$data['sort_direction'] = 'desc';
		}

		$data['info_image'] = array();

		foreach (array('category', 'list', 'thumb', 'additional') as $value) {
			$data['info_image'][$value]['name'] = $this->language->get('entry_image_' . $value);

			if (isset($this->request->post['setting']['info_image_' . $value . '_width'])) {
				$data['info_image'][$value]['width'] = $this->request->post['setting']['info_image_' . $value . '_width'];
			} elseif (!empty($category_info) && isset($category_info['setting']['info_image_' . $value . '_width'])) {
				$data['info_image'][$value]['width'] = $category_info['setting']['info_image_' . $value . '_width'];
			} else {
				$data['info_image'][$value]['width'] = $this->config->get('info_image_' . $value . '_width');
			}

			if (isset($this->request->post['setting']['info_image_' . $value . '_height'])) {
				$data['info_image'][$value]['height'] = $this->request->post['setting']['info_image_' . $value . '_height'];
			} elseif (!empty($category_info) && isset($category_info['setting']['info_image_' . $value . '_height'])) {
				$data['info_image'][$value]['height'] = $category_info['setting']['info_image_' . $value . '_height'];
			} else {
				$data['info_image'][$value]['height'] = $this->config->get('info_image_' . $value . '_height');
			}
		}

		if (isset($this->request->post['custom_field'])) {
			$custom_field_values = $this->request->post['custom_field'];
		} elseif (isset($this->request->get['info_category_id'])) {
			$custom_field_values = $this->model_info_category_modify->getCategoryCustomFields($this->request->get['info_category_id']);
		} else {
			$custom_field_values = array();
		}

		$this->load->model('core/custom_field');

		$custom_fields = $this->model_core_custom_field->getAsField($custom_field_values, 'info_category');

		$data['custom_fields'] = $this->load->controller('setting/custom_field/render', $custom_fields);

		$data['additional_fields'] = '';

		$data['content'] = $this->load->view('info/category_form', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'info/category')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['category_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 2) || (utf8_strlen($value['name']) > 255)) {
				$this->error['name'][$language_id] = $this->language->get('error_name');
			}
		}

		if (isset($this->request->get['info_category_id']) && $this->request->post['parent_id']) {
			$results = $this->model_info_category_modify->getCategoryPath($this->request->post['parent_id']);

			foreach ($results as $result) {
				if ($result['path_id'] == $this->request->get['info_category_id']) {
					$this->error['parent'] = $this->language->get('error_parent');

					break;
				}
			}
		}

		if ($this->request->post['category_seo_url']) {
			$this->load->model('design/seo_url');

			foreach ($this->request->post['category_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$seo_urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword, array('store_id' => $store_id, 'language_id' => $language_id));

						foreach ($seo_urls as $seo_url) {
							if (!isset($this->request->get['info_category_id']) || $seo_url['query'] != 'info_category_id=' . $this->request->get['info_category_id']) {
								$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_keyword');

								break;
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
		if (!$this->user->hasPermission('modify', 'info/category')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	protected function validateRepair() {
		if (!$this->user->hasPermission('modify', 'info/category')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	private function getAllCategories($categories, $parent_id = 0, $parent_name = '') {
		$output = array();

		if (array_key_exists($parent_id, $categories)) {
			if ($parent_name != '') {
				//$parent_name .= $this->language->get('text_separator');
				$parent_name .= ' &gt; ';
			}

			foreach ($categories[$parent_id] as $category) {
				$output[$category['category_id']] = array(
					'category_id' => $category['category_id'],
					'name'        => $parent_name . $category['name']
				);

				$output += $this->getAllCategories($categories, $category['category_id'], $parent_name . $category['name']);
			}
		}

	    uasort($output, array($this, 'sortByName'));

		return $output;
	}

	function sortByName($a, $b) {
		return strcmp($a['name'], $b['name']);
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('info/category_modify');

			$filter_data = array(
				'filter' => array(
					'path_name' => $this->request->get['filter_name']
				),
				'sort'        => 'path_name',
				'order'       => 'ASC',
				'start'       => 0,
				'limit'       => 5
			);

			$results = $this->model_info_category_modify->getCategories($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'category_id' => $result['category_id'],
					'name'        => strip_tags(html_entity_decode($result['path_name'] ? $result['path_name'] : $result['name'], ENT_QUOTES, 'UTF-8'))
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
			$this->load->model('info/category_modify');
			$this->load->model('localisation/language');

			$languages = $this->model_localisation_language->getLanguages();

			$languages = array_column($languages, 'code', 'language_id');

			$filter_data = array(
				'filter' => array(
					'path_name' => $this->request->get['filter_name']
				),
				'language_id' => $this->request->get['language_id'],
				'sort'        => 'path_name',
				'order'       => 'ASC',
				'start'       => 0,
				'limit'       => 5
			);

			$results = $this->model_info_category_modify->getCategories($filter_data);

			$client = new \Url('');

			foreach ($results as $result) {
				$url = array();
				$url['info_category_id'] = $result['category_id'];

				if (isset($languages[$result['language_id']]))
					$url['language'] = $languages[$result['language_id']];

				$json[] = array(
					'category_id'    => $result['category_id'],
					'link'           => str_replace('&amp;', '&', $client->link('info/category', $url)),
					'name'          => strip_tags(html_entity_decode($result['path_name'] ? $result['path_name'] : $result['name'], ENT_QUOTES, 'UTF-8'))
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

	// public function update() {
	// 	$this->load->language('info/category');

	// 	$this->document->setTitle($this->language->get('heading_title'));

	// 	$this->load->model('info/category_modify');
	// 	$this->load->model('info/category');

	// 	$this->model_info_category_modify->updateDateBase();

	// 	$this->session->data['success'] = $this->language->get('text_update_success');

	// 	$this->getList();
	// }
}