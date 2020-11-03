<?php
namespace Controller\Marketplace;

class Extension extends \Controller {
	public function index() {
		$this->load->language('marketplace/extension');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'])
		);

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->get['type'])) {
			$data['type'] = $this->request->get['type'];
		} else {
			$data['type'] = 'module';
		}

		$data['categories'] = array();

		$files = glob(DIR_CONTROLLER . 'extension/extension/*.php', GLOB_BRACE);

		foreach ($files as $file) {
			$extension = basename($file, '.php');

			// Compatibility code for old extension folders
			$this->load->language('extension/extension/' . $extension, 'extension');

			if ($this->user->hasPermission('access', 'extension/extension/' . $extension)) {
				$files = glob(DIR_CONTROLLER . 'extension/' . $extension . '/*.php', GLOB_BRACE);

				$data['categories'][] = array(
					'code' => $extension,
					'text' => $this->language->get('extension')->get('heading_title') . ' (' . count($files) .')',
					'href' => $this->url->link('extension/extension/' . $extension, 'user_token=' . $this->session->data['user_token'])
				);
			}
		}

		$data['content'] = $this->load->view('marketplace/extension', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}
}