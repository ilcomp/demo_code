<?php
namespace Model\Extension\Payment;

class Invoice extends \Model {
	public function getMethod($address, $total) {
		$this->load->language('extension/payment/invoice');

		$method_data = array();

		if (!$this->config->get('payment_invoice_geo_zone_id')) {
			$status = true;
		} else {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payment_invoice_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

			$status = $query->num_rows ? true : false;
		}

		if ($status) {
			$method_data['invoice.invoice'] = array(
				'code'        => 'invoice.invoice',
				'title'       => $this->language->get('text_title'),
				'description' => $this->language->get('text_description'),
				'image'       => $this->config->get('payment_invoice_image'),
				'terms'       => '',
				'sort_order'  => $key
			);
		}

		return $method_data;
	}
}
