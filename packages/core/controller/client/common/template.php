<?php
namespace Controller\Common;

class Template extends \Controller {
	public function index($data = array()) {
		$filename = 'system/client.js';

		if (@filemtime(DIR_TEMPLATE . $filename) > @filemtime(DIR_THEME . $filename))
			copy(DIR_TEMPLATE . $filename, DIR_THEME . $filename);

		if (file_exists(DIR_THEME . $filename))
			$this->document->addScript('theme/' . $filename . '?v=' . filemtime(DIR_THEME . $filename), 0, 'async');

		if ($this->config->get('app_client_icon')) {
			$this->load->model('tool/image');

			$icon = $this->model_tool_image->compress($this->config->get('app_client_icon'));

			if ($icon)
				$this->document->addLink(HTTP_SERVER . ltrim($icon, '/'), 'icon');
		}

		$data['lang'] = $this->language->get('code');
		$data['direction'] = $this->language->get('direction');

		$data['base'] = $this->config->get('config_url');

		$data['name'] = $this->config->get('app_client_name');
		$data['owner'] = $this->config->get('app_client_owner');
		$data['email'] = $this->config->get('app_client_email');
		$data['telephone'] = $this->config->get('app_client_telephone');

		$data['title'] = $this->document->getTitle();
		$data['description'] = $this->document->getDescription();

		$data['metas'] = $this->document->getMetas();
		$data['links'] = $this->document->getLinks();
		$data['styles'] = $this->document->getStyles();
		$data['scripts'] = $this->document->getScripts();

		$data['search'] = isset($this->request->get['search']) ? $this->request->get['search'] : '';

		$data['actions']['form'] = $this->url->link('block/form');
		$data['actions']['search'] = $this->url->link('common/search');
		$data['actions']['search_autocomplete'] = $this->url->link('common/search/autocomplete');

		if (!isset($data['error_warning'])) {
			$data['error_warning'] = '';
		}

		if (!isset($data['success'])) {
			$data['success'] = '';
		}

		if (!isset($data['breadcrumbs'])) {
			$data['breadcrumbs'] = array();
		}

		if (!isset($data['blocks'])) {
			$data['blocks'] = array();
		}

		$this->load->language('template/header');
		$data['header'] = $this->load->view('template/header', $data);

		$this->load->language('template/footer');
		$data['footer'] = $this->load->view('template/footer', $data);

		if (!empty($data['template'])) {
			$data['template'] = $data['template'];
		} else {
			$data['template'] = 'template/default';
		}

		return $this->load->view($data['template'], $data);
	}
}
