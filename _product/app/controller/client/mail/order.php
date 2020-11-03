<?php
namespace Controller\Mail;

class Order extends \Controller {
	public function index(&$route, &$args) {
		$this->load->model('order/order');
		$this->load->model('order/order_history');

		if (isset($args[0])) {
			$order_id = $args[0];
		} else {
			$order_id = 0;
		}

		if (isset($args[1])) {
			$order_status_id = $args[1];
		} else {
			$order_status_id = 0;
		}

		if (isset($args[2])) {
			$comment = $args[2];
		} else {
			$comment = '';
		}

		if (isset($args[3])) {
			$notify = $args[3];
		} else {
			$notify = '';
		}

		// We need to grab the old order status ID
		$order_info = $this->model_order_order->getOrder($order_id);

		if ($order_info) {
			// If order status is 0 then becomes greater than 0 send main html email
			if (!$order_info['order_status_id'] && $order_status_id) {
				$this->add($order_info, $order_status_id, $comment, $notify);
			}

			// If order status is not 0 then send update text email
			if ($order_info['order_status_id'] && $order_status_id && $notify) {
				$this->edit($order_info, $order_status_id, $comment, $notify);
			}
		}
	}

	public function add($order_info, $order_status_id, $comment, $notify) {
		// Check for any downloadable products
		$download_status = false;

		// Load the language for any mails that might be required to be sent out
		$language = new \Language($order_info['language_code']);
		$language->load($order_info['language_code']);
		$language->load('mail/order_add');

		$data = $language->all();

		// HTML Mail
		$data = array_merge($data, $order_info);

		$catalog = new \Url($order_info['store_url']);

		$data['title'] = sprintf($language->get('text_subject'), $order_info['store_name'], $order_info['order_id']);

		$data['text_greeting'] = sprintf($language->get('text_greeting'), $order_info['store_name']);

		$this->load->model('tool/image');

		if ($this->config->get('config_logo') && is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['logo'] = HTTP_APPLICATION . $this->model_tool_image->resize($this->config->get('config_logo'), 100, 50);
		} else {
			$data['logo'] = '';
		}

		$data['link'] = $catalog->link('account/order/info', 'order_id=' . $order_info['order_id']);

		if ($download_status) {
			$data['download'] = $catalog->link('account/download');
		} else {
			$data['download'] = '';
		}

		$data['date_added'] = date($language->get('date_format_short'), strtotime($order_info['date_added']));

		$order_status_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "listing_item_description WHERE listing_item_id = '" . (int)$order_status_id . "' AND language_id = '" . (int)$order_info['language_id'] . "'");

		if ($order_status_query->num_rows) {
			$data['order_status'] = $order_status_query->row['name'];
		} else {
			$data['order_status'] = '';
		}

		$this->load->model('tool/upload');

		// Products
		$data['products'] = $this->model_order_order->getOrderProducts($order_info['order_id']);

		foreach ($data['products'] as &$order_product) {
			$order_product['price_format'] = $this->currency->format($order_product['price'], $order_info['currency_code'], $order_info['currency_value']);
			$order_product['total_format'] = $this->currency->format($order_product['total'], $order_info['currency_code'], $order_info['currency_value']);
		}
		unset($order_product);

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
				'total' => $order_total['value'],
				'total_format' => $this->currency->format($order_total['value'], $order_info['currency_code'], $order_info['currency_value'])
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

	public function edit($order_info, $order_status_id, $comment) {
		$language = new \Language($order_info['language_code']);
		$language->load($order_info['language_code']);
		$language->load('mail/order_edit');

		$data = $language->all();

		$data = array_merge($data, $order_info);

		$data['date_added'] = date($language->get('date_format_short'), strtotime($order_info['date_added']));
		$data['store_url'] = HTTP_SERVER;
		$data['store'] = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');

		$order_status_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "listing_item_description WHERE listing_item_id = '" . (int)$order_status_id . "' AND language_id = '" . (int)$order_info['language_id'] . "'");

		if ($order_status_query->num_rows) {
			$data['order_status'] = $order_status_query->row['name'];
		} else {
			$data['order_status'] = '';
		}

		if ($order_info['account_id']) {
			$data['link'] = $order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_info['order_id'];
		} else {
			$data['link'] = '';
		}

		$data['comment'] = strip_tags($comment);

		$this->load->model('core/setting');

		$from = $this->model_core_setting->getSettingValue('mail_email', $order_info['store_id']);

		$this->load->controller('mail/mail', array(
			'from' => $from,
			'sender' => $order_info['store_name'],
			'subject' => sprintf($language->get('text_subject'), $order_info['store_name'], $order_info['order_id']),
			'view' => $this->load->view('mail/order_edit', $data),
			'email' => $order_info['email']
		));
	}

	// Admin Alert Mail
	public function alert(&$route, &$args) {
		$this->load->model('order/order');
		$this->load->model('order/order_history');

		if (isset($args[0])) {
			$order_id = $args[0];
		} else {
			$order_id = 0;
		}

		if (isset($args[1])) {
			$order_status_id = $args[1];
		} else {
			$order_status_id = 0;
		}

		if (isset($args[2])) {
			$comment = $args[2];
		} else {
			$comment = '';
		}

		if (isset($args[3])) {
			$notify = $args[3];
		} else {
			$notify = '';
		}

		$order_info = $this->model_order_order->getOrder($order_id);

		if ($order_info && !$order_info['order_status_id'] && $order_status_id && in_array('order', (array)$this->config->get('mail_alert'))) {
			$this->load->language('mail/order_alert');

			$data = $order_info;

			$data['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));

			$order_status_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "listing_item_description WHERE listing_item_id = '" . (int)$order_status_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

			if ($order_status_query->num_rows) {
				$data['order_status'] = $order_status_query->row['name'];
			} else {
				$data['order_status'] = '';
			}

			$data['store_url'] = HTTP_SERVER;
			$data['store'] = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');

			$this->load->model('tool/image');

			if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
				$data['logo'] = HTTP_APPLICATION . $this->model_tool_image->resize($this->config->get('config_logo'), 100, 50);
			} else {
				$data['logo'] = '';
			}

			$this->load->model('tool/upload');

			$data['products'] = array();

			$order_products = $this->model_order_order->getOrderProducts($order_id);

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

			$data['totals'] = array();

			$order_totals = $this->model_order_order->getOrderTotals($order_id);

			foreach ($order_totals as $order_total) {
				$data['totals'][] = array(
					'title' => $order_total['title'],
					'total' => $order_total['value'],
					'total_format' => $this->currency->format($order_total['value'], $order_info['currency_code'], $order_info['currency_value'])
				);
			}

			$data['additionallys'] = $this->model_order_order->getOrderAdditionallys($order_id);

			$emails = explode(',', $this->config->get('mail_alert_email'));
			array_unshift($emails, $this->config->get('mail_email'));

			$this->load->controller('mail/mail', array(
				'sender' => $order_info['store_name'],
				'subject' => sprintf($this->language->get('text_subject'), $this->config->get('config_name'), $order_info['order_id']),
				'view' => $this->load->view('mail/order_alert', $data),
				'email' => $emails
			));
		}
	}
}