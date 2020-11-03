<?php
namespace Model\Extension\Shipping;

class Constructor extends \Model {
	function getQuote($address) {
		$this->load->language('extension/shipping/constructor', 'temp');

		$language = $this->language->get('temp');

		if (!isset($address['country_id']))
			$address['country_id'] = 0;
		if (!isset($address['zone_id']))
			$address['zone_id'] = 0;

		$quote_data = array();

		$methods = $this->config->get('shipping_constructor_methods');

		foreach ($methods as $method) {
			$status = true;

			if (!empty($method['geo_zone_id'])) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$method['geo_zone_id'] . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

				if (!$query->num_rows)
					$status = false;
			}

			if (!empty($method['geo_zone_id_exclude'])) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$method['geo_zone_id_exclude'] . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

				if ($query->num_rows)
					$status = false;
			}

			if ($status) {
				$price = $this->currency->convert($method['price'], null, $this->session->data['currency']);

				$quote_data[$method['code']] = array(
					'code'         => 'constructor.' . $method['code'],
					'title'        => $method['title'][$this->config->get('config_language_id')],
					'description'  => $method['description'][$this->config->get('config_language_id')],
					'image'        => $method['image'],
					'cost'         => $price,
					'min'          => isset($method['free']) ? (float)$method['free'] : 0,
					'cost_format'  => $this->currency->format($price, $this->session->data['currency'])
				);
			}
		}

		if ($quote_data) {
			return array(
				'code'       => 'constructor',
				'title'      => $language->get('text_title'),
				'quote'      => $quote_data,
				'sort_order' => $this->config->get('shipping_constructor_sort_order'),
				'error'      => false
			);
		}

		return array();
	}
}