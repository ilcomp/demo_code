<?php
namespace Controller\Block\Order;

class CartTotal extends \Controller {
	public function index() {
		if ($this->config->get('block_order_cart_total')) {
			return $this->config->get('block_order_cart_total');
		}

		$form = $this->config->get('form_cart');

		if (isset($form['block']['cart_total'])) {
			$data = $form['block']['cart_total'];
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

		$data['modules'] = array();

		foreach ($results as $result) {
			if ($result['code'] != 'shipping' && $this->config->get('total_' . $result['code'] . '_status')) {
				$this->load->model('extension/total/' . $result['code']);

				// We have to put the totals in an array so that they pass by reference.
				$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);

				$module = $this->load->controller('extension/total/' . $result['code']);

				if ($module)
					$data['modules'][] = $module;
			}
		}

		$sort_order = array();

		foreach ($totals as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC, $totals);

		$data['totals'] = array();

		foreach ($totals as $total) {
			$total['text'] = $this->currency->format($total['value'], $this->session->data['currency']);
			$data['totals'][] = $total;
		}

		return $this->load->view('block/order/cart_total', $data);
	}

	public function form() {
		$this->config->set('block_order_cart_total', $this->index());
	}
}
