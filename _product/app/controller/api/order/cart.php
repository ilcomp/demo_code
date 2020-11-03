<?php
namespace Controller\Order;

class Cart extends \Controller {
	public function index() {
		// Totals
		$this->load->model('core/extension');

		$totals = array();
		$total = 0;

		// Because __call can not keep var references so we put them into an array.
		$total_data = array(
			'totals' => &$totals,
			'total'  => &$total
		);

		// Display prices
		$sort_order = array();

		$results = $this->model_core_extension->getExtensions('total');

		foreach ($results as $key => $value) {
			$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
		}

		array_multisort($sort_order, SORT_ASC, $results);

		foreach ($results as $result) {
			if ($result['code'] == 'sub_total' && $this->config->get('total_' . $result['code'] . '_status')) {
				$this->load->model('extension/total/' . $result['code']);

				// We have to put the totals in an array so that they pass by reference.
				$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
			}
		}

		$sort_order = array();

		foreach ($totals as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC, $totals);

		$data['count_items'] = $this->cart->countProducts();
		$data['total'] = $total;
		$data['total_format'] = $this->currency->format($data['total'], $this->session->data['currency']);
		$data['format'] = $this->currency->get($this->session->data['currency']);

		$this->load->model('tool/upload');

		$data['products'] = array();

		foreach ($this->cart->getProducts() as $product) {
			$option_data = array();

			foreach ($product['option'] as $option) {
				if (isset($option['type'])) {
					if ($option['type'] != 'file') {
						$value = $option['value'];
					} else {
						$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

						if ($upload_info) {
							$value = $upload_info['name'];
						} else {
							$value = '';
						}
					}

					$option_data[] = array(
						'name'  => $option['name'],
						'value' => $value,
						'type'  => $option['type']
					);
				} else {
					$option_data[] = array(
						'name'  => '',
						'value' => $option,
						'type'  => ''
					);
				}
			}

			$price = $this->currency->format($product['price'], $this->session->data['currency']);
			$total = $this->currency->format($product['price'] * $product['quantity'], $this->session->data['currency']);

			$product['option'] = $option_data;
			$product['price'] = $price;
			$product['total'] = $total;
			$product['href'] = $this->url->link('catalog/product', 'product_id=' . $product['product_id']);

			$data['products'][] = $product;
		}

		$data['totals'] = array();

		foreach ($totals as $total) {
			$data['totals'][] = array(
				'title' => $total['title'],
				'text'  => $this->currency->format($total['value'], $this->session->data['currency']),
			);
		}

		$data['cart'] = $this->url->link('order/cart');
		$data['checkout'] = $this->url->link('order/checkout');

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($data);
	}

	public function create() {
		$this->load->language('api/cart');

		$json = array();

		$this->cart->clear();

		foreach ($this->request->post['product'] as $product) {
			if (isset($product['option'])) {
				$option = $product['option'];
			} else {
				$option = array();
			}

			$this->cart->add($product['product_id'], $product['quantity'], $option);
		}
		$json['test'] = $this->session->getId();

		$json['success'] = $this->language->get('text_success');

		// unset($this->session->data['shipping_method']);
		// unset($this->session->data['shipping_methods']);
		// unset($this->session->data['payment_method']);
		// unset($this->session->data['payment_methods']);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}

	public function add() {
		$this->load->language('api/cart');

		$json = array();

		$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct($this->request->post['product_id']);

		if ($product_info) {
			if (isset($this->request->post['quantity'])) {
				$quantity = $this->request->post['quantity'];
			} else {
				$quantity = 1;
			}

			if (isset($this->request->post['option'])) {
				$option = array_filter($this->request->post['option']);
			} else {
				$option = array();
			}

			$this->cart->add($this->request->post['product_id'], $quantity, $option);

			$json['success'] = $this->language->get('text_success');
		} else {
			$json['error']['store'] = $this->language->get('error_store');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}

	public function get_cart() {
		$this->load->language('api/cart');

		$json = array();

		// Products
		$json['products'] = array();
		$json['test'] = $this->session->getId();

		$products = $this->cart->getProducts();

		foreach ($products as $product) {
			$product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}

			if (!empty($product['price']))
				$product['price'] = $this->currency->convert($product['price'], $product['currency_id'], $this->session->data['currency']);
			if (!empty($product['total']))
				$product['total'] = $this->currency->convert($product['total'], $product['currency_id'], $this->session->data['currency']);

			if (isset($product['price']))
				$product['price_format'] = $this->currency->format($product['price'], $this->session->data['currency']);

			if (isset($product['total']))
				$product['total_format'] = $this->currency->format($product['total'], $this->session->data['currency']);

			$product['title'] = $product['title'] ? $product['title'] : $product['name'];

			$json['products'][] = $product;
		}

		// Totals
		$this->load->model('core/extension');

		$totals = array();
		$total = 0;

		// Because __call can not keep var references so we put them into an array.
		$total_data = array(
			'totals' => &$totals,
			'total'  => &$total
		);

		$sort_order = array();

		$results = $this->model_core_extension->getExtensions('total');

		foreach ($results as $key => $value) {
			$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
		}

		array_multisort($sort_order, SORT_ASC, $results);

		$json['modules'] = array();

		foreach ($results as $result) {
			if ($result['code'] != 'shipping' && $this->config->get('total_' . $result['code'] . '_status')) {
				$this->load->model('extension/total/' . $result['code']);

				// We have to put the totals in an array so that they pass by reference.
				$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);

				$module = $this->load->controller('extension/total/' . $result['code']);

				if ($module)
					$json['modules'][] = $module;
			}
		}

		$sort_order = array();

		foreach ($totals as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC, $totals);

		$json['totals'] = array();

		foreach ($totals as $total) {
			$total['text'] = $this->currency->format($total['value'], $this->session->data['currency']);
			$json['totals'][] = $total;
		}

		if (empty($this->request->get['light']))
			$this->event->trigger('controller/api/cart/products', array(&$json));

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}

	public function edit_total() {
		$this->load->language('api/cart');

		$json = array();

		if (!$this->config->get('total_' . $this->request->post['code'] . '_status'))
			$json['error'] = $this->language->get('error_total');
		else {
			$this->load->model('extension/total/' . $this->request->post['code']);

			if (property_exists($this->{'model_extension_total_' . $this->request->post['code']}, 'editTotal')) {
				$this->{'model_extension_total_' . $this->request->post['code']}->editTotal($this->request->post['value']);
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}
}
