<?php
namespace Controller\Event;

class Reward extends \Controller {
	public function startup() {
		if ($this->config->get('total_reward_status')) {
			$this->event->register('model/account/account/deleteAccount/after', new \Action('event/reward/model_account'), 0);
			$this->event->register('view/account/account_form/before', new \Action('event/reward/account_form'), 0);

			$this->event->register('model/catalog/product_modify/*Product/after', new \Action('event/reward/model_product'), 0);
			$this->event->register('view/catalog/product_form/before', new \Action('event/reward/product_form'), 0);

			$this->event->register('model/order/order_history/order_status_id/after', new \Action('event/reward/confirm'), 0);
			$this->event->register('model/order/order_history/order_status_id/after', new \Action('event/reward/unconfirm'), 1);
		}
	}

	public function model_account(&$route, &$data, $output = '') {
		if ($route == 'account/account/deleteAccount') {
			$this->load->model('extension/total/reward');

			$this->model_extension_total_reward->deleteAccountReward((int)$data[0]);
		}
	}

	public function account_form(&$route, &$data) {
		if (!empty($this->request->get['account_id'])) {
			$data_group['actions']['view'] = $this->url->link('extension/total/reward/view', 'account_id=' . $this->request->get['account_id']);
			$data_group['actions']['add'] = $this->url->link('extension/total/reward/add');
			$data_group['account_id'] = $this->request->get['account_id'];

			if (!isset($data['additional_fields']))
				$data['additional_fields'] = '';

			$data['additional_fields'] .= $this->load->view('account/reward_view', $data_group);
		}
	}

	public function model_product(&$route, &$data, $output = '') {
		if ($route == 'catalog/product_modify/addProduct') {
			$this->load->model('extension/total/reward');

			$reward = isset($data[0]['reward']) ? $data[0]['reward'] : 0;

			$this->model_extension_total_reward->updateProductReward((int)$output, $reward);
		} elseif ($route == 'catalog/product_modify/editProduct') {
			$this->load->model('extension/total/reward');

			$reward = isset($data[1]['reward']) ? $data[1]['reward'] : 0;

			$this->model_extension_total_reward->updateProductReward((int)$data[0], $reward);
		} elseif ($route == 'catalog/product_modify/deleteProduct') {
			$this->load->model('extension/total/reward');

			$this->model_extension_total_reward->deleteProductReward((int)$data[0]);
		}
	}

	public function product_form(&$route, &$data) {
		$this->load->language('extension/total/reward', 'temp');

		$language = $this->language->get('temp');

		$data_group = $language->all();

		$this->load->model('extension/total/reward');

		if (isset($this->request->post['reward']))
			$data_group['reward'] = $this->request->post['reward'];
		elseif (isset($this->request->get['catalog_product_id']))
			$data_group['reward'] = (int)$this->model_extension_total_reward->getProductRewardValue($this->request->get['catalog_product_id']);
		else
			$data_group['reward'] = 0;

		$data['additional_fields'] .= $this->load->view('catalog/reward_view', $data_group);
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
}