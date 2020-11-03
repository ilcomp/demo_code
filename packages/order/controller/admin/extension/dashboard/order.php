<?php
namespace Controller\Extension\Dashboard;

class Order extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/dashboard/order');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_core_setting->editSetting('dashboard_order', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension',  'type=dashboard'));
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
			'href' => $this->url->link('marketplace/extension', 'type=dashboard')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/dashboard/order')
		);

		$data['action'] = $this->url->link('extension/dashboard/order');

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('marketplace/extension', 'type=dashboard');

		if (isset($this->request->post['dashboard_order_width'])) {
			$data['dashboard_order_width'] = $this->request->post['dashboard_order_width'];
		} else {
			$data['dashboard_order_width'] = $this->config->get('dashboard_order_width');
		}
	
		$data['columns'] = array();
		
		for ($i = 3; $i <= 12; $i++) {
			$data['columns'][] = $i;
		}
				
		if (isset($this->request->post['dashboard_order_status'])) {
			$data['dashboard_order_status'] = $this->request->post['dashboard_order_status'];
		} else {
			$data['dashboard_order_status'] = $this->config->get('dashboard_order_status');
		}

		if (isset($this->request->post['dashboard_order_sort_order'])) {
			$data['dashboard_order_sort_order'] = $this->request->post['dashboard_order_sort_order'];
		} else {
			$data['dashboard_order_sort_order'] = $this->config->get('dashboard_order_sort_order');
		}

		$data['content'] = $this->load->view('extension/dashboard/order_form', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/dashboard/order')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
	
	public function dashboard() {
		$this->load->language('extension/dashboard/order');

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('sale/sale_report');

		$today = $this->model_sale_sale_report->getTotalSales(array('filter_date_added' => date('Y-m-d', strtotime('-1 day'))));

		$yesterday = $this->model_sale_sale_report->getTotalSales(array('filter_date_added' => date('Y-m-d', strtotime('-2 day'))));

		$difference = $today - $yesterday;

		if ($difference && (int)$today) {
			$data['percentage'] = round(($difference / $today) * 100);
		} else {
			$data['percentage'] = 0;
		}

		$sale_total = $this->model_sale_sale_report->getTotalSales();

		if ($sale_total > 1000000000000) {
			$data['total'] = round($sale_total / 1000000000000, 1) . 'T';
		} elseif ($sale_total > 1000000000) {
			$data['total'] = round($sale_total / 1000000000, 1) . 'B';
		} elseif ($sale_total > 1000000) {
			$data['total'] = round($sale_total / 1000000, 1) . 'M';
		} elseif ($sale_total > 1000) {
			$data['total'] = round($sale_total / 1000, 1) . 'K';
		} else {
			$data['total'] = round($sale_total);
		}

		$data['sale'] = $this->url->link('sale/order');

		return $this->load->view('extension/dashboard/order_info', $data);
	}
}
