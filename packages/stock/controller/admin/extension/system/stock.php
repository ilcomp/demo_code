<?php
namespace Controller\Extension\System;

class Stock extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('core/setting');
		$this->load->language('extension/system/stock');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_core_setting->editSetting('stock', $this->request->post, $this->request->get['store_id']);

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
			'href' => $this->url->link('extension/system/stock', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $this->request->get['store_id'])
		);

		$data['action'] = $this->url->link('extension/system/stock', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $this->request->get['store_id']);

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('marketplace/system', 'user_token=' . $this->session->data['user_token']);

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->get['store_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$setting_info = $this->model_core_setting->getSetting('stock', $this->request->get['store_id']);
		}

		if (isset($this->request->post['stock_status'])) {
			$data['stock_status'] = $this->request->post['stock_status'];
		} elseif ($setting_info) {
			$data['stock_status'] = $setting_info['stock_status'];
		} else {
			$data['stock_status'] = 0;
		}

		$this->load->model('localisation/listing');

		if (isset($this->request->post['stock_status_listing'])) {
			$data['stock_status_listing'] = $this->request->post['stock_status_listing'];
		} elseif (isset($setting_info['stock_status_listing'])) {
			$data['stock_status_listing'] = $setting_info['stock_status_listing'];
		} else {
			$data['stock_status_listing'] = '';
		}

		$data['listings'] = $this->model_localisation_listing->getListings();

		$this->load->model('localisation/weight_class');

		if (isset($this->request->post['stock_weight_class'])) {
			$data['stock_weight_class'] = $this->request->post['stock_weight_class'];
		} elseif (isset($setting_info['stock_weight_class'])) {
			$data['stock_weight_class'] = $setting_info['stock_weight_class'];
		} else {
			$data['stock_weight_class'] = '';
		}

		$data['weight_classes'] = $this->model_localisation_weight_class->getWeightClasses();

		$this->load->model('localisation/length_class');

		if (isset($this->request->post['stock_length_class'])) {
			$data['stock_length_class'] = $this->request->post['stock_length_class'];
		} elseif (isset($setting_info['stock_length_class'])) {
			$data['stock_length_class'] = $setting_info['stock_length_class'];
		} else {
			$data['stock_length_class'] = '';
		}

		$data['length_classes'] = $this->model_localisation_length_class->getLengthClasses();

		$images_name = array('icon');

		$data['stock_image'] = array();

		foreach ($images_name as $value) {
			$data['stock_image'][$value]['name'] = $this->language->get('entry_image_' . $value);

			if (isset($this->request->post['stock_image_' . $value . '_width'])) {
				$data['stock_image'][$value]['width'] = $this->request->post['stock_image_' . $value . '_width'];
			} elseif (isset($setting_info['stock_image_' . $value . '_width'])) {
				$data['stock_image'][$value]['width'] = $setting_info['stock_image_' . $value . '_width'];
			} else {
				$data['stock_image'][$value]['width'] = 300;
			}

			if (isset($this->request->post['stock_image_' . $value . '_height'])) {
				$data['stock_image'][$value]['height'] = $this->request->post['stock_image_' . $value . '_height'];
			} elseif (isset($setting_info['stock_image_' . $value . '_height'])) {
				$data['stock_image'][$value]['height'] = $setting_info['stock_image_' . $value . '_height'];
			} else {
				$data['stock_image'][$value]['height'] = 300;
			}
		}

		$data['content'] = $this->load->view('extension/system/stock', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	public function install() {
		$table = new \Model\DBTable($this->registry);
		$table->create('stock');

		$this->load->model('core/event');
		$this->model_core_event->addEvent('stock', 'api/controller/startup/event/after', 'event/stock/startup');
		$this->model_core_event->addEvent('stock', 'admin/controller/startup/event/after', 'event/stock/startup');
		$this->model_core_event->addEvent('stock', 'client/controller/startup/event/after', 'event/stock/startup');
	}

	public function uninstall() {
		$table = new \Model\DBTable($this->registry);
		$table->delete('stock');

		$this->load->model('core/event');
		$this->model_core_event->deleteEventByCode('stock');
	}

	public function update() {
		$table = new \Model\DBTable($this->registry);
		$table->update('stock');
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/system/stock')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}
}