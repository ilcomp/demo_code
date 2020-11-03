<?php
namespace Controller\Order;

class Order extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('order/order');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('order/order_modify');

		$this->getList();
	}

	public function add() {
		$this->load->language('order/order');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('order/order_modify');

		$this->getForm();
	}

	public function edit() {
		$this->load->language('order/order');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('order/order_modify');

		$this->getForm();
	}

	public function delete() {
		$this->load->language('order/order');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->session->data['success'] = $this->language->get('text_success');

		$url = $this->request->get;
		unset($url['route']);
		unset($url['_route_']);

		$this->response->redirect($this->url->link('order/order', $url));
	}

	protected function getList() {
		if (isset($this->request->get['filter_order_id'])) {
			$filter_order_id = $this->request->get['filter_order_id'];
		} else {
			$filter_order_id = '';
		}

		if (isset($this->request->get['filter_order_status'])) {
			$filter_order_status = $this->request->get['filter_order_status'];
		} else {
			$filter_order_status = '';
		}

		if (isset($this->request->get['filter_order_status_id'])) {
			$filter_order_status_id = $this->request->get['filter_order_status_id'];
		} else {
			$filter_order_status_id = '';
		}

		if (isset($this->request->get['filter_total'])) {
			$filter_total = $this->request->get['filter_total'];
		} else {
			$filter_total = '';
		}

		if (isset($this->request->get['filter_date_added'])) {
			$filter_date_added = $this->request->get['filter_date_added'];
		} else {
			$filter_date_added = '';
		}

		if (isset($this->request->get['filter_date_modified'])) {
			$filter_date_modified = $this->request->get['filter_date_modified'];
		} else {
			$filter_date_modified = '';
		}

		if (isset($this->request->get['filter_account_id'])) {
			$filter_account_id = $this->request->get['filter_account_id'];
		} else {
			$filter_account_id = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'order_id';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'DESC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = $this->request->get;
		unset($url['route']);
		unset($url['_route_']);

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('order/order', $url)
		);

		$data['add'] = $this->url->link('order/order/add', $url);
		$data['delete_refresh'] = $this->url->link('order/order/delete', $url);

		$url = 'user_token=' . $this->session->data['user_token'];

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$data['action_filter'] = $this->url->link('order/order', $url);

		$data['orders'] = array();

		$filter_data = array(
			'filter' => array(
				'order_id'        => $filter_order_id,
				'order_status'    => $filter_order_status,
				'order_status_id' => $filter_order_status_id,
				'total'           => $filter_total,
				'date_added'      => $filter_date_added,
				'date_modified'   => $filter_date_modified,
				'account_id'      => $filter_account_id
			),
			'sort'                   => $sort,
			'order'                  => $order,
			'start'                  => ($page - 1) * $this->config->get('config_limit'),
			'limit'                  => $this->config->get('config_limit')
		);

		$order_total = $this->model_order_order_modify->getTotalOrders($filter_data);

		$results = $this->model_order_order_modify->getOrders($filter_data);

		$url = $this->request->get;
		unset($url['route']);
		unset($url['_route_']);

		foreach ($results as $result) {
			$result['order_status'] = $result['order_status'] ? $result['order_status'] : $this->language->get('text_missing');
			$result['order_status_value'] = $result['order_status_value'] ? $result['order_status_value'] : $this->language->get('text_missing');
			$result['total'] = $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']);
			$result['date_added'] = strtotime($result['date_added']);
			$result['date_modified'] = strtotime($result['date_modified']);

			$url['order_id'] = $result['order_id'];

			$result['view'] = $this->url->link('order/order/info', $url);
			$result['edit'] = $this->url->link('order/order/edit', $url);

			$data['orders'][] = $result;
		}

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = $this->request->get;
		unset($url['route']);
		unset($url['_route_']);
		$url['order'] = $order == 'ASC' ? 'DESC' : 'ASC';

		foreach (array('order_id', 'order_status', 'total', 'date_added', 'date_modified') as $result) {
			$url['sort'] = $result;
			$data['sort_' . $result] = $this->url->link('order/order', $url);
		}

		$url = $this->request->get;
		unset($url['route']);
		unset($url['_route_']);
		$url['page'] = '{page}';

		$data['pagination'] = $this->load->controller('block/pagination', array(
			'total' => $order_total,
			'page'  => $page,
			'limit' => $this->config->get('config_limit'),
			'url'   => $this->url->link('order/order', $url)
		));

		$data['results'] = $this->load->controller('block/pagination/result', array(
			'total' => $order_total,
			'page'  => $page,
			'limit' => $this->config->get('config_limit')
		));

		$data['filter_order_id'] = $filter_order_id;
		$data['filter_order_status'] = $filter_order_status;
		$data['filter_order_status_id'] = $filter_order_status_id;
		$data['filter_total'] = $filter_total;
		$data['filter_date_added'] = $filter_date_added;
		$data['filter_date_modified'] = $filter_date_modified;
		$data['filter_account_id'] = $filter_account_id;

		$data['sort'] = $sort;
		$data['order'] = $order;

		$this->load->model('localisation/listing');

		$data['order_statuses'] = $this->model_localisation_listing->getListingItems(array('filter_listing_id' => $this->config->get('order_status_listing')));

		// API login
		$data['catalog'] = HTTP_APPLICATION_CLIENT;
		$data['api'] = HTTP_APPLICATION_API;

		$this->load->model('core/api');

		$api_info = $this->model_core_api->getApi($this->config->get('system_api_id'));

		if ($api_info && $this->user->hasPermission('modify', 'order/order')) {
			$session = new \Session($this->config->get('session_engine'), $this->registry);

			$session->start();

			$this->model_user_api->deleteApiSessionBySessonId($session->getId());

			$this->model_user_api->addApiSession($api_info['api_id'], $session->getId(), $this->request->server['REMOTE_ADDR']);

			$session->data['api_id'] = $api_info['api_id'];

			$data['api_token'] = $session->getId();
		} else {
			$data['api_token'] = '';
		}

		$data['account_status'] = false;

		$data['content'] = $this->load->view('order/order_list', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	public function getForm() {
		if (isset($this->request->get['order_id'])) {
			$data['heading_title'] = sprintf($this->language->get('text_order_title'), $this->request->get['order_id']);

			$order_info = $this->model_order_order_modify->getOrder($this->request->get['order_id']);
		}

		$data = $order_info;

		if (empty($order_info)) {
			$data['order_id'] = 0;
			$data['store_id'] = 0;
			$data['store_url'] = HTTP_APPLICATION_CLIENT;

			$data['custom_field'] = array();
			$data['order_products'] = array();
			$data['order_totals'] = array();

			$data['order_status_id'] = $this->config->get('order_status_default');
			$data['currency_code'] = $this->config->get('catalog_currency');
		} else {
			$data['order_id'] = (int)$data['order_id'];

			// Products
			$data['order_products'] = $this->model_order_order_modify->getOrderProducts($order_info['order_id']);

			$data['order_totals'] = $this->model_order_order_modify->getOrderTotals($order_info['order_id']);

			foreach ($data['order_totals'] as &$order_total) {
				$order_total['total_format'] = $this->currency->format($order_total['value'], $order_info['currency_code'], $order_info['currency_value']);
			}
			unset($order_total);
		}

		$data['text_form'] = !isset($this->request->get['order_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		$url = $this->request->get;
		unset($url['route']);
		unset($url['_route_']);
		unset($url['order_id']);

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('order/order', $url)
		);

		$data['cancel'] = $this->url->link('order/order', $url);

		$data['user_token'] = $this->session->data['user_token'];

		$data['account'] = '';
		$data['account_groups'] = array();

		if ($this->config->get('account_status')) {
			$this->load->model('account/account');

			$account_info = $this->model_account_account->getAccount($data['account_id']);

			if ($account_info)
				$data['account'] = $account_info['login'];


			$this->load->model('account/account_group');

			$data['account_groups'] = $this->model_account_account_group->getAccountGroups();
		}

		$settings = array();

		$settings['custom_field_values'] = $data['custom_field'];

		$settings['location'] = 'order_contact';

		$data['custom_fields_contact'] = $this->load->controller('setting/custom_field/render', $settings);

		$settings['location'] = 'order_address';

		$data['custom_fields_address'] = $this->load->controller('setting/custom_field/render', $settings);

		// Stores
		$this->load->model('core/store');

		$data['stores'] = array();

		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		);

		$results = $this->model_core_store->getStores();

		foreach ($results as $result) {
			$data['stores'][] = array(
				'store_id' => $result['store_id'],
				'name'     => $result['name']
			);
		}

		$this->load->model('localisation/listing');

		$data['order_statuses'] = $this->model_localisation_listing->getListingItems(array('filter_listing_id' => $this->config->get('order_status_listing')));

		$this->load->model('localisation/currency');

		$data['currencies'] = $this->model_localisation_currency->getCurrencies();

		// API login
		$data['catalog'] = HTTP_APPLICATION_CLIENT;

		// API login
		$this->load->model('core/api');

		$api_info = $this->model_core_api->getApi($this->config->get('system_api_id'));

		if ($api_info && $this->user->hasPermission('modify', 'order/order')) {
			$session = new \Session($this->config->get('session_engine'), $this->registry);

			$session->start();

			$this->model_user_api->deleteApiSessionBySessonId($session->getId());

			$this->model_user_api->addApiSession($api_info['api_id'], $session->getId(), $this->request->server['REMOTE_ADDR']);

			$session->data['api_id'] = $api_info['api_id'];

			$data['api_token'] = $session->getId();
		} else {
			$data['api_token'] = '';
		}

		$data['content'] = $this->load->view('order/order_form', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	public function info() {
		$this->load->model('order/order_modify');

		if (isset($this->request->get['order_id'])) {
			$order_id = (int)$this->request->get['order_id'];
		} else {
			$order_id = 0;
		}

		$order_info = $this->model_order_order_modify->getOrder($order_id);

		if ($order_info) {
			$data = $order_info;

			$data['order_id'] = (int)$this->request->get['order_id'];

			if (!$order_info['store_id']) {
				$data['store_url'] = HTTP_APPLICATION_CLIENT;
			}

			$this->load->language('order/order');

			$this->document->setTitle($this->language->get('heading_title'));

			$data['heading_title'] = sprintf($this->language->get('text_order_title'), $this->request->get['order_id']);

			$data['text_ip_add'] = sprintf($this->language->get('text_ip_add'), $this->request->server['REMOTE_ADDR']);
			$data['text_order'] = sprintf($this->language->get('text_order'), $order_id);

			$url = $this->request->get;
			unset($url['route']);
			unset($url['_route_']);
			unset($url['order_id']);

			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/dashboard')
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('order/order', $url)
			);

			$data['edit'] = $this->url->link('order/order/edit', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . (int)$this->request->get['order_id']);
			$data['cancel'] = $this->url->link('order/order', $url);

			$data['user_token'] = $this->session->data['user_token'];

			// Uploaded files
			$this->load->model('tool/upload');

			$data['products'] = array();

			$products = $this->model_order_order_modify->getOrderProducts($this->request->get['order_id']);

			foreach ($products as $product) {
				$product['price'] = $this->currency->format($product['price'], $order_info['currency_code'], $order_info['currency_value']);
				$product['total'] = $this->currency->format($product['total'], $order_info['currency_code'], $order_info['currency_value']);
				$product['href'] = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $product['product_id']);

				$data['products'][] = $product;
			}

			$data['totals'] = array();

			$totals = $this->model_order_order_modify->getOrderTotals($this->request->get['order_id']);

			foreach ($totals as $total) {
				$data['totals'][] = array(
					'title' => $total['title'],
					'text'  => $this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value'])
				);
			}

			$data['commission'] = $this->currency->format($order_info['commission'], $order_info['currency_code'], $order_info['currency_value']);

			$this->load->model('localisation/listing');

			$order_status_info = $this->model_localisation_listing->getListingItem($order_info['order_status_id']);

			if ($order_status_info) {
				$data['order_status'] = $order_status_info['name'];
			} else {
				$data['order_status'] = '';
			}

			$data['order_statuses'] = $this->model_localisation_listing->getListingItems(array('filter_listing_id' => $this->config->get('order_status_listing')));

			$this->load->model('core/custom_field');

			$custom_field_values = $this->model_order_order_modify->getOrderCustomFields($order_info['order_id']);

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

			$data['ip'] = $order_info['ip'];
			$data['forwarded_ip'] = $order_info['forwarded_ip'];
			$data['user_agent'] = $order_info['user_agent'];
			$data['accept_language'] = $order_info['accept_language'];

			// Additional Tabs
			$data['tabs'] = array();

			if ($this->user->hasPermission('access', 'extension/payment/' . $order_info['payment_code'])) {
				if (is_file(DIR_CONTROLLER . 'extension/payment/' . $order_info['payment_code'] . '.php')) {
					$content = $this->load->controller('extension/payment/' . $order_info['payment_code'] . '/order');
				} else {
					$content = '';
				}

				if ($content) {
					$this->load->language('extension/payment/' . $order_info['payment_code']);

					$data['tabs'][] = array(
						'code'    => $order_info['payment_code'],
						'title'   => $this->language->get('heading_title'),
						'content' => $content
					);
				}
			}

			$this->load->model('core/extension');

			$extensions = $this->model_core_extension->getInstalled('fraud');

			foreach ($extensions as $extension) {
				if ($this->config->get('fraud_' . $extension . '_status')) {
					$this->load->language('extension/fraud/' . $extension, 'extension');

					$content = $this->load->controller('extension/fraud/' . $extension . '/order');

					if ($content) {
						$data['tabs'][] = array(
							'code'    => $extension,
							'title'   => $this->language->get('extension')->get('heading_title'),
							'content' => $content
						);
					}
				}
			}

			$data['additionallys'] = $this->model_order_order_modify->getOrderAdditionallys($order_info['order_id']);

			// The URL we send API requests to
			$data['catalog'] = HTTP_APPLICATION_CLIENT;

			// API login
			$this->load->model('core/api');

			$api_info = $this->model_core_api->getApi($this->config->get('system_api_id'));

			if ($api_info && $this->user->hasPermission('modify', 'order/order')) {
				$session = new \Session($this->config->get('session_engine'), $this->registry);

				$session->start();

				$this->model_user_api->deleteApiSessionBySessonId($session->getId());

				$this->model_user_api->addApiSession($api_info['api_id'], $session->getId(), $this->request->server['REMOTE_ADDR']);

				$session->data['api_id'] = $api_info['api_id'];

				$data['api_token'] = $session->getId();
			} else {
				$data['api_token'] = '';
			}

			$data['content'] = $this->load->view('order/order_info', $data);

			$this->response->setOutput($this->load->controller('common/template', $data));
		} else {
			return new \Action('error/not_found');
		}
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'order/order')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}