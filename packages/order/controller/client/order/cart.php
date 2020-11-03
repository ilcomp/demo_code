<?php
namespace Controller\Order;

class Cart extends \Controller {
	public function index($data = array(), $blocks = array('cart_product', 'cart_total')) {
		$this->load->language('order/cart');

		$data['block'] = array();

		foreach ($blocks as $block) {
			$data['block'][$block] = $this->load->controller('block/order/' . $block);
		}

		$data['has_products'] = $this->cart->hasProducts();

		if (!empty($data['has_products'])) {
			if (isset($this->session->data['error'])) {
				$data['error_warning'] = $this->session->data['error'];

				unset($this->session->data['error']);
			} else {
				$data['error_warning'] = '';
			}

			if (isset($this->session->data['attention'])) {
				$data['attention'] = $this->session->data['attention'];

				unset($this->session->data['attention']);
			} else {
				$data['attention'] = '';
			}

			if (isset($this->session->data['success'])) {
				$data['success'] = $this->session->data['success'];

				unset($this->session->data['success']);
			} else {
				$data['success'] = '';
			}

			$data['continue'] = $this->url->link('common/home');

			$data['actions']['cart'] = $this->url->link('order/cart');
			$data['actions']['checkout'] = $this->url->link('order/checkout');

			$data['count'] = $this->cart->countProducts();

			if (isset($this->session->data['error_cart'])) {
				if (empty($data['error_warning']))
					$data['error_warning'] = $this->session->data['error_cart'];

				unset($this->session->data['error_cart']);
			}

			if (empty($data['error_warning']) && $this->config->get('cart_min_amount') > $this->cart->getTotal()) {
				$data['error_warning'] = sprintf($this->language->get('error_cart_total'), $this->currency->format($this->config->get('cart_min_amount'), $this->session->data['currency']));
			}
		}

		$this->response->addHeader('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
		$this->response->addHeader('Pragma: no-cache');

		if ($this->request->server['HTTP_ACCEPT'] == 'text/html') {
			$this->response->setOutput($this->load->view('order/cart', $data));
		} else {
			$this->document->setTitle($this->language->get('heading_title'));

			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home')
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('order/cart')
			);

			$data['content'] = $this->load->view('order/cart', $data);

			$this->response->setOutput($this->load->controller('common/template', $data));
		}
	}

	public function form($json = array(), $blocks = array('cart_product', 'cart_total')) {
		$this->load->language('order/cart');

		$this->session->data['order']['validate'] = true;

		foreach ($blocks as $block) {
			$this->load->controller('block/order/' . $block . '/form');
		}

		if (isset($this->session->data['error']) || isset($this->session->data['error_cart']))
			unset($this->session->data['success_cart']);
		else
			$this->session->data['success_cart'] = true;

		if ($this->request->server['HTTP_ACCEPT'] == 'application/json') {
			$json['success'] = true;

			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput($json);
		} elseif ($this->request->server['HTTP_ACCEPT'] == 'text/html') {
			$this->config->set('form_cart', $json);
		} elseif (!empty($this->session->data['success_cart'])) {
			unset($this->session->data['success']);

			$this->response->redirect($this->url->link('order/checkout', !empty($this->request->post['next']) ? 'next=1' : ''));
		}
	}

	public function add($json = array()) {
		$this->load->model('catalog/product');

		$this->load->language('order/cart');

		$json = array_merge($this->language->all(), $json);

		$json['cart'] = array();

		if (isset($this->request->post['product_id'])) {
			$this->request->post['product'] = array();

			$this->request->post['product'][] = array(
				'product_id' => $this->request->post['product_id'],
				'quantity' => isset($this->request->post['quantity']) ? $this->request->post['quantity'] : 1,
				'option' => isset($this->request->post['option']) ? $this->request->post['option'] : array()
			);
		}

		$empty = empty($json['error']);

		if ($empty && !empty($this->request->post['product'])) {
			foreach ($this->request->post['product'] as $product) {
				if (!empty($product['quantity'])) {
					$empty = false;

					$product_info = $this->model_catalog_product->getProduct($product['product_id']);

					if ($product_info) {
						if (isset($product['option'])) {
							$product['option'] = array_filter($product['option']);
						} else {
							$product['option'] = array();
						}

						$this->cart->add($product['product_id'], $product['quantity'], $product['option']);

						if (empty($product_info['title']))
							$product_info['title'] = $product_info['name'];

						$product_info['href'] = $this->url->link('catalog/product', 'catalog_product_id=' . $product['product_id']);

						$json['cart'][]['product'] = $product_info;

						$json['success'] = sprintf($this->language->get('text_success_add'), $product_info['href'], $product_info['title']);
					}
				}
			}

			$json['link_cart'] = $this->url->link('order/cart');

			$this->load->controller('order/checkout/reset');
		}

		if ($empty) {
			$json['error'] = $this->language->get('error_product_add');
		}

		if ($this->request->server['HTTP_ACCEPT'] == 'application/json') {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput($json);
		} else {
			return array('form_result' => $json);
		}
	}

	public function edit($json = array()) {
		$this->load->language('order/cart');

		if (!empty($this->request->post['quantity'])) {
			$this->request->post['cart'] = array();

			foreach ($this->request->post['quantity'] as $cart_id => $quantity)
				$this->request->post['cart'][] = array(
					'cart_id' => $cart_id,
					'quantity' => $quantity
				);
		}

		if (!empty($this->request->post['cart'])) {
			foreach ($this->request->post['cart'] as $cart)
				$this->cart->update($cart['cart_id'], $cart['quantity']);

			$this->session->data['success'] = $this->language->get('text_success_update');

			$this->load->controller('order/checkout/reset');
		}

		if ($this->request->server['HTTP_ACCEPT'] == 'application/json') {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput($json);
		} else {
			return array('form_result' => $json);
		}
	}

	public function remove($json = array()) {
		$this->load->language('order/cart');

		if (!empty($this->request->request['cart_id'])) {
			$this->request->post['cart'] = array();

			$this->request->post['cart'][] = array('cart_id' => $this->request->request['cart_id']);
		}

		if (!empty($this->request->post['cart'])) {
			foreach ($this->request->post['cart'] as $cart)
				$this->cart->remove($cart['cart_id']);

			$json['success'] = $this->language->get('text_success_remove');

			$this->load->controller('order/checkout/reset');
		}

		if ($this->request->server['HTTP_ACCEPT'] == 'application/json') {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput($json);
		} else {
			$this->response->redirect($this->url->link('order/cart'));
		}
	}
}
