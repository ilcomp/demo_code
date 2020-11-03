<?php
namespace Controller\Extension\Additionally;

class Comment extends \Controller {
	public function index($data = array()) {
		$this->load->language('extension/additionally/comment');

		if (isset($this->session->data['additionally']['comment']))
			$data['additionally'] = $this->session->data['additionally']['comment'];
		else
			$data['additionally'] = false;

		return $this->load->view('extension/additionally/comment', $data);
	}
}
