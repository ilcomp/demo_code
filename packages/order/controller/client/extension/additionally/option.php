<?php
namespace Controller\Extension\Additionally;

class Option extends \Controller {
	public function index($data = array()) {
		$data['first_start'] = !isset($this->session->data['additionally']['option']);

		$data['additionally'] = !empty($this->session->data['additionally']['option']) ? $this->session->data['additionally']['option'] : array();
			
		$data['additionally_option_methods'] = (array)$this->config->get('additionally_option_methods');

		foreach ($data['additionally_option_methods'] as &$value) {
			$value['title'] = $value['title'][$this->config->get('config_language_id')];
			$value['description'] = $value['description'][$this->config->get('config_language_id')];
		}
		unset($value);

		return $this->load->view('extension/additionally/option', $data);
	}
}
