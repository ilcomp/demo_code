<?php
namespace Controller\Extension\Total;

class Coupon extends \Controller {
	public function index() {
		if ($this->config->get('total_coupon_status')) {
			$this->load->language('extension/total/coupon');

			if (isset($this->request->post['coupon_code'])) {
				$data['coupon_code'] = $this->request->post['coupon_code'];
			} elseif (isset($this->session->data['coupon_code'])) {
				$data['coupon_code'] = $this->session->data['coupon_code'];
			} else {
				$data['coupon_code'] = '';
			}

			$data['action'] = $this->url->link('extension/total/coupon/save');

			return $this->load->view('extension/total/coupon', $data);
		}
	}

	public function save() {
		$this->load->language('extension/total/coupon');

		$json = array();

		$this->load->model('extension/total/coupon');

		if (isset($this->request->post['coupon_code'])) {
			$coupon_code = $this->request->post['coupon_code'];
		} else {
			$coupon_code = '';
		}

		$coupon_info = $this->model_extension_total_coupon->getCoupon($coupon_code);

		if (empty($coupon_code)) {
			$json['error'] = $this->language->get('error_empty');

			unset($this->session->data['coupon_code']);
		} elseif ($coupon_info) {
			$this->session->data['coupon_code'] = $coupon_code;

			$this->session->data['success'] = $this->language->get('text_success');

			$json['success'] = $this->language->get('text_success');
		} else {
			$json['error'] = $this->language->get('error_coupon');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}
}
