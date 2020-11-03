<?php
namespace Controller\Extension\Module;

class Information extends Controller {
	public function index() {
		$this->load->language('extension/information');

		$this->load->model('information/information');

		if($setting['show_title']){
			$data['heading_title'] = $setting['name'];
			$data['href'] = $this->url->link('information/category', '&information_category_id=' . $setting['main_category_id']);
		}else{
			$data['heading_title'] = false;
			$data['href'] = false;
		}

		$data['informations'] = array();

		foreach ($this->model_information_information->getInformations() as $result) {
			$data['informations'][] = array(
				'title' => $result['title'],
				'href'  => $this->url->link('information/information', 'language=' . $this->config->get('config_language') . '&information_id=' . $result['information_id'])
			);
		}

		$data['contact'] = $this->url->link('information/contact', 'language=' . $this->config->get('config_language'));
		$data['sitemap'] = $this->url->link('information/sitemap', 'language=' . $this->config->get('config_language'));

		return $this->load->view('extension/module/information', $data);
	}
}