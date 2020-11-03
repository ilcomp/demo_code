<?php
namespace Controller\Localisation;

class ListingItem extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('localisation/listing');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('localisation/listing');

		$this->getList();
	}

	public function add() {
		$this->load->language('localisation/listing');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('localisation/listing');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_localisation_listing->addListingItem($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['listing_id'])) {
				$url .= '&listing_id=' . $this->request->get['listing_id'];
			}

			$this->response->redirect($this->url->link('localisation/listing_item', 'user_token=' . $this->session->data['user_token'] . $url));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('localisation/listing');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('localisation/listing');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_localisation_listing->editListingItem($this->request->get['listing_item_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['listing_id'])) {
				$url .= '&listing_id=' . $this->request->get['listing_id'];
			}

			$this->response->redirect($this->url->link('localisation/listing_item', 'user_token=' . $this->session->data['user_token'] . $url));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('localisation/listing');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('localisation/listing');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $listing_item_id) {
				$this->model_localisation_listing->deleteListingItem($listing_item_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['listing_id'])) {
				$url .= '&listing_id=' . $this->request->get['listing_id'];
			}

			$this->response->redirect($this->url->link('localisation/listing_item', 'user_token=' . $this->session->data['user_token'] . $url));
		}

		$this->getList();
	}

	protected function getList() {
		if (isset($this->request->get['listing_id'])) {
			$listing_id = $this->request->get['listing_id'];
		} else {
			$listing_id = '';
		}

		$listing_info = $this->model_localisation_listing->getListing($listing_id);

		if (!$listing_info) {
			$this->response->redirect($this->url->link('localisation/listing', 'user_token=' . $this->session->data['user_token']));
		}

		$url = '';

		if (isset($this->request->get['listing_id'])) {
			$url .= '&listing_id=' . $this->request->get['listing_id'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		);

		$data['breadcrumbs'][] = array(
			'text' => $listing_info['name'],
			'href' => $this->url->link('localisation/listing_item', 'user_token=' . $this->session->data['user_token'] . $url)
		);

		$data['actions']['add'] = $this->url->link('localisation/listing_item/add', 'user_token=' . $this->session->data['user_token'] . $url);
		$data['actions']['delete'] = $this->url->link('localisation/listing_item/delete', 'user_token=' . $this->session->data['user_token'] . $url);

		$data['listing_items'] = $this->model_localisation_listing->getListingItems(array('filter_listing_id' => $listing_id));

		foreach ($data['listing_items'] as &$result) {
			$result['edit'] = $this->url->link('localisation/listing_item/edit', 'user_token=' . $this->session->data['user_token'] . '&listing_item_id=' . $result['listing_item_id'] . $url);
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
		
		$data['content'] = $this->load->view('localisation/listing_item_list', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['listing_item_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

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

		$url = '';

		if (isset($this->request->get['listing_id'])) {
			$url .= '&listing_id=' . $this->request->get['listing_id'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('localisation/listing_item', 'user_token=' . $this->session->data['user_token'] . $url)
		);

		if (!isset($this->request->get['listing_item_id'])) {
			$data['action'] = $this->url->link('localisation/listing_item/add', 'user_token=' . $this->session->data['user_token'] . $url);
		} else {
			$data['action'] = $this->url->link('localisation/listing_item/edit', 'user_token=' . $this->session->data['user_token'] . '&listing_item_id=' . $this->request->get['listing_item_id'] . $url);
		}

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('localisation/listing_item', 'user_token=' . $this->session->data['user_token'] . $url);

		if (isset($this->request->get['listing_item_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$listing_item_info = $this->model_localisation_listing->getListingItem($this->request->get['listing_item_id']);
		} else {
			$listing_item_info = '';
		}

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['description'])) {
			$data['description'] = $this->request->post['description'];
		} elseif ($listing_item_info) {
			$data['description'] = $this->model_localisation_listing->getListingItemDescriptions($listing_item_info['listing_item_id']);
		} else {
			$data['description'] = array();
		}

		if (isset($this->request->post['value'])) {
			$data['value'] = $this->request->post['value'];
		} elseif ($listing_item_info) {
			$data['value'] = $listing_item_info['value'];
		} else {
			$data['value'] = '';
		}

		if (isset($this->request->post['image'])) {
			$data['image'] = $this->request->post['image'];
		} elseif (!empty($listing_item_info)) {
			$data['image'] = $listing_item_info['image'];
		} else {
			$data['image'] = '';
		}

		$this->load->model('tool/image');

		$thumb_width = $this->config->get('admin_image_thumb_width') ? $this->config->get('admin_image_thumb_width') : 100;
		$thumb_height = $this->config->get('admin_image_thumb_height') ? $this->config->get('admin_image_thumb_height') : 100;

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', $thumb_width, $thumb_height);

		if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], $thumb_width, $thumb_height);
		} elseif (!empty($listing_item_info) && is_file(DIR_IMAGE . $listing_item_info['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($listing_item_info['image'], $thumb_width, $thumb_height);
		} else {
			$data['thumb'] = $data['placeholder'];
		}

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif ($listing_item_info) {
			$data['sort_order'] = $listing_item_info['sort_order'];
		} else {
			$data['sort_order'] = '';
		}

		$data['content'] = $this->load->view('localisation/listing_item_form', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'localisation/listing_item')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!isset($this->request->get['listing_id'])) {
			$this->error['warning'] = $this->language->get('error_listing');
		} else {
			$listing = $this->model_localisation_listing->getListing($this->request->get['listing_id']);

			if (!$listing) {
				$this->error['warning'] = $this->language->get('error_listing');
			} else {
				$this->request->post['listing_id'] = $this->request->get['listing_id'];
			}
		}

		foreach ($this->request->post['description'] as $language_id => $description) {
			if ((utf8_strlen($description['name']) < 1) || (utf8_strlen($description['name']) > 255)) {
				$this->error['name'][$language_id] = $this->language->get('error_name');
			}
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'localisation/listing_item')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('localisation/listing');

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'filter_listing_id' => isset($this->request->get['filter_listing_id']) ? (int)$this->request->get['filter_listing_id'] : 0,
				'start'       => 0,
				'limit'       => 5
			);

			$results = $this->model_localisation_listing->getListingItems($filter_data);

			foreach ($results as $result) {
				$result['name'] = strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'));
				$json[] = $result;
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
