<?php
namespace Controller\Extension\Additionally;

class Comment extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/additionally/comment');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_core_setting->editSetting('additionally_comment', $this->request->post);

			unset($this->session->data['additionally_method']);
			unset($this->session->data['additionally_methods']);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('order/extension/additionally'));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['error_code'])) {
			$data['error_code'] = $this->error['error_code'];
		} else {
			$data['error_code'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('order/extension/additionally')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/additionally/comment')
		);

		$data['action'] = $this->url->link('extension/additionally/comment');

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('order/extension/additionally');

		if (isset($this->request->post['additionally_comment_status'])) {
			$data['additionally_comment_status'] = $this->request->post['additionally_comment_status'];
		} else {
			$data['additionally_comment_status'] = $this->config->get('additionally_comment_status');
		}

		if (isset($this->request->post['additionally_comment_sort_order'])) {
			$data['additionally_comment_sort_order'] = $this->request->post['additionally_comment_sort_order'];
		} else {
			$data['additionally_comment_sort_order'] = $this->config->get('additionally_comment_sort_order');
		}

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$data['content'] = $this->load->view('extension/additionally/comment', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/additionally/comment')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['additionally_comment_methods'] as $value)
			if (!$value['code'] || preg_match('/^[0-9a-zA-z]$/', $value['code']))
				$this->error['error_code'] = $this->language->get('error_permission');

		return !$this->error;
	}
}