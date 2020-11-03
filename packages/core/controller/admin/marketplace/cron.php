<?php
namespace Controller\Marketplace;

class Cron extends \Controller {
	private $error = array();
	
	public function index() {
		$this->load->language('marketplace/cron');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/cron');

		$this->getList();
	}

	public function delete() {
		$this->load->language('marketplace/cron');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/cron');

		if (isset($this->request->post['selected']) && $this->validate()) {
			foreach ($this->request->post['selected'] as $cron_id) {
				$this->model_core_cron->deleteCron($cron_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('marketplace/cron', 'user_token=' . $this->session->data['user_token'] . $url));
		}

		$this->getList();
	}

	public function getList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'code';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('marketplace/cron', 'user_token=' . $this->session->data['user_token'] . $url)
		);

		$data['actions']['delete'] = $this->url->link('marketplace/cron/delete', 'user_token=' . $this->session->data['user_token'] . $url);
		$data['cron'] = $this->url->link('common/cron');

		$data['crons'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit'),
			'limit' => $this->config->get('config_limit')
		);

		$cron_total = $this->model_core_cron->getTotalCrons();

		$results = $this->model_core_cron->getCrons($filter_data);

		foreach ($results as $result) {
			$data['crons'][] = array(
				'cron_id'       => $result['cron_id'],
				'code'          => $result['code'],
				'cycle'         => $result['cycle'],
				'action'        => $result['action'],
				'status'        => $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				'date_added'    => date($this->language->get('datetime_format'), strtotime($result['date_added'])),
				'date_modified' => date($this->language->get('datetime_format'), strtotime($result['date_modified'])),
				'enabled'       => $result['status']
			);
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

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_code'] = $this->url->link('marketplace/cron', 'user_token=' . $this->session->data['user_token'] . '&sort=code' . $url);
		$data['sort_cycle'] = $this->url->link('marketplace/cron', 'user_token=' . $this->session->data['user_token'] . '&sort=cycle' . $url);
		$data['sort_action'] = $this->url->link('marketplace/cron', 'user_token=' . $this->session->data['user_token'] . '&sort=action' . $url);
		$data['sort_status'] = $this->url->link('marketplace/cron', 'user_token=' . $this->session->data['user_token'] . '&sort=status' . $url);
		$data['sort_date_added'] = $this->url->link('marketplace/cron', 'user_token=' . $this->session->data['user_token'] . '&sort=date_added' . $url);
		$data['sort_date_modified'] = $this->url->link('marketplace/cron', 'user_token=' . $this->session->data['user_token'] . '&sort=date_modified' . $url);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$data['pagination'] = $this->load->controller('block/pagination', array(
			'total' => $cron_total,
			'page'  => $page,
			'limit' => $this->config->get('config_limit'),
			'url'   => $this->url->link('marketplace/cron', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}')
		));

		$data['results'] = $this->load->controller('block/pagination/result', array(
			'total' => $cron_total,
			'page'  => $page,
			'limit' => $this->config->get('config_limit')
		));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['cycles'] = array(
			'month' => 'month',
			'day' => 'day',
			'today' => 'today',
			'hour' => 'hour',
			'minute' => 'minute',
			'second' => 'second',
		);

		$data['content'] = $this->load->view('marketplace/cron', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'marketplace/cron')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function run() {
		$this->load->language('marketplace/cron');

		$json = array();

		if (isset($this->request->get['cron_id'])) {
			$cron_id = $this->request->get['cron_id'];
		} else {
			$cron_id = 0;
		}

		if (!$this->user->hasPermission('modify', 'marketplace/cron')) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			$this->load->model('core/cron');

			$cron_info = $this->model_core_cron->getCron($cron_id);

			if ($cron_info) {
				ob_start();

				$this->load->controller($cron_info['action'], $cron_id, $cron_info['code'], $cron_info['cycle'], $cron_info['date_added'], $cron_info['date_modified']);

				$json['result'] = ob_get_clean();

				$this->model_core_cron->editCron($cron_info['cron_id']);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}

	public function enable() {
		$this->load->language('marketplace/cron');

		$json = array();

		if (isset($this->request->get['cron_id'])) {
			$cron_id = $this->request->get['cron_id'];
		} else {
			$cron_id = 0;
		}

		if (!$this->user->hasPermission('modify', 'marketplace/cron')) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			$this->load->model('core/cron');

			$this->model_core_cron->editStatus($cron_id, 1);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}

	public function disable() {
		$this->load->language('marketplace/cron');

		$json = array();

		if (isset($this->request->get['cron_id'])) {
			$cron_id = $this->request->get['cron_id'];
		} else {
			$cron_id = 0;
		}

		if (!$this->user->hasPermission('modify', 'marketplace/cron')) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			$this->load->model('core/cron');

			$this->model_core_cron->editStatus($cron_id, 0);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}

	public function edit_cycle() {
		$this->load->language('marketplace/cron');

		$json = array();

		if (!isset($this->request->get['cron_id'])) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!isset($this->request->get['cycle'])) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$this->user->hasPermission('modify', 'marketplace/cron')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!isset($json['error'])) {
			$this->load->model('core/cron');

			$this->model_core_cron->editCycle($this->request->get['cron_id'], $this->request->get['cycle']);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}
}