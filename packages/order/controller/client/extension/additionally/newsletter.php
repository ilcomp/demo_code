<?php
namespace Controller\Extension\Additionally;

class Newsletter extends \Controller {
	public function index($data = array()) {
		$data['first_start'] = !isset($this->session->data['additionally']['newsletter']);

		$data['additionally'] = !empty($this->session->data['additionally']['newsletter']) ? $this->session->data['additionally']['newsletter'] : array();

		$data['additionally_newsletter_methods'] = (array)$this->config->get('additionally_newsletter_methods');

		foreach ($data['additionally_newsletter_methods'] as &$value) {
			$value['title'] = $value['title'][$this->config->get('config_language_id')];
		}
		unset($value);

		return $this->load->view('extension/additionally/newsletter', $data);
	}
}
