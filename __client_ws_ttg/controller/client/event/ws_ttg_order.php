<?php
namespace Controller\Event;

class WSTTGOrder extends \Controller {
	public function index() {
		$this->event->register('controller/block/order/address/form/before', new \Action('event/ws_ttg_order/address_form'), 0);
		$this->event->register('controller/order/cart/add/before', new \Action('event/ws_ttg_order/cart_add'), 0);

		$this->event->register('model/registry/cart/getProducts', new \Action('event/ws_ttg_order/cart'), 0);
		$this->event->register('model/extension/shipping/*/getQuote/before', new \Action('event/ws_ttg_order/getQuote'), 0);

		$this->event->register('model/retailcrm/order/getOrder/create', new \Action('event/ws_ttg_order/getOrder_create'), 0);

		$this->event->register('view/block/order/shipping_method/before', new \Action('event/ws_ttg_order/shipping_method'), 0);
		$this->event->register('view/block/order/payment_method/before', new \Action('event/ws_ttg_order/payment_method'), 0);
		$this->event->register('view/mail/order_add/before', new \Action('event/ws_ttg_order/mail'), 0);
		$this->event->register('view/mail/order_alert//before', new \Action('event/ws_ttg_order/mail'), 0);
	}

	public function address_form($route, &$args) {
		$this->load->model('core/custom_field');

		if (isset($this->request->post['shipping_method']) && $this->request->post['shipping_method'] == 'constructor.samovyvoz') {
			$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('order_address');
			$methods = $this->config->get('shipping_constructor_methods');

			foreach ($custom_fields as $custom_field) {
				if ($custom_field['code'] == 'address') {
					$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

					foreach ($methods as $method) {
						if ($method['code'] == 'samovyvoz') {
							$this->request->post['custom_field'][$custom_field['custom_field_id']][$language_id] = $method['description'][$this->config->get('config_language_id')];
						}
					}
				}
			}
		}
	}

	public function cart_add($route, &$args) {
		if (isset($this->request->post['product_id']) && $this->request->post['product_id'] == $this->config->get('theme_client_ws_ttg_construstor_product_id')) {
			$this->load->model('catalog/product');
			$this->load->model('core/custom_field');

			$attributes = $this->model_core_custom_field->getCustomFieldsByLocation('catalog_product_attribute');

			$options = isset($this->request->post['option']) ? $this->request->post['option'] : array();

			$exlude = (array)$this->config->get('theme_client_ws_ttg_construstor_exlude');
			$exlude[] = (int)$this->config->get('theme_client_ws_ttg_construstor_product_id');

			$count = 0;
			$missing = false;

			$composition = isset($options['composition']) ? (array)$options['composition'] : array();

			foreach ($composition as $product_id) {
				if (in_array((int)$product_id, $exlude))
					continue;

				if (!$missing) {
					foreach ($attributes as $custom_field) {
						if ($custom_field['code'] == 'missing' && empty($product['missing'])) {
							$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

							$value = $this->model_catalog_product->getProductCustomField($product_id, $custom_field['custom_field_id'], $language_id);

							$missing = $this->model_core_custom_field->getAsValue($value, $custom_field);
						}
					}
				}

				$composition_product = $this->model_catalog_product->getProduct($product_id);

				if ($composition_product) {
					$count++;
				}
			}

			if ($missing) {
				$args[0]['error'] = $this->language->get('error_constructor_composition_missing');

				unset($this->request->post['product_id']);
			}

			if ($count < max(1, $this->config->get('theme_client_ws_ttg_construstor_min'))) {
				$args[0]['error'] = sprintf($this->language->get('error_constructor_composition_min'), max(1, $this->config->get('theme_client_ws_ttg_construstor_min')));

				unset($this->request->post['product_id']);
			}

			if ($this->config->get('theme_client_ws_ttg_construstor_max') > 0 && $count > $this->config->get('theme_client_ws_ttg_construstor_max')) {
				$args[0]['error'] = sprintf($this->language->get('error_constructor_composition_max'), $this->config->get('theme_client_ws_ttg_construstor_max'));

				unset($this->request->post['product_id']);
			}
		}
	}

