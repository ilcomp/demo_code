<?php
namespace Controller\Mail;

class Forgotten extends \Controller {
	public function index(&$route, &$args, &$output) {
		if ($args[0] && $args[1]) {
			$this->load->language('mail/forgotten');

			$data['text_greeting'] = sprintf($this->language->get('text_greeting'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));

			$data['reset'] = str_replace('&amp;', '&', $this->url->link('common/reset', 'email=' . urlencode($args[0]) . '&code=' . $args[1]));
			$data['ip'] = $this->request->server['REMOTE_ADDR'];

			$this->load->controller('mail/mail', array(
				'subject' => sprintf($this->language->get('text_subject'), $this->config->get('config_name')),
				'view' => $this->load->view('mail/forgotten', $data),
				'email' => $args[0]
			));
		}
	}
}
