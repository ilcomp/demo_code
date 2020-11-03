<?php
namespace Controller\Extension\System;

class Info extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/system/info');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_core_setting->editSetting('info', $this->request->post, $this->request->get['store_id']);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/system', 'user_token=' . $this->session->data['user_token']));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/system', 'user_token=' . $this->session->data['user_token'])
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/system/info', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $this->request->get['store_id'])
		);

		$data['action'] = $this->url->link('extension/system/info', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $this->request->get['store_id']);

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('marketplace/system', 'user_token=' . $this->session->data['user_token']);

		if (isset($this->request->get['store_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$setting_info = $this->model_core_setting->getSetting('info', $this->request->get['store_id']);
		}

		if (isset($this->request->post['info_status'])) {
			$data['info_status'] = $this->request->post['info_status'];
		} elseif ($setting_info) {
			$data['info_status'] = $setting_info['info_status'];
		} else {
			$data['info_status'] = '';
		}

		if (isset($this->request->post['info_home_page'])) {
			$data['info_home_page'] = $this->request->post['info_home_page'];
		} elseif (isset($setting_info['info_home_page'])) {
			$data['info_home_page'] = $setting_info['info_home_page'];
		} else {
			$data['info_home_page'] = '';
		}

		if (isset($this->request->post['info_contact_page'])) {
			$data['info_contact_page'] = $this->request->post['info_contact_page'];
		} elseif (isset($setting_info['info_contact_page'])) {
			$data['info_contact_page'] = $setting_info['info_contact_page'];
		} else {
			$data['info_contact_page'] = '';
		}

		$images_name = array('category', 'list', 'thumb', 'popup', 'additional', 'related');


		$data['info_image'] = array();

		foreach ($images_name as $value) {
			$data['info_image'][$value]['name'] = $this->language->get('entry_image_' . $value);

			if (isset($this->request->post['info_image_' . $value . '_width'])) {
				$data['info_image'][$value]['width'] = $this->request->post['info_image_' . $value . '_width'];
			} elseif (isset($setting_info['info_image_' . $value . '_width'])) {
				$data['info_image'][$value]['width'] = $setting_info['info_image_' . $value . '_width'];
			} else {
				$data['info_image'][$value]['width'] = 300;
			}

			if (isset($this->request->post['info_image_' . $value . '_height'])) {
				$data['info_image'][$value]['height'] = $this->request->post['info_image_' . $value . '_height'];
			} elseif (isset($setting_info['info_image_' . $value . '_height'])) {
				$data['info_image'][$value]['height'] = $setting_info['info_image_' . $value . '_height'];
			} else {
				$data['info_image'][$value]['height'] = 300;
			}
		}

		$this->load->model('info/article');

		$data['articles'] = $this->model_info_article->getArticles(array('filter_category_id' => -1));

		$data['content'] = $this->load->view('extension/system/info', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	public function install() {
		$table = new \Model\DBTable($this->registry);
		$table->create('info');

		$this->load->model('core/event');
		$this->model_core_event->addEvent('info', 'api/controller/startup/event/after', 'event/info/startup');
		$this->model_core_event->addEvent('info', 'admin/controller/startup/event/after', 'event/info/startup');
		$this->model_core_event->addEvent('info', 'client/controller/startup/event/after', 'event/info/startup');

		$this->load->model('design/seo_regex');
		$this->model_design_seo_regex->addSeoRegex(array('name' => 'Info Article', 'regex' => '(info_article_id=\d+)(?:[&]|$)', 'sort_order' => 1));
		$this->model_design_seo_regex->addSeoRegex(array('name' => 'Info Category', 'regex' => '(info_category_id=\d+)(?:[_&]|$)', 'sort_order' => 1));
		$this->model_design_seo_regex->addSeoRegex(array('name' => 'Info Category', 'regex' => '(info_category_id=)', 'sort_order' => 1));
		$this->model_design_seo_regex->addSeoRegex(array('name' => 'Info Article', 'regex' => '(info_article_id=)', 'sort_order' => 2));

		$this->load->model('design/seo_url');
		$this->model_design_seo_url->addSeoUrl(array('store_id' => 0, 'language_id' => 1, 'query' => 'route=info/category', 'keyword' => '', 'push' => ''));
		$this->model_design_seo_url->addSeoUrl(array('store_id' => 0, 'language_id' => 1, 'query' => 'route=info/article', 'keyword' => '', 'push' => ''));
	}

	public function uninstall() {
		$table = new \Model\DBTable($this->registry);
		$table->delete('info');

		$this->load->model('core/event');
		$this->model_core_event->deleteEventByCode('info');

		$this->load->model('design/seo_regex');
		$this->model_design_seo_regex->deleteSeoRegexByName('Info Article');
		$this->model_design_seo_regex->deleteSeoRegexByName('Info Category');

		$this->load->model('design/seo_url');
		$this->model_design_seo_url->deleteSeoUrlByQuery('route=info/');
		$this->model_design_seo_url->deleteSeoUrlByQuery('info_');
	}

	public function update() {
		$table = new \Model\DBTable($this->registry);
		$table->update('info');
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/system/info')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}