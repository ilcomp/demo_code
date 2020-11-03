<?php
namespace Controller\Common;

class Contact extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('common/contact');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('common/contact')
		);

		$data['store'] = $this->config->get('config_name');
		$data['address'] = nl2br($this->config->get('config_address'));
		$data['telephone'] = $this->config->get('config_telephone');
		$data['email'] = $this->config->get('config_email');

		$this->load->model('localisation/location');
		$this->load->model('tool/image');

		$data['locations'] = $this->model_localisation_location->getLocations();

		foreach($data['locations'] as &$location) {
			if ($location['image']) {
				$location['thumb'] = $this->model_tool_image->resize($location['image'], $this->config->get('config_location_width'), $this->config->get('config_location_height'));
			} else {
				$location['thumb'] = '';
			}
		}
		unset($location);

		$data['content'] = $this->load->view('common/contact', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}
}