	public function cart(&$data) {
		$this->load->model('catalog/product');
		$this->load->model('core/custom_field');

		$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('catalog_product');
		$attributes = $this->model_core_custom_field->getCustomFieldsByLocation('catalog_product_attribute');

		foreach ($data as &$product) {
			foreach ($custom_fields as $custom_field) {
				if ($custom_field['code'] == 'composition_products') {
					$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

					$value = $this->model_catalog_product->getProductCustomField($product['product_id'], $custom_field['custom_field_id'], $language_id);

					$product['composition_products'] = $this->model_core_custom_field->getAsValue($value, $custom_field);
				}
			}

			foreach ($attributes as $custom_field) {
				if ($custom_field['code'] == 'sku') {
					$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

					$value = $this->model_catalog_product->getProductCustomField($product['product_id'], $custom_field['custom_field_id'], $language_id);

					$product['sku'] = $this->model_core_custom_field->getAsValue($value, $custom_field);
				} elseif ($custom_field['code'] == 'missing' && empty($product['missing'])) {
					$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

					$value = $this->model_catalog_product->getProductCustomField($product['product_id'], $custom_field['custom_field_id'], $language_id);

					$product['missing'] = $this->model_core_custom_field->getAsValue($value, $custom_field);

					if ($product['missing'])
						$this->session->data['error'] = $this->language->get('error_missing');
				}
			}

			if (!isset($product['composition_products'])) {
				$product['composition_products'] = array();
			} else {
				$product['composition_products'] = (array)$product['composition_products'];
			}

			if ($product['product_id'] == $this->config->get('theme_client_ws_ttg_construstor_product_id')) {
				$exlude = (array)$this->config->get('theme_client_ws_ttg_construstor_exlude');
				$exlude[] = (int)$this->config->get('theme_client_ws_ttg_construstor_product_id');

				$price = 0;
				$special = 0;
				$missing = false;

				$composition = isset($product['option']) && isset($product['option']['composition']) ? (array)$product['option']['composition'] : array();

				foreach ($composition as $product_id) {
					if (in_array((int)$product_id, $exlude))
						continue;

					if (!$missing) {
						foreach ($attributes as $custom_field) {
							if ($custom_field['code'] == 'missing' && empty($product['missing'])) {
								$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

								$value = $this->model_catalog_product->getProductCustomField($product_id, $custom_field['custom_field_id'], $language_id);

								$missing = $this->model_core_custom_field->getAsValue($value, $custom_field);
							}
						}
					}

					$composition_product = $this->model_catalog_product->getProduct($product_id);

					if ($composition_product) {
						if (!empty($composition_product['price'])) {
							$price += $this->currency->convert($composition_product['price'], $composition_product['currency_id'], $this->session->data['currency']);
						}

						$product['composition_products'][] = $composition_product;
					}
				}

				$count = count($product['composition_products']);

				if ($count > 4) {
					$special = 0.2;
				} elseif ($count > 3) {
					$special = 0.15;
				} elseif ($count > 2) {
					$special = 0.1;
				}

				if ($missing) {
					if (empty($this->session->data['error']))
						$this->session->data['error'] = $this->language->get('error_constructor_composition');

					$product['error'] = $this->language->get('error_constructor_composition_missing');
				}

				if ($count < max(1, $this->config->get('theme_client_ws_ttg_construstor_min'))) {
					if (empty($this->session->data['error']))
						$this->session->data['error'] = $this->language->get('error_constructor_composition');

					$product['error'] = sprintf($this->language->get('error_constructor_composition_min'), max(1, $this->config->get('theme_client_ws_ttg_construstor_min')));
				}

				if ($this->config->get('theme_client_ws_ttg_construstor_max') > 0 && $count > $this->config->get('theme_client_ws_ttg_construstor_max')) {
					if (empty($this->session->data['error']))
						$this->session->data['error'] = $this->language->get('error_constructor_composition');

					$product['error'] = sprintf($this->language->get('error_constructor_composition_max'), $this->config->get('error_constructor_composition_max'));
				}

				$product['price_old'] = $price;

				$product['price'] = $price * (1 - $special);
				$product['total'] = $product['price'] * $product['quantity'];
			}

			if (empty($product['missing'])) {
				foreach ($product['composition_products'] as $composition_product) {
					foreach ($attributes as $custom_field) {
						if ($custom_field['code'] == 'missing') {
							$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

							$value = $this->model_catalog_product->getProductCustomField($composition_product['product_id'], $custom_field['custom_field_id'], $language_id);

							$product['missing'] = $this->model_core_custom_field->getAsValue($value, $custom_field);

							if ($product['missing']) {
								if (empty($this->session->data['error']))
									$this->session->data['error'] = $this->language->get('error_constructor_composition');

								break;
							}
						}
					}

					if (!empty($product['missing'])) {
						break;
					}
				}
			}
		}
		unset($product);
	}

	public function getQuote($route, &$args) {
		foreach ($args[0] as $key => $value) {
			if ($key == 'city' && $value == 'Москва') {
				$this->load->model('localisation/zone');

				$args[0]['zone_id'] = $this->model_localisation_zone->getZoneIdByName($value);

				break;
			}
		}
	}

