<?php
namespace Controller\Event;

class ThemeClientDefault extends \Controller {
	public function index() {
		$this->event->register('controller/startup/event/after', new \Action('event/theme_client_default/startup'), 0);
	}

	public function startup() {
		$this->event->register('controller/common/template/before', new \Action('event/theme/theme'), 997);
		$this->event->register('controller/common/template/before', new \Action('event/theme/scss'), 998);
		$this->event->register('controller/common/template/before', new \Action('event/theme_client_default/template'), 999);

		$this->load->language('extension/theme/client_default');
	}

	public function template($route, &$args) {
		$styles = [
			$this->config->get('config_theme') . '/css/core.css',
			$this->config->get('config_theme') . '/css/style.css'
		];
		foreach ($styles as $style) {
			$this->document->addStyle('theme/' . $style . (file_exists(DIR_THEME . $style) ? ('?v=' . filemtime(DIR_THEME . $style)) : ''));
		}

		$scripts = [
			$this->config->get('config_theme') . '/js/script.js',
			$this->config->get('config_theme') . '/js/theme.js',
			$this->config->get('config_theme') . '/js/common.js'
		];
		foreach ($scripts as $script) {
			if (file_exists(DIR_THEME . $script))
				$this->document->addScript('theme/' . $script . '?v=' . filemtime(DIR_THEME . $script), 100);
		}

		$this->load->model('tool/image');

		$args[0]['logo'] = $this->model_tool_image->link($this->config->get('config_logo'), 200, 200);

		$args[0]['form'] = $this->load->controller('form/callback');
		$args[0]['link_contact'] = $this->url->link('common/contact');

		$args[0]['name'] = $this->config->get('config_name');
		$args[0]['address'] = $this->config->get('config_address');
		$args[0]['open'] = $this->config->get('config_open');
		$args[0]['telephone'] = $this->config->get('config_telephone');
		$args[0]['email'] = $this->config->get('config_email');
		$args[0]['comment'] = html_entity_decode($this->config->get('config_comment'), ENT_QUOTES, 'UTF-8');

		
		// $data['menu'] = $this->load->controller('common/menu');
		// $data['language'] = $this->load->controller('common/language');
		// $data['search_form'] = $this->load->controller('common/search/form');

		//$data['panel_user'] = $this->load->controller('common/panel_user', $setting);
	}
}