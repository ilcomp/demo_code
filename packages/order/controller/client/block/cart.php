<?php
namespace Controller\Block;

class Cart extends \Controller {
	public function index() {
		$this->load->language('block/cart');

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
		$data['total_format'] = $this->currency->get($this->session->data['currency']);

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
}