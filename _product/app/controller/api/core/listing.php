<?php
namespace Controller\Core;

class Listing extends \Controller {
	public function index() {
		if ($this->request->server['REQUEST_METHOD'] == 'GET') {
			$this->get();
		} elseif ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$this->add();
		} elseif ($this->request->server['REQUEST_METHOD'] == 'PUT') {
			$this->edit();
		} elseif ($this->request->server['REQUEST_METHOD'] == 'DELETE') {
			$this->delete();
		} else {
			$this->response->setOutput($this->tree());
		}
	}

	public function get() {
		if ($this->request->server['REQUEST_METHOD'] != 'GET') {
			new Action('error/not_found');
		}

		$this->load->model('core/localisation/listing');

		$data['error'] = $this->model_core_localisation_listing->validateGetListing($this->request->get);

		if (empty($data['error']))
			$data['result'] = $this->model_core_localisation_listing->getListing($this->request->get);

		$this->response->setOutput($data);
	}

	public function add() {
		if ($this->request->server['REQUEST_METHOD'] != 'POST') {
			new Action('error/not_found');
		}

		$this->load->model('core/localisation/listing');

		$data['error'] = $this->model_core_localisation_listing->validateAddListing($this->request->post);

		if (empty($data['error']))
			$data['result'] = $this->model_core_localisation_listing->addListing($this->request->post);

		$this->response->setOutput($data);
	}

	public function edit() {
		if ($this->request->server['REQUEST_METHOD'] != 'POST') {
			new Action('error/not_found');
		}

		$this->load->model('core/localisation/listing');

		$data['error'] = $this->model_core_localisation_list->validateEditListing($this->request->post);

		if (empty($data['error']))
			$data['result'] = $this->model_core_localisation_list->editListing($this->request->post);

		$this->response->setOutput($data);
	}

	public function delete() {
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$request = $this->request->post;
		} else {
			$request = $this->request->get;
		}

		$this->load->model('core/localisation/listing');

		$data['error'] = $this->model_core_localisation_listing->validateDeleteListing($request);

		if (empty($data['error']))
			$data['result'] = $this->model_core_localisation_listing->deleteListing($request);

		$this->response->setOutput($data);
	}

	public function tree($data = array()) {
		return array(
			'link' => $this->url->link('core/listing'),
			'endpoints' => array(
				'get' => array(
					'link' => $this->url->link('core/listing/get'),
					'methods' => array('GET'),
					'args' => array(
						array(
							'code' => 'listing_id',
							'required' => false,
							'type' => 'int'
						),
						array(
							'context' => 'context',
							'required' => false,
							'type' => 'array',
							'enum' => array(
								'id',
								'view',
								'edit'
							),
							'default' => 'view'
						)
					)
				),
				'add' => array(
					'link' => $this->url->link('core/listing/add'),
					'methods' => array('POST'),
					'args' => array(
						array(
							'code' => 'name',
							'required' => true,
							'type' => 'string'
						)
					)
				),
				'edit' => array(
					'link' => $this->url->link('core/listing/edit'),
					'methods' => array('POST'),
					'args' => array(
						array(
							'code' => 'listing_id',
							'required' => false,
							'type' => 'int'
						)
					)
				),
				'delete' => array(
					'link' => $this->url->link('core/listing/delete'),
					'methods' => array('GET', 'POST'),
					'args' => array(
						array(
							'code' => 'listing_id',
							'required' => false,
							'type' => 'int'
						)
					)
				)
			)
		);
	}
}