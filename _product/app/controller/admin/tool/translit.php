<?php
namespace Controller\Tool;

class Translit extends \Controller {
	public function index() {
		$str = isset($this->request->post['str']) ? $this->request->post['str'] : '';

		if ($str) {
			$language = isset($this->request->post['language']) ? $this->request->post['language'] : '';

			if (!$language)
				$language = $this->config->get('config_language');

			$str = (String)$this->load->controller('extension/language/' . str_replace('-', '_', $language), $str);
		}

		$this->response->setOutput($str);
	}
}