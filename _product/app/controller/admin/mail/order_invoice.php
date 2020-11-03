<?php
namespace Controller\Mail;

class Order extends \Controller {
	public function index() {
		if (empty($this->request->get['order_id']) || empty($this->request->get['payment_method']))
			return new \Action('error/not_found');

		$this->load->model('order/order');

		$order_id = $this->request->get['order_id'];

		$order_info = $this->model_order_order->getOrder($order_id);

		if (!$order_info)
			return new \Action('error/not_found');

		$data['payment_method'] = $this->request->get['payment_method'];

		$order_products = $this->model_order_order->getOrderProducts($order_info['order_id']);

		// Load the language for any mails that might be required to be sent out
		$language = new \Language($order_info['language_code']);
		$language->load($order_info['language_code']);
		$language->load('mail/order_add');

		$data = $language->all();

		// HTML Mail
		$catalog = new \Url($order_info['store_url']);

		$data['title'] = sprintf($language->get('text_subject'), $order_info['store_name'], $order_info['order_id']);

		$data['text_greeting'] = sprintf($language->get('text_greeting'), $order_info['store_name']);

		$this->load->model('tool/image');

		if ($this->config->get('config_logo') && is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['logo'] = $this->model_tool_image->resize($this->config->get('config_logo'), 100, 50);
		} else {
			$data['logo'] = '';
		}

		$data['store_name'] = $order_info['store_name'];
		$data['store_url'] = $order_info['store_url'];
		$data['account_id'] = $order_info['account_id'];
		$data['link'] = $catalog->link('account/order/info', 'order_id=' . $order_info['order_id']);

		$data['order_id'] = $order_info['order_id'];
		$data['date_added'] = date($language->get('date_format_short'), strtotime($order_info['date_added']));
		$data['payment_method'] = $order_info['payment_method'];
		$data['shipping_method'] = $order_info['shipping_method'];
		$data['email'] = $order_info['email'];
		$data['telephone'] = $order_info['telephone'];
		$data['ip'] = $order_info['ip'];

		$order_status_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "listing_item_description WHERE listing_item_id = '" . (int)$order_info['order_status_id'] . "' AND language_id = '" . (int)$order_info['language_id'] . "'");

		if ($order_status_query->num_rows) {
			$data['order_status'] = $order_status_query->row['name'];
		} else {
			$data['order_status'] = '';
		}

		$this->load->model('tool/upload');

		// Products
		$data['products'] = array();

		foreach ($order_products as $order_product) {
			$order_product['price_format'] = $this->currency->format($order_product['price'], $order_info['currency_code'], $order_info['currency_value']);
			$order_product['total_format'] = $this->currency->format($order_product['total'], $order_info['currency_code'], $order_info['currency_value']);

			$data['products'][] = $order_product;
		}

		$this->load->model('core/custom_field');

		$custom_field_values = $this->model_order_order->getOrderCustomFields($order_info['order_id']);

		$data['custom_fields'] = array();

		$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('order_contact');

		foreach ($custom_fields as $custom_field) {
			$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

			if (isset($custom_field_values[$custom_field['custom_field_id']]) && isset($custom_field_values[$custom_field['custom_field_id']][$language_id])) {
				$value = $custom_field_values[$custom_field['custom_field_id']][$language_id];
			} elseif (isset($custom_field['setting']['default']))
				$value = $custom_field['setting']['default'];
			else
				$value = '';

			$data['custom_fields'][$custom_field['custom_field_id']]['value'] = $this->model_core_custom_field->getAsValue($value, $custom_field);
			$data['custom_fields'][$custom_field['custom_field_id']]['name'] = $custom_field['name'];
		}

		$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('order_address');

		foreach ($custom_fields as $custom_field) {
			$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

			if (isset($custom_field_values[$custom_field['custom_field_id']]) && isset($custom_field_values[$custom_field['custom_field_id']][$language_id])) {
				$value = $custom_field_values[$custom_field['custom_field_id']][$language_id];
			} elseif (isset($custom_field['setting']['default']))
				$value = $custom_field['setting']['default'];
			else
				$value = '';

			$data['custom_fields'][$custom_field['custom_field_id']]['value'] = $this->model_core_custom_field->getAsValue($value, $custom_field);
			$data['custom_fields'][$custom_field['custom_field_id']]['name'] = $custom_field['name'];
		}

		// Order Totals
		$data['totals'] = array();

		$order_totals = $this->model_order_order->getOrderTotals($order_info['order_id']);

		foreach ($order_totals as $order_total) {
			$data['totals'][] = array(
				'title' => $order_total['title'],
				'text'  => $this->currency->format($order_total['value'], $order_info['currency_code'], $order_info['currency_value']),
			);
		}

		$this->load->model('core/setting');

		$from = $this->model_core_setting->getSettingValue('mail_email', $order_info['store_id']);

		$this->load->controller('mail/mail', array(
			'from' => $from,
			'sender' => $order_info['store_name'],
			'subject' => sprintf($language->get('text_subject'), $order_info['store_name'], $order_info['order_id']),
			'view' => $this->load->view('mail/order_add', $data),
			'email' => $order_info['email']
		));
	}
}
