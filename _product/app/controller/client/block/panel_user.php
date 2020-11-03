<?php
namespace Controller\Block;

class PanelUser extends Controller {
	public function index($setting) {
		$user = new Model\User($this->registry);

		if ($user->isLogged()) {
			$this->load->language('block/panel_user');

			if (isset($setting['user_route'])) {
				$user_url = new Url(HTTPS_SERVER . 'admin/');

				$user_args = isset($setting['user_args']) ? $setting['user_args'] : '';

				$user_args .= ($user_args ? '&' : '') . 'user_token=' . $this->session->data['user_token'];

				$data['edit'] = $user_url->link($setting['user_route'], $user_args);
			} else {
				$data['edit'] = '';
			}

			//header("X-Frame-Options: SAMEORIGIN");

			return $this->load->view('block/panel_user', $data);
		}
	}
}
