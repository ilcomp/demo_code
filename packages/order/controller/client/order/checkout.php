<?php
namespace Controller\Order;

class Checkout extends \Controller {
	public function index($data = array(), $blocks = array('contact', 'total_product')) {
		$this->load->language('order/checkout');

		$data['redirect'] = '';

		// Validate cart has products and has stock.
		if (!$this->cart->hasProducts() || empty($this->session->data['success_cart'])) {
			$data['redirect'] = $this->url->link('order/cart');
		}

		if (!$data['redirect']) {
			$data['block'] = array();

			foreach ($blocks as $block) {
				$data['block'][$block] = $this->load->controller('block/order/' . $block);
			}

			if (isset($this->session->data['error'])) {
				$data['error_warning'] = $this->session->data['error'];

				unset($this->session->data['error']);
			} else {
				$data['error_warning'] = '';
			}

			$data['actions']['cart'] = $this->url->link('order/cart');
			$data['actions']['checkout'] = $this->url->link('order/checkout');
			$data['actions']['confirm'] = $this->url->link('order/confirm');

			$data['next'] = !empty($this->request->get['next']);
		}

		$this->response->addHeader('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
		$this->response->addHeader('Pragma: no-cache');

		if ($this->request->server['HTTP_ACCEPT'] == 'text/html') {
			$this->response->setOutput($this->load->view('order/checkout', $data));
		} else {
			if ($data['redirect'])
				$this->response->redirect($data['redirect']);

			$this->document->setTitle($this->language->get('heading_title'));

			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home')
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_cart'),
				'href' => $this->url->link('order/cart')
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('order/checkout')
			);

			$data['content'] = $this->load->view('order/checkout', $data);

			$this->response->setOutput($this->load->controller('common/template', $data));
		}
	}

	public function form($json = array(), $blocks = array('contact')) {
		$this->load->language('order/checkout');

		$data['redirect'] = '';

		if (!$this->cart->hasProducts() || empty($this->session->data['success_cart'])) {
			$data['redirect'] = $this->url->link('order/cart');
		}

		$this->session->data['order']['validate'] = empty($data['redirect']);

		foreach ($blocks as $block) {
			$this->load->controller('block/order/' . $block . '/form');
		}

		if (isset($this->session->data['error']))
			unset($this->session->data['success_checkout']);
		else
			$this->session->data['success_checkout'] = true;

		if ($this->request->server['HTTP_ACCEPT'] == 'application/json') {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput($json);
		} elseif ($this->request->server['HTTP_ACCEPT'] == 'text/html') {
			$this->config->set('form_checkout', $json);
		} elseif (!empty($this->session->data['success_cart']) && !empty($this->session->data['success_checkout'])) {
			unset($this->session->data['success']);

			$this->response->redirect($this->url->link('order/confirm'));
		}
	}

	public function reset() {
		// unset($this->session->data['shipping_methods']);
		// unset($this->session->data['payment_methods']);
		unset($this->session->data['shipping_method']);
		unset($this->session->data['payment_method']);

		unset($this->session->data['success_cart']);
		unset($this->session->data['success_checkout']);
		unset($this->session->data['success_confirm']);

		unset($this->session->data['order']);
		$this->session->data['order']['validate'] = true;
	}
}