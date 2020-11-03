<?php
namespace Controller\Block\Order;

class Confirm extends \Controller {
	public function index() {
		$data = (array)$this->config->get('block_order_confirm');

		if (!isset($data['redirect'])) {
			$data['redirect'] = '';
		}

		if (!isset($data['payment'])) {
			$data['payment'] = '';
		}

		$this->load->language('order/checkout');

		return $this->load->view('block/order/confirm', $data);
	}

	public function form($data = array()) {
		$this->load->language('order/checkout');

		if (isset($this->request->post['submit']) && $this->request->post['submit'] && isset($this->session->data['order']['validate']) && $this->session->data['order']['validate']) {
			$order_data = $this->session->data['order'];

			$order_data['products'] = $this->cart->getProducts();

			$this->load->model('order/order');

			$this->session->data['order_id'] = $this->model_order_order->addOrder($order_data);

			$this->load->model('order/order_history');

			if ($this->request->post['submit'] == 1) {
				if ($this->session->data['payment_method']) {
					$payment_method = explode('.', $this->session->data['payment_method']['code']);

					$data['payment'] = $this->load->controller('extension/payment/' . $payment_method[0]);

					if ($data['payment'])
						$data['redirect'] = $this->url->link('order/success');
					else
						$data['redirect'] = $this->url->link('order/success/confirm');
				}
			} elseif ($this->request->post['submit'] == 2) {
				$this->model_order_order_history->addOrderHistory($this->session->data['order_id'], $this->config->get('cart_order_status_draft_id'));

				$data['redirect'] = $this->url->link('order/success', 'draft=true');
			}
		}

		$this->config->set('block_order_confirm', $data);
	}
}
