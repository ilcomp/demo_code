<?php
namespace Controller\Common;

class Search extends \Controller {
	public function index($data = array()) {
		$this->load->language('common/search');

		$this->load->model('tool/image');

		if (isset($this->request->get['search']) && preg_replace('/[\s]/', '', $this->request->get['search'])) {
			$search = $this->request->get['search'];
		} else {
			$search = '';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		if ($this->config->get('theme_' . $this->config->get('config_theme') . '_search_limit')) {
			$limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_search_limit');
		} elseif ($this->config->get('config_limit')) {
			$limit = (int)$this->config->get('config_limit');
		} else {
			$limit = 12;
		}

		if ($search) {
			$this->document->setTitle($this->language->get('text_result') .  ' - ' . $this->request->get['search']);
		} else {
			$this->document->setTitle($this->language->get('text_result'));
		}

		if (!isset($data['search_results']))
			$data['search_results'] = array();

		$total = isset($data['total']) ? $data['total'] : 0;

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$url = '';

		if (isset($this->request->get['search'])) {
			$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_result'),
			'href' => $this->url->link('common/search', $url)
		);

		if ($search) {
			$data['heading_title'] = $this->language->get('text_result') .  ' - ' . $this->request->get['search'];
		} else {
			$data['heading_title'] = $this->language->get('text_result');
		}

		$url = $this->request->get;
		unset($url['route']);
		unset($url['_route_']);
		$url['page'] = '{page}';

		$data['pagination'] = $this->load->controller('block/pagination', array(
			'total' => $total,
			'page'  => $page,
			'limit' => $limit,
			'url'   => $this->url->link('common/search',  $url)
		));

		$data['results'] = $this->load->controller('block/pagination/result', array(
			'total' => $total,
			'page'  => $page,
			'limit' => $limit
		));

		$data['search'] = $search;
		$data['limit'] = $limit;
		$data['total'] = $total;
		$data['page'] = $page;

		if (isset($this->request->get['page'])) {
			$url['page'] = $this->request->get['page'];
		}

		$data['href_state'] = $this->url->link('common/search', $url);

		$data['language'] = $this->config->get('config_language');

		$data['continue'] = $this->url->link('common/home');

		$data['content'] = $this->load->view('common/search', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	public function autocomplete($data = array()) {
		if (!isset($data['search_results']))
			$data['search_results'] = array();

		$json = $data['search_results'];

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}
}