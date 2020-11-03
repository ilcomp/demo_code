<?php
namespace Controller\Extension\Module;

class Form extends \Controller {
	public function index($setting) {
		if (empty($setting['html'][$this->config->get('config_language_id')]) || empty($setting['form_id']))
			return;

		$data = $setting;

		$data['form'] = $this->load->controller('block/form', $setting);

		if (!$data['form'])
			return;

		if (isset($setting['html'][$this->config->get('config_language_id')]))
			$data['html'] = str_replace('{{ form }}', $data['form'], html_entity_decode($setting['html'][$this->config->get('config_language_id')], ENT_QUOTES, 'UTF-8'));
		else
			$data['html'] = '';

		return $this->load->view('extension/module/form', $data);
	}
}