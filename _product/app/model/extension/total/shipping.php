<?php
namespace Model\Extension\Total;

class Shipping extends \Model {
	public function getTotal($total) {
		if (isset($this->session->data['shipping_method'])) {
			if (isset($this->session->data['total_shipping'])) {
				$cost = (float)$this->session->data['total_shipping'];
			} else {
				if (!empty($this->session->data['shipping_method']['min']) && (float)$total['total'] > (float)$this->session->data['shipping_method']['min'])
					$cost = 0;
				else
					$cost = (float)$this->session->data['shipping_method']['cost'];
			}

			if ($cost) {
				$total['totals'][] = array(
					'code'       => 'shipping',
					'title'      => $this->session->data['shipping_method']['title'],
					'value'      => $cost,
					'sort_order' => $this->config->get('total_shipping_sort_order')
				);

				$total['total'] += $cost;
			}
		}
	}

	public function editTotal($total) {
		$this->session->data['total_shipping'] = $total;
	}
}