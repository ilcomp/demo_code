<?php
namespace Controller\Extension\Total;

class Coupon extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/total/coupon');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_core_setting->editSetting('total_coupon', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('order/extension/total'));
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
			'href' => $this->url->link('order/extension/total')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/total/coupon')
		);

		$data['action'] = $this->url->link('extension/total/coupon');

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('order/extension/total');

		if (isset($this->request->post['total_coupon_status'])) {
			$data['total_coupon_status'] = $this->request->post['total_coupon_status'];
		} else {
			$data['total_coupon_status'] = $this->config->get('total_coupon_status');
		}

		if (isset($this->request->post['total_coupon_sort_order'])) {
			$data['total_coupon_sort_order'] = $this->request->post['total_coupon_sort_order'];
		} else {
			$data['total_coupon_sort_order'] = $this->config->get('total_coupon_sort_order');
		}

		if (isset($this->request->post['total_coupon_status_fraud_id'])) {
			$data['total_coupon_status_fraud_id'] = $this->request->post['total_coupon_status_fraud_id'];
		} else {
			$data['total_coupon_status_fraud_id'] = $this->config->get('total_coupon_status_fraud_id');
		}

		$images_name = array('thumb', 'icon');

		$data['total_coupon_image'] = array();

		foreach ($images_name as $value) {
			$data['total_coupon_image'][$value]['name'] = $this->language->get('entry_image_' . $value);

			if (isset($this->request->post['total_coupon_image_' . $value . '_width'])) {
				$data['total_coupon_image'][$value]['width'] = $this->request->post['total_coupon_image_' . $value . '_width'];
			} elseif (isset($setting_info['total_coupon_image_' . $value . '_width'])) {
				$data['total_coupon_image'][$value]['width'] = $setting_info['total_coupon_image_' . $value . '_width'];
			} else {
				$data['total_coupon_image'][$value]['width'] = 300;
			}

			if (isset($this->request->post['total_coupon_image_' . $value . '_height'])) {
				$data['total_coupon_image'][$value]['height'] = $this->request->post['total_coupon_image_' . $value . '_height'];
			} elseif (isset($setting_info['total_coupon_image_' . $value . '_height'])) {
				$data['total_coupon_image'][$value]['height'] = $setting_info['total_coupon_image_' . $value . '_height'];
			} else {
				$data['total_coupon_image'][$value]['height'] = 300;
			}
		}

		$this->load->model('localisation/listing');

		$data['listing_items'] = $this->model_localisation_listing->getListingItems(array('filter_listing_id' => $this->config->get('order_status_listing')));

		$data['content'] = $this->load->view('extension/total/coupon', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	public function install() {
		$table = new \Model\DBTable($this->registry);
		$table->create('coupon');

		$this->load->model('core/event');
		$this->model_core_event->addEvent('coupon', 'api/controller/startup/event/after', 'event/coupon/startup');
		$this->model_core_event->addEvent('coupon', 'admin/controller/startup/event/after', 'event/coupon/startup');
		$this->model_core_event->addEvent('coupon', 'client/controller/startup/event/after', 'event/coupon/startup');
	}

	public function uninstall() {
		$table = new \Model\DBTable($this->registry);
		$table->delete('coupon');

		$this->load->model('core/event');
		$this->model_core_event->deleteEventByCode('coupon');
	}

	public function update() {
		$table = new \Model\DBTable($this->registry);
		$table->update('coupon');
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/total/coupon')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}