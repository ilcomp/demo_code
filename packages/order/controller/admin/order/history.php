<?php
namespace Controller\Order;

class History extends \Controller {
	public function index() {
		$this->load->language('order/order');

		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['histories'] = array();

		$this->load->model('order/order_history');

		$results = $this->model_order_order_history->getOrderHistories($order_id, ($page - 1) * 10, 10);

		foreach ($results as $result) {
			$data['histories'][] = array(
				'notify'     => $result['notify'] ? $this->language->get('text_yes') : $this->language->get('text_no'),
				'status'     => $result['status'],
				'comment'    => nl2br($result['comment']),
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
			);
		}

		$history_total = $this->model_order_order_history->getTotalOrderHistories($order_id);

		$url = array();
		$url['user_token'] = $this->session->data['user_token'];
		$url['order_id'] = $order_id;
		$url['page'] = '{page}';

		$data['pagination'] = $this->load->controller('block/pagination', array(
			'total' => $history_total,
			'page'  => $page,
			'limit' => 10,
			'url'   => $this->url->link('order/order/history', $url)
		));

		$data['results'] = $this->load->controller('block/pagination/result', array(
			'total' => $history_total,
			'page'  => $page,
			'limit' => 10
		));

		$this->response->setOutput($this->load->view('order/order_history', $data));
	}

	public function add() {
		$this->load->language('api/order');

		$json = array();

		// Add keys for missing post vars
		$keys = array(
			'order_status_id',
			'notify',
			'override',
			'comment'
		);

		foreach ($keys as $key) {
			if (!isset($this->request->post[$key])) {
				$this->request->post[$key] = '';
			}
		}

		$this->load->model('order/order_history');

		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}

		if ($this->model_order_order_history->addOrderHistory($order_id, $this->request->post['order_status_id'], $this->request->post['comment'], $this->request->post['notify'], $this->request->post['override'])) {
			$json['success'] = $this->language->get('text_success');
		} else {
			$json['error'] = $this->language->get('error_not_found');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}
}