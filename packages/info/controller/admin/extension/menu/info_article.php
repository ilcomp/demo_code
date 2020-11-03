<?php
namespace Controller\Extension\Menu;

class InfoArticle extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/menu/info_article');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_core_setting->editSetting('menu_info_article', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('design/extension/menu'));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('design/extension/menu')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/menu/info_article')
		);

		$data['action'] = $this->url->link('extension/menu/info_article');

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('design/extension/menu');

		if (isset($this->request->post['menu_info_article_status'])) {
			$data['menu_info_article_status'] = $this->request->post['menu_info_article_status'];
		} else {
			$data['menu_info_article_status'] = $this->config->get('menu_info_article_status');
		}

		$data['content'] = $this->load->view('extension/menu/info_article', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	public function get_setting($setting = array()) {
		$this->load->language('extension/menu/info_article');

		$this->load->model('localisation/language');

		$setting['languages'] = $this->model_localisation_language->getLanguages();

		$setting['user_token'] = $this->session->data['user_token'];

		return $this->load->view('extension/menu/info_article_setting', $setting);
	}

	public function install() {
	}

	public function uninstall() {
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/menu/info_article')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}