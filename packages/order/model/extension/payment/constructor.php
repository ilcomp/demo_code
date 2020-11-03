<?php
namespace Model\Extension\Payment;

class Constructor extends \Model {
	public function getMethod($address, $total) {
		$this->load->language('extension/payment/constructor');

		if (!isset($address['country_id']))
			$address['country_id'] = 0;
		if (!isset($address['zone_id']))
			$address['zone_id'] = 0;

		$method_data = array();

		$methods = $this->config->get('payment_constructor_methods');

		foreach ($methods as $key => $method) {
			if (!$method['geo_zone_id']) {
				$status = true;
			} else {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$method['geo_zone_id'] . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

				$status = $query->num_rows ? true : false;
			}

			if (!empty($method['shipping'])) {
				$shipping = explode(',', $method['shipping']);

				$status = !empty($this->session->data['shipping_method']) && in_array($this->session->data['shipping_method']['code'], $shipping);
			}

			if (!empty($method['shipping_exclude'])) {
				$shipping = explode(',', $method['shipping_exclude']);

				$status = !empty($this->session->data['shipping_method']) && !in_array($this->session->data['shipping_method']['code'], $shipping);
			}

			if ($this->config->get('payment_constructor_sort_order'))
				$key = $key + $this->config->get('payment_constructor_sort_order');

			if ($status) {
				$method_data['constructor.' . $method['code']] = array(
					'code'        => 'constructor.' . $method['code'],
					'title'       => $method['title'][$this->config->get('config_language_id')],
					'description' => $method['description'][$this->config->get('config_language_id')],
					'image'       => $method['image'],
					'terms'       => '',
					'sort_order'  => $key
				);
			}
		}

		return $method_data;
	}
}
