<?php
namespace Controller\Event;

class Coupon extends \Controller {
	public function startup() {
		$this->event->register('view/block/column_left/before', new \Action('event/coupon/menu'), 0);

		if ($this->config->get('total_coupon_status')) {
			$this->event->register('model/order/order_history/order_status_id/before', new \Action('event/coupon/confirm'), 0);
			$this->event->register('model/order/order_history/order_status_id/after', new \Action('event/coupon/unconfirm'), 0);
		}
	}

	public function menu($route, &$data) {
		$this->load->language('extension/total/coupon', 'temp');

		$language = $this->language->get('temp');

		foreach ($data['menus'] as &$value) {
			if ($value['id'] == 'menu-marketing') {
				$value['children'][] = array(
					'name'	   => $language->get('text_coupon'),
					'href'     => $this->url->link('marketing/coupon'),
					'children' => array()
				);
				break;
			}
		}
		unset($value);
	}

	public function confirm($route, $data) {
		$statuses = array(0, (int)$this->config->get('order_status_canceled_id'), (int)$this->config->get('order_status_draft_id'));

		if (in_array((int)$data[0]['order_status_id'], $statuses) && !in_array((int)$data[1], $statuses)) {
			$this->load->model('extension/total/coupon');

			foreach ($data[0]['totals'] as $order_total) {
				if ($order_total['code'] == 'coupon') {
					if (!$this->model_extension_total_coupon->confirm($data[0], $order_total))
						return $this->config->get('total_coupon_status_fraud_id');
				}
			}
		}
	}

	public function unconfirm($route, $data, $output) {
		$statuses = array(0, (int)$this->config->get('order_status_canceled_id'), (int)$this->config->get('order_status_draft_id'));

		if (!in_array((int)$data[0]['order_status_id'], $statuses) && in_array((int)$data[1], $statuses)) {
			$this->load->model('extension/total/coupon');

			foreach ($data[0]['totals'] as $order_total) {
				if ($order_total['code'] == 'coupon') {
					$this->model_extension_total_coupon->unconfirm($data[0]['order_id']);
				}
			}
		}
	}
}