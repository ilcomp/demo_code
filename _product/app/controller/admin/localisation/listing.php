<?php
namespace Controller\Localisation;

class Listing extends \Controller {
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
			$this->model_localisation_listing->addListing($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('localisation/listing', 'user_token=' . $this->session->data['user_token']));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('localisation/listing');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('localisation/listing');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_localisation_listing->editListing($this->request->get['listing_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('localisation/listing', 'user_token=' . $this->session->data['user_token']));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('localisation/listing');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('localisation/listing');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $listing_id) {
				$this->model_localisation_listing->deleteListing($listing_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('localisation/listing', 'user_token=' . $this->session->data['user_token']));
		}

		$this->getList();
	}

	protected function getList() {
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('localisation/listing', 'user_token=' . $this->session->data['user_token'])
		);

		$data['actions']['add'] = $this->url->link('localisation/listing/add', 'user_token=' . $this->session->data['user_token']);
		$data['actions']['delete'] = $this->url->link('localisation/listing/delete', 'user_token=' . $this->session->data['user_token']);

		$data['listings'] = $this->model_localisation_listing->getListings();

		foreach ($data['listings'] as &$listing) {
			$listing['edit'] = $this->url->link('localisation/listing/edit', 'user_token=' . $this->session->data['user_token'] . '&listing_id=' . $listing['listing_id']);
		}
		unset($listing);
		
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

		$data['content'] = $this->load->view('localisation/listing_list', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['listing_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

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

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('localisation/listing', 'user_token=' . $this->session->data['user_token'])
		);

		if (!isset($this->request->get['listing_id'])) {
			$data['action'] = $this->url->link('localisation/listing/add', 'user_token=' . $this->session->data['user_token']);
		} else {
			$data['action'] = $this->url->link('localisation/listing/edit', 'user_token=' . $this->session->data['user_token'] . '&listing_id=' . $this->request->get['listing_id']);
		}

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('localisation/listing', 'user_token=' . $this->session->data['user_token']);

		if (isset($this->request->get['listing_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$listing_info = $this->model_localisation_listing->getListing($this->request->get['listing_id']);
		} else {
			$listing_info = '';
		}

		if (isset($this->request->post['type'])) {
			$data['type'] = $this->request->post['type'];
		} elseif ($listing_info) {
			$data['type'] = $listing_info['type'];
		} else {
			$data['type'] = '';
		}

		if (isset($this->request->post['readonly'])) {
			$data['readonly'] = $this->request->post['readonly'];
		} elseif ($listing_info) {
			$data['readonly'] = $listing_info['readonly'];
		} else {
			$data['readonly'] = 0;
		}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif ($listing_info) {
			$data['name'] = $this->model_localisation_listing->getListingDescriptions($listing_info['listing_id']);
		} else {
			$data['name'] = array();
		}

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$data['types'] = array(
			array(
				'value' => 'color',
				'name' => $this->language->get('text_color')
			)
		);

		$data['content'] = $this->load->view('localisation/listing_form', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'localisation/listing')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['name'] as $language_id => $name) {
			if ((utf8_strlen($name) < 3) || (utf8_strlen($name) > 255)) {
				$this->error['name'][$language_id] = $this->language->get('error_name');
			}
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'localisation/listing')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
