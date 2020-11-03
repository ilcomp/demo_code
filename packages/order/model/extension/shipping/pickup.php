<?php
namespace Model\Extension\Shipping;

class Pickup extends \Model {
	function getQuote($address) {
		$this->load->language('extension/shipping/pickup', 'temp');

		$language = $this->language->get('temp');

		if (!isset($address['country_id']))
			$address['country_id'] = 0;
		if (!isset($address['zone_id']))
			$address['zone_id'] = 0;

		$this->load->model('localisation/location');

		$status = true;

		if ((int)$this->config->get('shipping_pickup_geo_zone_id')) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('shipping_pickup_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

			if (!$query->num_rows)
				$status = false;
		}

		if ((int)$this->config->get('shipping_pickup_geo_zone_id_exclude')) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('shipping_pickup_geo_zone_id_exclude') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

			if ($query->num_rows)
				$status = false;
		}

		$method_data = array();

		if ($status) {
			$quote_data = array();

			$locations = $this->model_localisation_location->getLocations(array('filter_issue' => 1));

			if ($locations) {
				foreach ($locations as $location) {
					$quote_data[$location['location_id']] = array(
						'code'         => 'pickup.' . $location['location_id'],
						'title'        => $location['name'],
						'description'  => $location['comment'],
						'address'      => $location['address'],
						'open'         => $location['open'],
						'cost'         => 0,
						'cost_format'  => ''
					);
				}
			} else {
				$quote_data['pickup'] = array(
					'code'         => 'pickup.pickup',
					'title'        => $language->get('text_title'),
					'description'  => $language->get('text_description'),
					'cost'         => 0,
					'cost_format'  => ''
				);
			}

			$method_data = array(
				'code'       => 'pickup',
				'title'      => $language->get('text_title'),
				'quote'      => $quote_data,
				'sort_order' => $this->config->get('shipping_pickup_sort_order'),
				'error'      => false
			);
		}

		return $method_data;
	}

	function validate($error = array()) {
		$this->load->model('core/custom_field');

		$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('order_address');

		foreach ($custom_fields as $custom_field) {
			if ($custom_field['code'] == 'country') {
				$this->session->data['order']['custom_field'][$custom_field['custom_field_id']][-1] = $this->config->get('config_country_id');
			} elseif ($custom_field['code'] == 'zone') {
				$this->session->data['order']['custom_field'][$custom_field['custom_field_id']][-1] = $this->config->get('config_zone_id');
			} elseif ($custom_field['code'] == 'city') {
				$this->session->data['order']['custom_field'][$custom_field['custom_field_id']][-1] = '';
			} elseif ($custom_field['code'] == 'address') {
				$this->session->data['order']['custom_field'][$custom_field['custom_field_id']][-1] = $this->session->data['shipping_method']['description'];
			}
		}

		return $error;
	}
}