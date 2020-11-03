<?php
namespace Controller\Order;

class Confirm extends \Controller {
	public function index($data = array(), $blocks = array('address', 'shipping_method', 'payment_method', 'additionally', 'total_product', 'total', 'confirm')) {
		$this->load->language('order/checkout');
		$this->load->language('order/confirm');

		$data['redirect'] = '';

		// Validate cart has products and has stock.
		if (!$this->cart->hasProducts() || empty($this->session->data['success_cart'])) {
			$data['redirect'] = $this->url->link('order/cart');
		}

		if (empty($this->session->data['success_checkout'])) {
			$data['redirect'] = $this->url->link('order/checkout');
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
		}

		$data['count'] = $this->cart->countProducts();

		$this->response->addHeader('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
		$this->response->addHeader('Pragma: no-cache');

		if ($this->request->server['HTTP_ACCEPT'] == 'text/html') {
			$this->response->setOutput($this->load->view('order/confirm', $data));
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
				'text' => $this->language->get('text_checkout'),
				'href' => $this->url->link('order/checkout')
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('order/confirm')
			);

			$data['content'] = $this->load->view('order/confirm', $data);

			$this->response->setOutput($this->load->controller('common/template', $data));
		}
	}

	public function form($json = array(), $blocks = array('address', 'shipping_method', 'payment_method', 'additionally', 'total_product', 'total', 'confirm')) {
		$this->load->language('order/checkout');
		$this->load->language('order/confirm');

		$data['redirect'] = '';

		if (!$this->cart->hasProducts() || empty($this->session->data['success_cart'])) {
			$data['redirect'] = $this->url->link('order/cart');
		}

		if (empty($this->session->data['success_checkout'])) {
			$data['redirect'] = $this->url->link('order/checkout');
		}

		$this->session->data['order']['validate'] = empty($data['redirect']);

		foreach ($blocks as $block) {
			$this->load->controller('block/order/' . $block . '/form');
		}

		if (isset($this->session->data['error']))
			unset($this->session->data['success_confirm']);
		else
			$this->session->data['success_confirm'] = true;

		if ($this->request->server['HTTP_ACCEPT'] == 'application/json') {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput($json);
		} elseif ($this->request->server['HTTP_ACCEPT'] == 'text/html') {
			$this->config->set('form_confirm', $json);
		// } elseif (!empty($this->session->data['success_cart']) && !empty($this->session->data['success_checkout']) && !empty($this->session->data['success_confirm'])) {
			// unset($this->session->data['success']);
		// 	$this->response->redirect($this->url->link('order/confirm'));
		}
	}
}