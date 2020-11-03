<?php
namespace Controller\Block\Order;

class TotalProduct extends \Controller {
	public function index($setting = array()) {
		if ($this->config->get('block_order_total_product')) {
			return $this->config->get('block_order_total_product');
		}

		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		$data['image_width'] = $this->config->get('cart_image_list_width');
		$data['image_height'] = $this->config->get('cart_image_list_height');

		$data['products'] = array();

		$products = $this->cart->getProducts();

		foreach ($products as $product) {
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
		}

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

		foreach ($results as $result) {
			if ($this->config->get('total_' . $result['code'] . '_status')) {
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

		$this->session->data['order']['totals'] = $totals;
		$this->session->data['order']['total'] = $total_data['total'];

		$data['totals'] = array();

		foreach ($totals as $total) {
			$total['text'] = $this->currency->format($total['value'], $this->session->data['currency']);

			$data['totals'][] = $total;
		}

		return $this->load->view('block/order/total_product', $data);
	}

	public function form() {
		$this->config->set('block_order_total_product', $this->index());
	}
}
