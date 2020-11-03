<?php
namespace Controller\Extension\Payment;

class Constructor extends \Controller {
	public function index() {
		$code = explode('.', $this->session->data['payment_method']['code']);

		if ($code[0] == 'constructor') {
			$this->load->model('order/order_history');

			$this->model_order_order_history->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_constructor_order_status_id'));

			return true;
		}		
	}
}
