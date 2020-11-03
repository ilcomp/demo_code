<?php
namespace Controller\Event;

class Reward extends \Controller {
	public function startup() {
		if ($this->config->get('total_reward_status')) {
			$this->event->register('model/account/account/deleteAccount/after', new \Action('event/reward/model_account'), 0);
			$this->event->register('model/catalog/product/getProduct/sql', new \Action('event/reward/getProduct_sql'), 0);

			$this->event->register('view/block/order/cart_product/before', new \Action('event/reward/cart_product'), 0);
			$this->event->register('view/account/account/before', new \Action('event/reward/account'), 0);

			$this->event->register('model/order/order_history/order_status_id/after', new \Action('event/reward/confirm'), 0);
			$this->event->register('model/order/order_history/order_status_id/after', new \Action('event/reward/unconfirm'), 1);
		}

		$this->event->register('controller/order/checkout/reset/before', new \Action('event/reward/checkout_reset'), 0);
	}

	public function model_account($route, $args, $output = '') {
		if ($route == 'account/account/deleteAccount') {
			$this->load->model('extension/total/reward');

			$this->model_extension_total_reward->deleteAccountReward((int)$args[0]);
		}
	}

	public function getProduct_sql($data, &$join, &$where, &$columns) {
		$join[] = "LEFT JOIN " . DB_PREFIX . "reward_product rp ON (p.product_id = rp.product_id)";

		$columns[] = "rp.reward";
	}

	public function cart_product($route, &$data) {
		// if ($this->account->isLogged()) {
			$this->load->model('extension/total/reward');

			foreach ($data['products'] as &$product) {
				$product['reward'] = $this->model_extension_total_reward->getProductRewardValue($product['product_id']);
			}
			unset($product);
		// }
	}

	public function account($route, &$data) {
		$this->load->model('extension/total/reward');

		$data['reward'] = $this->model_extension_total_reward->getAccountRewardValue($this->account->getId());
	}

	public function confirm($route, $data, $output) {
		$this->load->language('extension/total/reward', 'reward');
		$this->load->model('extension/total/reward');

		if ((int)$data[0]['order_status_id'] != (int)$this->config->get('order_status_completed_id') && (int)$data[1] == (int)$this->config->get('order_status_completed_id')) {
			foreach ($data[0]['totals'] as $order_total) {
				if ($order_total['code'] == 'reward' && (int)$order_total['value'] > 0) {
					$this->model_extension_total_reward->addAccountReward($data[0]['account_id'], (int)$order_total['value'], sprintf($this->language->get('reward')->get('text_note'), $order_total['title'], $data[0]['order_id']));
				}
			}
		}

		$statuses = array(0, (int)$this->config->get('order_status_canceled_id'), (int)$this->config->get('order_status_draft_id'));

		if (in_array((int)$data[0]['order_status_id'], $statuses) && !in_array((int)$data[1], $statuses)) {
			foreach ($data[0]['totals'] as $order_total) {
				if ($order_total['code'] == 'reward' && (int)$order_total['value'] < 0) {
					$this->model_extension_total_reward->addAccountReward($data[0]['account_id'], (int)$order_total['value'], sprintf($this->language->get('reward')->get('text_note'), $order_total['title'], $data[0]['order_id']));
				}
			}
		}
	}

	public function unconfirm($route, $data, $output) {
		$this->load->language('extension/total/reward', 'reward');
		$this->load->model('extension/total/reward');

		if ((int)$data[0]['order_status_id'] == (int)$this->config->get('order_status_completed_id') && (int)$data[1] != (int)$this->config->get('order_status_completed_id')) {
			foreach ($data[0]['totals'] as $order_total) {
				if ($order_total['code'] == 'reward' && (int)$order_total['value'] > 0) {
					$this->model_extension_total_reward->addAccountReward($data[0]['account_id'], -(int)$order_total['value'], sprintf($this->language->get('reward')->get('text_cancel'), $order_total['title'], $data[0]['order_id']));
				}
			}
		}

		$statuses = array(0, (int)$this->config->get('order_status_canceled_id'), (int)$this->config->get('order_status_draft_id'));

		if (!in_array((int)$data[0]['order_status_id'], $statuses) && in_array((int)$data[1], $statuses)) {
			foreach ($data[0]['totals'] as $order_total) {
				if ($order_total['code'] == 'reward' && (int)$order_total['value'] < 0) {
					$this->model_extension_total_reward->addAccountReward($data[0]['account_id'], -(int)$order_total['value'], sprintf($this->language->get('reward')->get('text_cancel'), $order_total['title'], $data[0]['order_id']));
				}
			}
		}
	}

	public function checkout_reset($route, $args) {
		unset($this->session->data['reward_status']);
		unset($this->session->data['reward_value']);
	}
}