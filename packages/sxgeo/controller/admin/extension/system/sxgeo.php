<?php
namespace Controller\Extension\System;

class SxGeo extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/system/sxgeo');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/setting');
		$this->load->model('core/cron');

		$cron_update_ip = $this->model_core_cron->getCronByCode('sxgeo_update_ip');
		$cron_update_db = $this->model_core_cron->getCronByCode('sxgeo_update_db');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_core_setting->editSetting('sxgeo', $this->request->post);

			if (isset($this->request->post['cron']['update_ip']) && $this->request->post['cron']['update_ip']) {
				$this->model_core_cron->editStatus($cron_update_ip['cron_id'], 1);
				$this->model_core_cron->editCycle($cron_update_ip['cron_id'], $this->request->post['cron']['update_ip']);
			} else {
				$this->model_core_cron->editStatus($cron_update_ip['cron_id'], 0);
			}

			if (isset($this->request->post['cron']['update_db']) && $this->request->post['cron']['update_db']) {
				$this->model_core_cron->editStatus($cron_update_db['cron_id'], 1);
				$this->model_core_cron->editCycle($cron_update_db['cron_id'], $this->request->post['cron']['update_db']);
			} else {
				$this->model_core_cron->editStatus($cron_update_db['cron_id'], 0);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/system'));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['limit'])) {
			$data['error_limit'] = $this->error['limit'];
		} else {
			$data['error_limit'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/system')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/system/sxgeo', 'store_id=' . $this->request->get['store_id'])
		);

		$data['action'] = $this->url->link('extension/system/sxgeo', 'store_id=' . $this->request->get['store_id']);

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('marketplace/extension', 'type=system');
		$data['actions']['autocomplete'] = $this->url->link('localisation/sxgeo/autocomplete');

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->get['store_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$setting_info = $this->model_core_setting->getSetting('sxgeo', $this->request->get['store_id']);
		}

		$data['update_ip'] = $this->url->link('marketplace/cron/run', 'cron_id=' . $cron_update_ip['cron_id']);

		$data['update_db'] = $this->url->link('marketplace/cron/run', 'cron_id=' . $cron_update_db['cron_id']);

		if (isset($this->request->post['sxgeo_status'])) {
			$data['sxgeo_status'] = $this->request->post['sxgeo_status'];
		} else {
			$data['sxgeo_status'] = $this->config->get('sxgeo_status');
		}

		if (isset($this->request->post['sxgeo_default'])) {
			$data['sxgeo_default'] = $this->request->post['sxgeo_default'];
		} else {
			$data['sxgeo_default'] = $this->config->get('sxgeo_default');
		}

		$this->load->model('extension/system/sxgeo');

		$sxgeo = $this->model_extension_system_sxgeo->getSxGeo(2, $data['sxgeo_default']);

		$data['sxgeo_default_name'] = $sxgeo ? $sxgeo['name'] : '';

		if (isset($this->request->post['cron']['update_ip'])) {
			$data['cron']['update_ip'] = $this->request->post['cron']['update_ip'];
		} else {
			$data['cron']['update_ip'] = $cron_update_ip['status'] ? $cron_update_ip['cycle'] : '';
		}

		if (isset($this->request->post['cron']['update_db'])) {
			$data['cron']['update_db'] = $this->request->post['cron']['update_db'];
		} else {
			$data['cron']['update_db'] = $cron_update_db['status'] ? $cron_update_db['cycle'] : '';
		}

		$data['cycles'] = array(
			'' => $this->language->get('text_disabled'),
			'+1 minute' => $this->language->get('text_minute'),
			'+1 hour' => $this->language->get('text_hour'),
			'+1 day' => $this->language->get('text_day'),
			'+1 week' => $this->language->get('text_week'),
			'+1 month' => $this->language->get('text_month'),
		);

		$data['user_token'] = $this->session->data['user_token'];

		$data['content'] = $this->load->view('extension/system/sxgeo', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	public function install() {
		$table = new \Model\DBTable($this->registry);
		$table->create('sxgeo');

		$this->load->model('core/event');
		$this->model_core_event->addEvent('sxgeo', 'api/controller/startup/event/after', 'event/sxgeo/startup');
		$this->model_core_event->addEvent('sxgeo', 'admin/controller/startup/event/after', 'event/sxgeo/startup');
		$this->model_core_event->addEvent('sxgeo', 'client/controller/startup/event/after', 'event/sxgeo/startup');

		$this->load->model('core/cron');
		$this->model_core_cron->addCron('sxgeo_update_ip', '+1 day', 'cron/sxgeo/update_ip', 0);
		$this->model_core_cron->addCron('sxgeo_update_db', '+1 day', 'cron/sxgeo/update_db', 0);
	}

	public function uninstall() {
		$table = new \Model\DBTable($this->registry);
		$table->delete('sxgeo');

		$this->load->model('core/event');
		$this->model_core_event->deleteEventByCode('sxgeo');

		$this->load->model('core/cron');
		$this->model_core_cron->deleteCronByCode('sxgeo_update_ip');
		$this->model_core_cron->deleteCronByCode('sxgeo_update_db');
	}

	public function update() {
		$table = new \Model\DBTable($this->registry);
		$table->update('sxgeo');
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/system/sxgeo')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}
}
