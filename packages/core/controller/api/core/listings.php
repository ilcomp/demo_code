<?php
namespace Controller\Core;

class Listings extends \Controller {
	public function index() {
		if ($this->request->server['REQUEST_METHOD'] == 'GET') {
			$this->get();
		} elseif ($this->request->server['REQUEST_METHOD'] == 'DELETE') {
			$this->delete();
		} else {
			$this->response->setOutput($this->tree());
		}
	}

	public function get() {
		$this->load->model('core/localisation/listing');

		$data['error'] = $this->model_core_localisation_listing->validateDeleteListing($this->request->get);

		if (empty($data['error']))
			$data['result'] = $this->model_core_localisation_listing->deleteListings($this->request->get);

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
					'link' => $this->url->link('core/listing/get_listings'),
					'methods' => array('GET'),
					'args' => array(
						array(
							'context' => 'context',
							'required' => false,
							'type' => 'array',
							'enum' => array(
								'view',
								'full'
							),
							'default' => array('id')
						),
						array(
							'code' => 'filter_name',
							'required' => false,
							'type' => 'string'
						),
						array(
							'code' => 'page',
							'required' => false,
							'type' => 'int'
						),
						array(
							'code' => 'limit',
							'required' => false,
							'type' => 'int'
						),
						array(
							'code' => 'sort',
							'required' => false,
							'type' => 'string',
							'enum' => array(
								'listing_id',
								'name'
							),
							'default' => 'listing_id'
						),
						array(
							'code' => 'order',
							'required' => false,
							'type' => 'string',
							'enum' => array(
								'ASC',
								'DESC'
							),
							'default' => 'ASC'
						)
					)
				),
				'delete' => array(
					'link' => $this->url->link('core/listing/delete_listings'),
					'methods' => array('POST'),
					'args' => array(
						array(
							'code' => 'listing_ids',
							'required' => false,
							'type' => 'array(int)'
						)
					)
				)
			)
		);
	}
}