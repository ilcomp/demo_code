<?php
namespace Controller\Event;

class Coupon extends \Controller {
	public function startup() {
		if ($this->config->get('total_coupon_status')) {
			$this->event->register('model/order/order_history/order_status_id/before', new \Action('event/coupon/confirm'), 0);
			$this->event->register('model/order/order_history/order_status_id/after', new \Action('event/coupon/unconfirm'), 0);
		}

		$this->event->register('controller/order/checkout/reset/before', new \Action('event/coupon/checkout_reset'), 0);
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

	public function checkout_reset($route, $arg) {
		unset($this->session->data['coupon_code']);
	}
}