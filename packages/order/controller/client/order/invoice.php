<?php
class ControllerOrderInvoice extends Controller {
	public function index() {
		if (isset($this->request->get['order_id'])) {
			$this->load->model('order/order');

			$this->session->data['order_id'] = $this->request->get['order_id'];

			$order_info = $this->model_order_order->getOrder($this->request->get['order_id']);

			if ($order_info['account_id'] && $this->account && $order_info['account_id'] != $this->account->getId()) {
				return new \Action('account/login');
			}

			$this->load->language('order/invoice');

			$payment_method = explode('.', $order_info['payment_code']);

			$data['payment'] = $this->load->controller('extension/payment/' . $payment_method[0]);

			$data['content'] = $this->load->view('order/invoice', $data);

			$this->response->setOutput($this->load->controller('common/template', $data));
		}
	}
}