<?php
namespace Controller\Localisation;

class SxGeo extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('localisation/sxgeo');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/system/sxgeo');

		$this->getList();
	}

	public function edit() {
		$this->load->language('localisation/sxgeo');

		$this->load->model('extension/system/sxgeo');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_extension_system_sxgeo->editSxGeo($this->request->get['sxgeo_id'], $this->request->post);

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
		}

		$data['error'] = array();

		if (isset($this->error['warning'])) {
			$data['error'][] = $this->error['warning'];
		}

		if (isset($this->error['error'])) {
			$data['error'][] = $this->error['error'];
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->get['sxgeo_id'])) {
			$sxgeo_info = $this->model_extension_system_sxgeo->getSxGeo($this->request->get['sxgeo_id']);
		}

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($sxgeo_info)) {
			$data['sort_order'] = $sxgeo_info['sort_order'];
		} else {
			$data['sort_order'] = '';
		}

		header('Content-type: text/json; charset=utf8');

		$this->response->setOutput(json_encode($data));
	}

	protected function getList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'region';
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
			'href' => $this->url->link('common/dashboard')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('localisation/sxgeo', $url)
		);

		$data['sxgeos'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('admin_limit'),
			'limit' => $this->config->get('admin_limit')
		);

		$sxgeo_total = $this->model_extension_system_sxgeo->getTotalSxGeos();

		$data['sxgeos'] = $this->model_extension_system_sxgeo->getSxGeos($filter_data);

		foreach ($data['sxgeos'] as &$value) {
			$value['edit'] = $this->url->link('localisation/sxgeo/edit', 'sxgeo_id=' . $value['sxgeo_id']);
		}
		unset($value);

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

		$data['sort_name'] = $this->url->link('localisation/sxgeo', 'sort=name' . $url);
		$data['sort_region'] = $this->url->link('localisation/sxgeo', 'sort=region' . $url);
		$data['sort_sort_order'] = $this->url->link('localisation/sxgeo', 'sort=sort_order' . $url);

		$url = $this->request->get;
		unset($url['route']);
		unset($url['_route_']);
		$url['page'] = '{page}';

		$data['pagination'] = $this->load->controller('block/pagination', array(
			'total' => $sxgeo_total,
			'page'  => $page,
			'limit' => $this->config->get('config_limit'),
			'url'   => $this->url->link('localisation/sxgeo', $url)
		));

		$data['results'] = $this->load->controller('block/pagination/result', array(
			'total' => $sxgeo_total,
			'page'  => $page,
			'limit' => $this->config->get('config_limit')
		));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['content'] = $this->load->view('localisation/sxgeo', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'localisation/sxgeo')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!isset($this->request->post['sort_order']) || !preg_match('/^-?\d+$/', $this->request->post['speed_max'])) {
			$this->error['error'] = $this->language->get('error_integer');
		}

		if ($this->error) {
			unset($this->request->post['sort_order']);
		}

		return !$this->error;
	}

	public function autocomplete() {
		$name = isset($this->request->get['filter_name']) ? $this->request->get['filter_name'] : '';
		$country = isset($this->request->get['filter_country']) ? $this->request->get['filter_country'] : '';

		$this->load->model('extension/system/sxgeo');

		$json = $this->model_extension_system_sxgeo->getSxGeosByName($name, $country);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}
}