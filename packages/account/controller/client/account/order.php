<?php
namespace Controller\Account;

class Order extends \Controller {
	public function index($data = array()) {
		if (!$this->account->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/order');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 401 Unauthorized');

			return new \Action('account/login');
		}

		$this->load->language('account/account');
		$this->load->language('account/order');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account')
		);
		
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('account/order' . $url)
		);

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		if ($this->config->get('config_limit')) {
			$limit = $this->config->get('config_limit');
		} else {
			$limit = 20;
		}

		$data['orders'] = array();

		$this->load->model('order/order');
		$this->load->model('localisation/listing');

		$filter_data = array(
			'sort'   => 'o.order_id',
			'order'  => 'DESC',
			'start'  => ($page - 1) * $limit,
			'limit'  => $limit
		);

		$order_total = $this->model_order_order->getTotalOrders($filter_data);

		$data['orders'] = $this->model_order_order->getOrders($filter_data);

		$total = 0;

		foreach ($data['orders'] as &$result) {
			// $result['order_status'] = $this->model_localisation_listing->getListingItem($result['order_status_id']);
			$result['date_added'] = date($this->language->get('date_format_short'), strtotime($result['date_added']));
			$result['total_format'] = $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']);
			$result['view'] = $this->url->link('account/order/info' . '&order_id=' . $result['order_id']);

			$result['products'] = $this->model_order_order->getOrderProducts($result['order_id']);

			$result['quantity'] = 0;

			foreach ($result['products'] as $product) {
				$result['quantity'] += $product['quantity'];
			}

			$total += $result['total'];
		}
		unset($result);

		$data['total'] = $this->currency->format($total, $this->session->data['currency']);

		$data['quantity'] = $order_total;

		$url = $this->request->get;
		unset($url['route']);
		unset($url['_route_']);
		$url['code'] = 'account_order';
		$url['page'] = '{page}';

		$data['pagination'] = $this->load->controller('block/pagination', array(
			'total' => $order_total,
			'page'  => $page,
			'limit' => $limit,
			'url'   => $this->url->link('account/order', $url)
		));

		$data['results'] = $this->load->controller('block/pagination/result', array(
			'total' => $order_total,
			'page'  => $page,
			'limit' => $limit
		));

		if ($this->request->server['HTTP_ACCEPT'] == 'application/json') {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput($data);
		} else {
			$data['content'] = $this->load->view('account/order_list', $data);

			if (!empty($data['return_content'])) {
				return $data['content'];
			} elseif ($this->request->server['HTTP_ACCEPT'] == 'text/html') {
				$this->response->setOutput($data['content']);
			} else {
				$this->document->setTitle($this->language->get('heading_title'));

				$this->response->setOutput($this->load->controller('common/template', $data));
			}
		}
	}

	public function info() {
		$this->load->language('account/account');
		$this->load->language('account/order');

		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}

		if (!$this->account->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/order/info' . '&order_id=' . $order_id);

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 401 Unauthorized');

			return new \Action('account/login');
		}

		$this->load->model('order/order');
		$this->load->model('order/order_history');
		$this->load->model('localisation/listing');

		$order_info = $this->model_order_order->getOrder($order_id);

		if ($order_info) {
			$data = $order_info;

			$this->document->setTitle($this->language->get('text_order'));

			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home')
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_account'),
				'href' => $this->url->link('account/account')
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('account/order')
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_order_id'),
				'href' => $this->url->link('account/order/info' . '&order_id=' . $order_info['order_id'])
			);

			if (isset($this->session->data['error'])) {
				$data['error_warning'] = $this->session->data['error'];

				unset($this->session->data['error']);
			} else {
				$data['error_warning'] = '';
			}

			if (isset($this->session->data['success'])) {
				$data['success'] = $this->session->data['success'];

				unset($this->session->data['success']);
			} else {
				$data['success'] = '';
			}

			$data['order_id'] = (int)$order_info['order_id'];
			$data['date_added'] = date($this->language->get('datetime_format'), strtotime($order_info['date_added']));
			$data['order_status'] = $this->model_localisation_listing->getListingItem($order_info['order_status_id']);
			$data['total_format'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value']);

			$this->load->model('catalog/product');
			$this->load->model('tool/upload');
			$this->load->model('tool/image');

			$image_product_width = $this->config->get('cart_image_list_width') ? $this->config->get('cart_image_list_width') : 100;
			$image_product_height = $this->config->get('cart_image_list_height') ? $this->config->get('cart_image_list_height') : 100;

			// Products
			$data['products'] = array();

			$products = $this->model_order_order->getOrderProducts($order_info['order_id']);

			$data['quantity'] = 0;
			$data['options'] = array();

			foreach ($products as $product) {
				$product_info = $this->model_catalog_product->getProduct($product['product_id']);
				$product['option_data'] = $this->model_order_order->getOrderOptions($order_info['order_id'], $product['product_id']);

				foreach ($product['option_data'] as $option) {
					if (!isset($data['options'][$option['option_code']]))
						$data['options'][$option['option_code']] = $option;
				}

				if ($product_info) {
					$product['reorder'] = $this->url->link('account/order/reorder' . '&order_id=' . $order_id . '&order_product_id=' . $product['order_product_id']);
				} else {
					$product['reorder'] = '';
				}

				$image = $this->model_catalog_product->getProductImageMain($product['product_id']);

				if ($image) {
					$product['image'] = $image['image'];
					$product['thumb'] = $this->model_tool_image->resize($image['image'], $image_product_width, $image_product_height);
				} else {
					$product['image'] = '';
					$product['thumb'] = '';
				}

				$product['price_format'] = $this->currency->format($product['price'], $order_info['currency_code'], $order_info['currency_value']);
				$product['total_format'] = $this->currency->format($product['total'], $order_info['currency_code'], $order_info['currency_value']);
				$product['return'] = $this->url->link('account/return/add' . '&order_id=' . $order_info['order_id'] . '&product_id=' . $product['product_id']);

				$data['quantity'] += $product['quantity'];

				$data['products'][] = $product;
			}

			// Totals
			$data['totals'] = $this->model_order_order->getOrderTotals($order_info['order_id']);

			foreach ($data['totals'] as &$total) {
				$total['text'] = $this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value']);
			}
			unset($total);

			// History
			$data['histories'] = array();

			$results = $this->model_order_order_history->getOrderHistories($order_info['order_id']);

			foreach ($results as $result) {
				$data['histories'][] = array(
					'date_added' => date($this->language->get('datetime_format'), strtotime($result['date_added'])),
					'status'     => $result['status'],
					'comment'    => $result['notify'] ? nl2br($result['comment']) : ''
				);
			}

			// Custom Fields
			$this->load->model('core/custom_field');

			$custom_field_values = $this->model_order_order->getOrderCustomFields($order_info['order_id']);

			$data['custom_fields_account'] = array();

			$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('order_contact');

			foreach ($custom_fields as $custom_field) {
				$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

				if (isset($custom_field_values[$custom_field['custom_field_id']]) && isset($custom_field_values[$custom_field['custom_field_id']][$language_id])) {
					$value = $custom_field_values[$custom_field['custom_field_id']][$language_id];
				} elseif (isset($custom_field['setting']['default']))
					$value = $custom_field['setting']['default'];
				else
					$value = '';

				$data['custom_fields_account'][$custom_field['custom_field_id']]['value'] = $this->model_core_custom_field->getAsValue($value, $custom_field);
				$data['custom_fields_account'][$custom_field['custom_field_id']]['name'] = $custom_field['name'];
			}

			$data['custom_fields_address'] = array();

			$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('order_address');

			foreach ($custom_fields as $custom_field) {
				$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

				if (isset($custom_field_values[$custom_field['custom_field_id']]) && isset($custom_field_values[$custom_field['custom_field_id']][$language_id])) {
					$value = $custom_field_values[$custom_field['custom_field_id']][$language_id];
				} elseif (isset($custom_field['setting']['default']))
					$value = $custom_field['setting']['default'];
				else
					$value = '';

				$data['custom_fields_address'][$custom_field['custom_field_id']]['value'] = $this->model_core_custom_field->getAsValue($value, $custom_field);
				$data['custom_fields_address'][$custom_field['custom_field_id']]['name'] = $custom_field['name'];
			}

			$data['continue'] = $this->url->link('account/account');
			$data['logout'] = $this->url->link('account/logout');
			$data['account'] = $this->url->link('account/account');
			$data['order'] = $this->url->link('account/order');

			$data['content'] = $this->load->view('account/order_info', $data);

			$this->response->setOutput($this->load->controller('common/template', $data));
		} else {
			return new Action('error/not_found');
		}
	}

	public function reorder() {
		$this->load->language('account/order');

		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}

		if (!$this->account->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/order');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 401 Unauthorized');

			return new \Action('account/login');
		}

		$this->load->model('account/order');

		$order_info = $this->model_order_order->getOrder($order_id);

		if ($order_info) {
			if (isset($this->request->get['order_product_id'])) {
				$order_product_id = $this->request->get['order_product_id'];
			} else {
				$order_product_id = 0;
			}

			$order_product_info = $this->model_order_order->getOrderProduct($order_id, $order_product_id);

			if ($order_product_info) {
				$this->load->model('catalog/product');

				$product_info = $this->model_catalog_product->getProduct($order_product_info['product_id']);

				if ($product_info) {
					$option_data = array();

					$order_options = $this->model_order_order->getOrderOptions($order_product_info['order_id'], $order_product_id);

					$this->cart->add($order_product_info['product_id'], $order_product_info['quantity'], $option_data);

					$this->session->data['success'] = sprintf($this->language->get('text_success'), $this->url->link('product/product' . '&product_id=' . $product_info['product_id']), $product_info['name'], $this->url->link('checkout/cart'));

					unset($this->session->data['shipping_method']);
					unset($this->session->data['shipping_methods']);
					unset($this->session->data['payment_method']);
					unset($this->session->data['payment_methods']);
				} else {
					$this->session->data['error'] = sprintf($this->language->get('error_reorder'), $order_product_info['name']);
				}
			}
		}

		$this->response->redirect($this->url->link('account/order/info' . '&order_id=' . $order_id));
	}
}