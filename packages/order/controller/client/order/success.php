<?php
namespace Controller\Order;

class Success extends \Controller {
	public function index() {
		$this->load->language('order/success');

		if (isset($this->session->data['order_id'])) {
			$order_id = $this->session->data['order_id'];

			$this->cart->clear();

			$this->load->controller('order/checkout/reset');

			unset($this->session->data['additionally']);
			unset($this->session->data['address']);
			unset($this->session->data['contact']);

			unset($this->session->data['order_id']);
		} else {
			$order_id = '';

			$this->load->controller('order/checkout/reset');
		}

		$this->document->setTitle($this->language->get('heading_title'));

		if ($this->account && $this->account->isLogged()) {
			$data['account'] = $this->url->link('account/account');
			$data['order'] = $this->url->link('account/order');
		} else {
			$data['account'] = '';
			$data['order'] = '';
		}

		$data['text_order'] = $order_id ? sprintf($this->language->get('text_order'), $order_id) : '';
		$data['text_message'] = sprintf($this->language->get('text_guest'), $this->url->link('common/contact'));

		if ($order_id  && !empty($this->request->get['draft'])) {
			$data['text_success'] = $this->language->get('text_order_draft');
			$data['text_order'] = '';
		}

		$data['home'] = $this->url->link('common/home');

		$data['content'] = $this->load->view('order/success', $data);

		$this->response->addHeader('Cache-Control: no-cache');
		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	public function confirm() {
		$this->load->model('order/order_history');

		if (empty($this->session->data['order_id'])) {
			$this->response->redirect($this->url->link('order/cart'));
		}

		$this->model_order_order_history->addOrderHistory($this->session->data['order_id'], $this->config->get('cart_order_status_id'));
		
		$this->response->redirect($this->url->link('order/success'));
	}
}