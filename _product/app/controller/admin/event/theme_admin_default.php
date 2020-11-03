<?php
namespace Controller\Event;

class ThemeAdminDefault extends \Controller {
	public function index() {
		$this->event->register('controller/startup/event/after', new \Action('event/theme_admin_default/startup'), 0);
	}

	public function startup() {
		$this->event->register('controller/common/template/before', new \Action('event/theme/theme'), 997);
		$this->event->register('controller/common/template/before', new \Action('event/theme/scss'), 998);
		$this->event->register('controller/common/template/before', new \Action('event/theme_admin_default/template'), 999);
		$this->event->register('view/*/before', new \Action('event/theme_admin_default/view'), 0);

		$this->event->register('view/extension/module/html/before', new \Action('event/theme_admin_default/extension_module_html'), 0);
		$this->event->register('view/extension/module/form/before', new \Action('event/theme_admin_default/extension_module_html'), 0);
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
				$this->document->addScript('theme/' . $script . '?v=' . filemtime(DIR_THEME . $script), 100, '');
		}

		$this->load->model('tool/image');

		if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$args[0]['logo'] = $this->model_tool_image->resize($this->config->get('config_logo'), 182, 36);
		} else {
			$args[0]['logo'] = $this->model_tool_image->resize('logo.png', 182, 36);
		}
		
		$args[0]['text_logged'] = sprintf($this->language->get('text_logged'), $this->user->getUserName());

		if (!isset($this->request->get['user_token']) || !isset($this->session->data['user_token']) || ($this->request->get['user_token'] != $this->session->data['user_token'])) {
			$args[0]['logged'] = '';

			$args[0]['home'] = $this->url->link('common/login');
		} else {
			$args[0]['logged'] = true;

			$args[0]['home'] = $this->url->link('common/dashboard');
			$args[0]['logout'] = $this->url->link('common/logout');
			$args[0]['profile'] = $this->url->link('common/profile');

			$args[0]['firstname'] = '';
			$args[0]['lastname'] = '';
			$args[0]['user_group'] = '';
			$args[0]['image'] = $this->model_tool_image->resize('profile.png', 45, 45);
						
			$this->load->model('core/user');
	
			$user_info = $this->model_core_user->getUser($this->user->getId());
	
			if ($user_info) {
				$args[0]['firstname'] = $user_info['firstname'];
				$args[0]['lastname'] = $user_info['lastname'];
				$args[0]['username']  = $user_info['username'];
				$args[0]['user_group'] = $user_info['user_group'];
	
				if (is_file(DIR_IMAGE . $user_info['image'])) {
					$args[0]['image'] = $this->model_tool_image->resize($user_info['image'], 45, 45);
				}
			} 		
			
			// Online Stores
			$args[0]['stores'] = array();

			$args[0]['stores'][] = array(
				'name' => $this->config->get('config_name'),
				'href' => HTTP_APPLICATION_CLIENT
			);

			$this->load->model('core/store');

			$results = $this->model_core_store->getStores();

			foreach ($results as $result) {
				$args[0]['stores'][] = array(
					'name' => $result['name'],
					'href' => $result['url']
				);
			}
		}

		if ($this->user->isLogged() && isset($this->request->get['user_token']) && ($this->request->get['user_token'] == $this->session->data['user_token'])) {
			$args[0]['version'] = VERSION;
		} else {
			$args[0]['version'] = '';
		}
	}

	public function view($route, &$data) {
		if (isset($data['languages'])) {
			$this->load->model('tool/image');

			if (isset($data['languages']) && is_array($data['languages'])) {
				foreach ($data['languages'] as &$language) {
					if (is_array($language))
						$language['image'] = $this->model_tool_image->link('language/' . $language['code'] . '.png');
				}
				unset($language);
			}
		}
	}

	public function extension_module_html($route, &$data) {
		//$this->document->addScript('/assets/babel/babel.js');
		$this->document->addScript('/assets/switcheditor/switcheditor.js', 5);

		$data['editor'] = 'codemirror';

		$this->document->addScript('/assets/switcheditor/dialog-image.js', 10);
		$this->document->addScript('/assets/switcheditor/dialog-file.js', 10);
		$this->document->addScript('/assets/switcheditor/dialog-link.js', 10);

		$data['dialogs'] = array(
			array(
				'code' => 'image',
				'setting' => array(
					'manager' => $this->url->link('common/filemanager', 'user_token=' . $this->request->get['user_token']),
					'link' => $this->url->link('common/filemanager/get_link', 'user_token=' . $this->request->get['user_token'])
				),
				'icon' => 'fas fa-image'
			),
			array(
				'code' => 'file',
				'setting' => array(
					'manager' => $this->url->link('common/file', 'user_token=' . $this->request->get['user_token']),
					'link' => $this->url->link('common/file/get_link', 'user_token=' . $this->request->get['user_token'])
				),
				'icon' => 'fas fa-file'
			),
			array(
				'code' => 'link',
				'setting' => array(
					'manager' => $this->url->link('design/link', 'user_token=' . $this->request->get['user_token'])
				),
				'icon' => 'fas fa-link'
			)
		);

		$this->document->addScript('/assets/switcheditor/editor-codemirror.js', 10);

		$data['editors'] = array(array(
			'code' => 'codemirror',
			'setting' => array(),
			'icon' => 'fas fa-code'
		));
	}
}