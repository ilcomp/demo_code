<?php
namespace Controller\Block\Order;

class CartProduct extends \Controller {
	public function index() {
		$this->load->language('order/cart');

		$products = $this->cart->getProducts();

		if (!empty($products)) {
			$this->load->model('catalog/product');
			$this->load->model('tool/image');

			$data['image_width'] = $this->config->get('order_image_cart_list_width');
			$data['image_height'] = $this->config->get('order_image_cart_list_height');

			$data['products'] = array();

			foreach ($products as $product) {
				if ($product['quantity'] > 0) {
					$product_total = 0;

					foreach ($products as $product_2) {
						if ($product_2['product_id'] == $product['product_id']) {
							$product_total += $product_2['quantity'];
						}
					}

					$image = $this->model_catalog_product->getProductImageMain($product['product_id']);

					if ($image) {
						$product['image'] = $image['image'];
						$product['thumb'] = $this->model_tool_image->resize($image['image'], $data['image_width'], $data['image_height']);
					} else {
						$product['image'] = '';
						$product['thumb'] = $this->model_tool_image->resize('placeholder.png', $data['image_width'], $data['image_height']);
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
					$product['href'] = $this->url->link('catalog/product', 'catalog_product_id=' . $product['product_id']);

					$data['products'][] = $product;
				} else {
					$this->cart->remove($product['cart_id']);
				}
			}

			return $this->load->view('block/order/cart_product', $data);
		}
	}

	public function form($data = array()) {
		$this->load->language('order/cart');

		$this->load->controller('order/checkout/reset');

		if (!empty($this->request->post['delete'])) {
			$this->cart->remove($this->request->post['delete']);
		} else {
			if (!empty($this->request->post['option'])) {
				$products = $this->cart->getProducts();

				foreach ($this->request->post['option'] as $cart_id => $options) {
					$option = false;

					foreach ($products as $item) {
						if ($item['cart_id'] == $cart_id) {
							$option = $item['option'];

							break;
						}
					}

					if ($option !== false) {
						foreach ($options as $key => $item) {
							if ($item)
								$option[$key] = $item;
							elseif (isset($option[$key]))
								unset($option[$key]);
						}

						$this->cart->update_option($cart_id, $option);
					}
				}
			}

			if (!empty($this->request->post['quantity'])) {
				foreach ($this->request->post['quantity'] as $key => $value) {
					if ($value > 0)
						$this->cart->update($key, $value);
					else
						$this->cart->remove($key);
				}

				$this->session->data['success'] = $this->language->get('text_success_edit');
			} else {
				$this->session->data['error'] = $this->language->get('text_no_results');
			}
		}
	}
}