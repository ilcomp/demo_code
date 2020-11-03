<?php
namespace Controller\Order;

class Fast extends \Controller {
	private $error;
	private $products;

	public function index() {
		$this->load->model('catalog/product');
		$this->load->model('core/custom_field');

		$this->load->language('order/fast');

		if (isset($this->request->post['product_id'])) {
			$this->request->post['product'] = array(
				'product_id' => $this->request->post['product_id'],
				'option' => !empty($this->request->post['option']) ? (array)$this->request->post['option'] : array(),
				'quantity' => !empty($this->request->post['quantity']) ? $this->request->post['quantity'] : 1
			);
		} else {
			if (empty($this->request->post['product']))
				return;

			$product_info = $this->model_catalog_product->getProduct($this->request->post['product']['product_id']);

			if (!isset($product_info))
				return;

			if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {
				$this->load->model('order/order');
				$this->load->model('order/order_history');

				$order_data = $this->request->post;
				$order_data['marketing_id'] = 1;
				$order_data['total'] = 0;
				$order_data['products'] = $this->products;

				foreach ($order_data['products'] as $product) {
					$order_data['total'] += $product['price'] * $product['quantity'];
				}

				$order_id = $this->model_order_order->addOrder($order_data);

				$data['success'] = $this->language->get('text_success');

				$order_id = $this->model_order_order_history->addOrderHistory($order_id, $this->config->get('order_status_default_id'));
			}
		}

		$data['error'] = $this->error;

		if (!isset($data['success']))
			$data['success'] = '';

		$data['action'] = $this->url->link('order/fast');

		$data['product'] = urldecode(http_build_query(array('product' => $this->request->post['product'])));

		if (isset($this->request->post['telephone'])) {
			$data['telephone'] = $this->request->post['telephone'];
		} else {
			$data['telephone'] = '';
		}

		if (isset($this->request->post['custom_field'])) {
			$custom_field_values = $this->request->post['custom_field'];
		} else {
			$custom_field_values = array();
		}

		$data['custom_fields'] = $this->model_core_custom_field->getAsField($custom_field_values, 'order_contact');

		$this->response->setOutput($this->load->view('order/fast', $data));
	}


	private function validate() {
		if (empty($this->request->post['telephone']) || utf8_strlen(preg_replace('/\D/', '', $this->request->post['telephone'])) < 11 || utf8_strlen($this->request->post['telephone']) > 32)
			$this->error['telephone'] = $this->language->get('error_telephone');

		$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('order_contact');

		foreach ($custom_fields as $custom_field) {
			if (isset($this->request->post['custom_field'][$custom_field['custom_field_id']])) {
				$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;
				$value = isset($this->request->post['custom_field'][$custom_field['custom_field_id']][$language_id]) ? $this->request->post['custom_field'][$custom_field['custom_field_id']][$language_id] : '';

				if ($custom_field['required'] && !$value) {
					$this->error['custom_field' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
				} elseif ($value && !empty($custom_field['setting']['validation']) && filter_var($value, FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => '/' . html_entity_decode($custom_field['setting']['validation'], ENT_QUOTES, 'UTF-8') . '/')))) {
					$this->error['custom_field' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
				}
			}
		}

		if (empty($this->error)) {
			$this->load->model('core/api');

			$api_info = $this->model_core_api->getApiByUsername('FastOrder');

			if (!$api_info)
				$api_info = $this->model_core_api->getApiByUsername('Default');

			if ($api_info) {
				// Api
				$session = new \Session($this->config->get('session_engine'), $this->registry);

				// Queries
				$catalog = new \GuzzleHttp\Client([
					'defaults' => [
						'headers'  => [
							'Content-Type' => 'multipart/form-data',
						],
						'connect_timeout' => 10
					],
				]);

				$session->start();

				$this->model_core_api->deleteApiSessionBySessonId($session->getId());

				$this->model_core_api->addApiSession($api_info['api_id'], $session->getId(), $this->request->server['REMOTE_ADDR']);

				$session->data['api_id'] = $api_info['api_id'];
				$session->data['currency'] = $this->session->data['currency'];

				$api_token = $session->getId();

				$session->close();

				$api_url = new \Url(HTTP_APPLICATION_API);

				$link = $api_url->link('order/cart/create', 'api_token=' . $api_token . '&language=' . $this->config->get('config_language'));

				try {
					$response = $catalog->post(str_replace('&amp;', '&', $link), array(
						'body' => array(
							'product' => array($this->request->post['product'])
						)
					));

					$result = $response->json();
				} catch(Exception $e) {
					$this->log->write($e->getMessage());

					return 0;
				}

				if (!empty($result['success'])) {
					$link = $api_url->link('order/cart/get_cart', 'api_token=' . $api_token . '&language=' . $this->config->get('config_language'));

					try {
						$response = $catalog->get(str_replace('&amp;', '&', $link));

						$result = $response->json();

						if (!empty($result['products'])) {
							$this->load->model('catalog/product');
							$this->load->model('core/custom_field');

							$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('catalog_product');

							foreach ($result['products'] as &$product) {
								foreach ($custom_fields as $custom_field) {
									if ($custom_field['code'] == 'composition_products') {
										$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

										$value = $this->model_catalog_product->getProductCustomField($product['product_id'], $custom_field['custom_field_id'], $language_id);

										$product['composition_products'] = $this->model_core_custom_field->getAsValue($value, $custom_field);
									}
								}
							}
							unset($product);

							$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('catalog_product_attribute');

							foreach ($result['products'] as &$product) {
								foreach ($custom_fields as $custom_field) {
									if ($custom_field['code'] == 'sku') {
										$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

										$value = $this->model_catalog_product->getProductCustomField($product['product_id'], $custom_field['custom_field_id'], $language_id);

										$product['sku'] = $this->model_core_custom_field->getAsValue($value, $custom_field);
									}
								}
							}
							unset($product);

							$this->products = $result['products'];
						}
					} catch(Exception $e) {
						$this->log->write($e->getMessage());

						return 0;
					}
				}

				if (!empty($result['error']))
					$this->error['api'] = $result['error'];

				if (empty($this->products))
					$this->error['api'] = $result;
			} else {
				$this->error['api'] = 'Api username not found';
			}
		}

		return !$this->error && $this->products;
	}
}