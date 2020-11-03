<?php
namespace Controller\Extension\System;

class Order extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('setting/setting');
		$this->load->language('extension/system/order');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_core_setting->editSetting('order', $this->request->post, $this->request->get['store_id']);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/system'));
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
			'href' => $this->url->link('marketplace/system')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/system/order', 'store_id=' . $this->request->get['store_id'])
		);

		$data['action'] = $this->url->link('extension/system/order', 'store_id=' . $this->request->get['store_id']);

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('marketplace/system');

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->get['store_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$setting_info = $this->model_core_setting->getSetting('order', $this->request->get['store_id']);
		}

		if (isset($this->request->post['order_status'])) {
			$data['order_status'] = $this->request->post['order_status'];
		} elseif ($setting_info) {
			$data['order_status'] = $setting_info['order_status'];
		} else {
			$data['order_status'] = 0;
		}

		if (isset($this->request->post['order_status_listing'])) {
			$data['order_status_listing'] = $this->request->post['order_status_listing'];
		} elseif (isset($setting_info['order_status_listing'])) {
			$data['order_status_listing'] = $setting_info['order_status_listing'];
		} else {
			$data['order_status_listing'] = '';
		}

		if (isset($this->request->post['order_status_default_id'])) {
			$data['order_status_default_id'] = $this->request->post['order_status_default_id'];
		} elseif (isset($setting_info['order_status_default_id'])) {
			$data['order_status_default_id'] = $setting_info['order_status_default_id'];
		} else {
			$data['order_status_default_id'] = '';
		}

		if (isset($this->request->post['order_status_draft_id'])) {
			$data['order_status_draft_id'] = $this->request->post['order_status_draft_id'];
		} elseif (isset($setting_info['order_status_draft_id'])) {
			$data['order_status_draft_id'] = $setting_info['order_status_draft_id'];
		} else {
			$data['order_status_draft_id'] = '';
		}

		if (isset($this->request->post['order_status_edited_id'])) {
			$data['order_status_edited_id'] = $this->request->post['order_status_edited_id'];
		} elseif (isset($setting_info['order_status_edited_id'])) {
			$data['order_status_edited_id'] = $setting_info['order_status_edited_id'];
		} else {
			$data['order_status_edited_id'] = '';
		}

		if (isset($this->request->post['order_status_canceled_id'])) {
			$data['order_status_canceled_id'] = $this->request->post['order_status_canceled_id'];
		} elseif (isset($setting_info['order_status_canceled_id'])) {
			$data['order_status_canceled_id'] = $setting_info['order_status_canceled_id'];
		} else {
			$data['order_status_canceled_id'] = '';
		}

		if (isset($this->request->post['order_status_completed_id'])) {
			$data['order_status_completed_id'] = $this->request->post['order_status_completed_id'];
		} elseif (isset($setting_info['order_status_completed_id'])) {
			$data['order_status_completed_id'] = $setting_info['order_status_completed_id'];
		} else {
			$data['order_status_completed_id'] = '';
		}

		if (isset($this->request->post['order_status_processing'])) {
			$data['order_status_processing'] = $this->request->post['order_status_processing'];
		} elseif (isset($setting_info['order_status_processing'])) {
			$data['order_status_processing'] = $setting_info['order_status_processing'];
		} else {
			$data['order_status_processing'] = '';
		}

		if (isset($this->request->post['order_status_complete'])) {
			$data['order_status_complete'] = $this->request->post['order_status_complete'];
		} elseif (isset($setting_info['order_status_complete'])) {
			$data['order_status_complete'] = $setting_info['order_status_complete'];
		} else {
			$data['order_status_complete'] = '';
		}

		if (isset($this->request->post['order_min_amount'])) {
			$data['order_min_amount'] = $this->request->post['order_min_amount'];
		} elseif (isset($setting_info['order_min_amount'])) {
			$data['order_min_amount'] = $setting_info['order_min_amount'];
		} else {
			$data['order_min_amount'] = 0;
		}

		if (isset($this->request->post['order_shipping_country'])) {
			$data['order_shipping_country'] = $this->request->post['order_shipping_country'];
		} elseif (isset($setting_info['order_shipping_country'])) {
			$data['order_shipping_country'] = $setting_info['order_shipping_country'];
		} else {
			$data['order_shipping_country'] = 0;
		}

		$images_name = array('cart_list', 'cart_thumb');

		$data['order_image'] = array();

		foreach ($images_name as $value) {
			$data['order_image'][$value]['name'] = $this->language->get('entry_image_' . $value);

			if (isset($this->request->post['order_image_' . $value . '_width'])) {
				$data['order_image'][$value]['width'] = $this->request->post['order_image_' . $value . '_width'];
			} elseif (isset($setting_info['order_image_' . $value . '_width'])) {
				$data['order_image'][$value]['width'] = $setting_info['order_image_' . $value . '_width'];
			} else {
				$data['order_image'][$value]['width'] = 300;
			}

			if (isset($this->request->post['order_image_' . $value . '_height'])) {
				$data['order_image'][$value]['height'] = $this->request->post['order_image_' . $value . '_height'];
			} elseif (isset($setting_info['order_image_' . $value . '_height'])) {
				$data['order_image'][$value]['height'] = $setting_info['order_image_' . $value . '_height'];
			} else {
				$data['order_image'][$value]['height'] = 300;
			}
		}

		$this->load->model('localisation/listing');

		$data['listings'] = $this->model_localisation_listing->getListings();
		$data['listing_items'] = $this->model_localisation_listing->getListingItems(array('filter_listing_id' => $data['order_status_listing']));

		$data['content'] = $this->load->view('extension/system/order', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	public function install() {
		$table = new \Model\DBTable($this->registry);
		$table->create('order');

		$this->load->model('core/event');
		$this->model_core_event->addEvent('order', 'api/controller/startup/event/after', 'event/order/startup');
		$this->model_core_event->addEvent('order', 'admin/controller/startup/event/after', 'event/order/startup');
		$this->model_core_event->addEvent('order', 'client/controller/startup/event/after', 'event/order/startup');

		$this->model_core_event->addEvent('order_mail', 'client/model/order/order_history/addOrderHistory/before', 'mail/order');
		$this->model_core_event->addEvent('order_mail', 'client/model/order/order_history/addOrderHistory/before', 'mail/order/alert');

		$this->load->model('design/seo_url');
		$this->model_design_seo_url->addSeoUrl(array('store_id' => 0, 'language_id' => 1, 'query' => 'route=order/cart', 'keyword' => 'cart', 'push' => 'route=order/cart'));
		$this->model_design_seo_url->addSeoUrl(array('store_id' => 0, 'language_id' => 1, 'query' => 'route=order/checkout', 'keyword' => 'checkout', 'push' => 'route=order/checkout'));
	}

	public function uninstall() {
		$table = new \Model\DBTable($this->registry);
		$table->delete('order');

		$this->load->model('core/event');
		$this->model_core_event->deleteEventByCode('order');
		$this->model_core_event->deleteEventByCode('order_mail');

		$this->load->model('design/seo_url');
		$this->model_design_seo_url->deleteSeoUrlByQuery('route=order/');
	}

	public function update() {
		// $table = new \Model\DBTable($this->registry);
		// $table->update('order');
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/system/order')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}
}