	public function getOrder_create($route, &$order) {
		if (empty($this->config->get('reatilcrm_history_run')) && isset($order['products'])) {
			$this->load->model('core/custom_field');
			$this->load->model('catalog/product');

			$products = array();

			$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('catalog_product');
			$attributes = $this->model_core_custom_field->getCustomFieldsByLocation('catalog_product_attribute');

			foreach ($order['products'] as $product) {
				foreach ($custom_fields as $custom_field) {
					if ($custom_field['code'] == 'composition_products') {
						$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

						$value = $this->model_catalog_product->getProductCustomField($product['product_id'], $custom_field['custom_field_id'], $language_id);

						$composition_products = $this->model_core_custom_field->getAsValue($value, $custom_field);

						foreach ($composition_products as $composition_product) {
							foreach ($attributes as $attribute) {
								if ($attribute['code'] == 'sku') {
									$language_id = $attribute['multilanguage'] ? $this->config->get('config_language_id') : -1;

									$value = $this->model_catalog_product->getProductCustomField($composition_product['product_id'], $attribute['custom_field_id'], $language_id);

									$composition_product['sku'] = $this->model_core_custom_field->getAsValue($value, $attribute);
								}
							}

							if (empty($composition_product['quantity']))
								$composition_product['quantity'] = 1;

							$composition_product['price'] = 0;
							$composition_product['quantity'] *= $product['quantity'];
							$composition_product['total'] = 0;

							$products[] = $composition_product;
						}
					}
				}

				if ($product['product_id'] == $this->config->get('theme_client_ws_ttg_construstor_product_id')) {
					$composition = isset($product['option']) && isset($product['option']['composition']) ? (array)$product['option']['composition'] : array();

					foreach ($composition as $product_id) {
						$composition_product = $this->model_catalog_product->getProduct($product_id);

						if ($composition_product) {
							foreach ($attributes as $attribute) {
								if ($attribute['code'] == 'sku') {
									$language_id = $attribute['multilanguage'] ? $this->config->get('config_language_id') : -1;

									$value = $this->model_catalog_product->getProductCustomField($composition_product['product_id'], $attribute['custom_field_id'], $language_id);

									$composition_product['sku'] = $this->model_core_custom_field->getAsValue($value, $attribute);
								}
							}

							if (empty($composition_product['quantity']))
								$composition_product['quantity'] = 1;

							$composition_product['price'] = 0;
							$composition_product['quantity'] *= $product['quantity'];
							$composition_product['total'] = 0;

							$products[] = $composition_product;
						}
					}
				}
			}

			$order['products'] = array_merge($order['products'], $products);
		}
	}


	public function shipping_method($route, &$data) {
		if (!$data['shipping_method'] && !empty($data['shipping_methods'])) {
			foreach ($data['shipping_methods'] as $shipping_method) {
				$shipping = current($shipping_method['quote']);
				
				if (!empty($shipping))
					break;
			}

			if (!empty($shipping)) {
				$data['shipping_method'] = $shipping['code'];

				$this->session->data['shipping_method'] = $shipping;
			}
		}
	}

	public function payment_method($route, &$data) {
		if (!$data['payment_method'] && !empty($data['payment_methods'])) {
			$payment = current($data['payment_methods']);

			if (!empty($payment)) {
				$data['payment_method'] = $payment['code'];

				$this->session->data['payment_method'] = $payment;
			}
		}
	}

	public function mail($route, &$data) {
		$this->load->model('catalog/product');
		$this->load->model('core/custom_field');

		$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('catalog_product');
		$attributes = $this->model_core_custom_field->getCustomFieldsByLocation('catalog_product_attribute');

		foreach ($data['products'] as &$product) {
			foreach ($custom_fields as $custom_field) {
				if ($custom_field['code'] == 'composition_products') {
					$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

					$value = $this->model_catalog_product->getProductCustomField($product['product_id'], $custom_field['custom_field_id'], $language_id);

					$product['composition_products'] = $this->model_core_custom_field->getAsValue($value, $custom_field);
				}
			}

			if (!isset($product['composition_products'])) {
				$product['composition_products'] = array();
			} else {
				$product['composition_products'] = (array)$product['composition_products'];
			}

			if ($product['product_id'] == $this->config->get('theme_client_ws_ttg_construstor_product_id')) {
				$composition = isset($product['option']) && isset($product['option']['composition']) ? (array)$product['option']['composition'] : array();

				foreach ($composition as $product_id) {
					$composition_product = $this->model_catalog_product->getProduct($product_id);

					if ($composition_product) {
						$product['composition_products'][] = $composition_product;
					}
				}

				if (isset($product['option'])) {
					unset($product['option']['composition']);
				}
			}
		}
		unset($product);
	}
}