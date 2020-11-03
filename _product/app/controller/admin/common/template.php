<?php
namespace Controller\Common;

class Template extends \Controller {
	public function index($data) {
		if ($this->user->isLogged() && isset($this->request->get['user_token']) && ($this->request->get['user_token'] == $this->session->data['user_token'])) {
			$filename = 'system/admin.js';

			if (@filemtime(DIR_TEMPLATE . $filename) > @filemtime(DIR_THEME . $filename)) {
				if(!is_dir(DIR_THEME . 'system'))
					mkdir(DIR_THEME . 'system', 0755, true);

				copy(DIR_TEMPLATE . $filename, DIR_THEME . $filename);
			}

			if (file_exists(DIR_THEME . $filename))
				$this->document->addScript('theme/' . $filename . '?v=' . filemtime(DIR_THEME . $filename), 0, 'async');
		}

		if ($this->config->get('app_client_icon')) {
			$this->load->model('tool/image');

			$icon = $this->model_tool_image->compress($this->config->get('app_client_icon'));

			if ($icon)
				$this->document->addLink($icon, 'icon');
		}

		$this->document->addMeta('robots', 'noindex, nofollow');

		$data['lang'] = $this->language->get('code');
		$data['direction'] = $this->language->get('direction');

		$data['base'] = $this->config->get('config_url');
		
		$data['title'] = $this->document->getTitle();
		$data['description'] = $this->document->getDescription();

		$data['links'] = $this->document->getLinks();
		$data['styles'] = $this->document->getStyles();
		$data['scripts'] = $this->document->getScripts();

		//$data['menu'] = $this->load->controller('block/menu');

		$data['menu']['general']['view'] = $this->load->controller('block/column_left');

		if (!isset($data['error_warning'])) {
			$data['error_warning'] = '';
		}

		if (!isset($data['success'])) {
			$data['success'] = '';
		}

		if (!isset($data['breadcrumbs'])) {
			$data['breadcrumbs'] = array();
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
