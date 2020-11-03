<?php
namespace Controller\Extension\Report;

class Order extends \Controller {
	public function index() {
		$this->load->language('extension/report/order');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_core_setting->editSetting('report_order', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'type=report'));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'type=report')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/report/order')
		);

		$data['action'] = $this->url->link('extension/report/order');

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('marketplace/extension', 'type=report');

		if (isset($this->request->post['report_order_status'])) {
			$data['report_order_status'] = $this->request->post['report_order_status'];
		} else {
			$data['report_order_status'] = $this->config->get('report_order_status');
		}

		if (isset($this->request->post['report_order_sort_order'])) {
			$data['report_order_sort_order'] = $this->request->post['report_order_sort_order'];
		} else {
			$data['report_order_sort_order'] = $this->config->get('report_order_sort_order');
		}

		$data['content'] = $this->load->view('extension/report/order_form', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/report/order')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
		
	public function report() {
		$this->load->language('extension/report/order');

		if (isset($this->request->get['filter_date_start'])) {
			$filter_date_start = $this->request->get['filter_date_start'];
		} else {
			$filter_date_start = date('Y-m-d', strtotime(date('Y') . '-' . date('m') . '-01'));
		}

		if (isset($this->request->get['filter_date_end'])) {
			$filter_date_end = $this->request->get['filter_date_end'];
		} else {
			$filter_date_end = date('Y-m-d');
		}

		if (isset($this->request->get['filter_group'])) {
			$filter_group = $this->request->get['filter_group'];
		} else {
			$filter_group = 'week';
		}

		if (isset($this->request->get['filter_order_status_id'])) {
			$filter_order_status_id = $this->request->get['filter_order_status_id'];
		} else {
			$filter_order_status_id = 0;
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$this->load->model('sale/sale_report');

		$data['orders'] = array();

		$filter_data = array(
			'filter_date_start'	     => $filter_date_start,
			'filter_date_end'	     => $filter_date_end,
			'filter_group'           => $filter_group,
			'filter_order_status_id' => $filter_order_status_id,
			'start'                  => ($page - 1) * $this->config->get('admin_limit'),
			'limit'                  => $this->config->get('admin_limit')
		);

		$order_total = $this->model_sale_sale_report->getTotalOrders($filter_data);

		$results = $this->model_sale_sale_report->getOrders($filter_data);

		foreach ($results as $result) {
			$data['orders'][] = array(
				'date_start' => date($this->language->get('date_format_short'), strtotime($result['date_start'])),
				'date_end'   => date($this->language->get('date_format_short'), strtotime($result['date_end'])),
				'orders'     => $result['orders'],
				'products'   => $result['products'],
				'tax'        => $this->currency->format($result['tax'], $this->config->get('config_currency')),
				'total'      => $this->currency->format($result['total'], $this->config->get('config_currency'))
			);
		}

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('localisation/listing');

		$data['order_statuses'] = $this->model_localisation_listing->getListingItems(array('filter_listing_id' => $this->config->get('order_status_listing')));

		$data['groups'] = array();

		$data['groups'][] = array(
			'text'  => $this->language->get('text_year'),
			'value' => 'year',
		);

		$data['groups'][] = array(
			'text'  => $this->language->get('text_month'),
			'value' => 'month',
		);

		$data['groups'][] = array(
			'text'  => $this->language->get('text_week'),
			'value' => 'week',
		);

		$data['groups'][] = array(
			'text'  => $this->language->get('text_day'),
			'value' => 'day',
		);


		$url = $this->request->get;
		unset($url['route']);
		unset($url['_route_']);
		$url['page'] = '{page}';
		$url['code'] = 'order';

		$data['pagination'] = $this->load->controller('block/pagination', array(
			'total' => $order_total,
			'page'  => $page,
			'limit' => $this->config->get('config_limit'),
			'url'   => $this->url->link('report/report', $url)
		));

		$data['results'] = $this->load->controller('block/pagination/result', array(
			'total' => $order_total,
			'page'  => $page,
			'limit' => $this->config->get('config_limit')
		));

		$data['filter_date_start'] = $filter_date_start;
		$data['filter_date_end'] = $filter_date_end;
		$data['filter_group'] = $filter_group;
		$data['filter_order_status_id'] = $filter_order_status_id;

		return $this->load->view('extension/report/order_info', $data);
	}
}