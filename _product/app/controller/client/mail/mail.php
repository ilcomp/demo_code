<?php
namespace Controller\Mail;

class Mail extends \Controller {
	public function index($setting) {
		if (!empty($setting['email']) && !empty($setting['view']) && !empty($setting['subject'])) {
			$mail = new \Mail(isset($setting['engine']) ? $setting['engine'] : $this->config->get('mail_engine'));
			$mail->parameter = isset($setting['parameter']) ? $setting['parameter'] : $this->config->get('mail_parameter');
			$mail->smtp_hostname = isset($setting['smtp_hostname']) ? $setting['smtp_hostname'] : $this->config->get('mail_smtp_hostname');
			$mail->smtp_username = isset($setting['smtp_username']) ? $setting['smtp_username'] : $this->config->get('mail_smtp_username');
			$mail->smtp_password = html_entity_decode(isset($setting['smtp_password']) ? $setting['smtp_password'] : $this->config->get('mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = isset($setting['smtp_port']) ? $setting['smtp_port'] : $this->config->get('mail_smtp_port');
			$mail->smtp_timeout = isset($setting['smtp_timeout']) ? $setting['smtp_timeout'] : $this->config->get('mail_smtp_timeout');

			$mail->setFrom(!empty($setting['from']) ? $setting['from'] : $this->config->get('mail_email'));
			$mail->setSender(html_entity_decode(isset($setting['sender']) ? $setting['sender'] : $this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject(html_entity_decode($setting['subject'], ENT_QUOTES, 'UTF-8'));

			if (isset($setting['type_view']) && $setting['type_view'] == 'text') {
				$mail->setText($setting['view']);
			} else {
				$mail->setHTML($setting['view']);
			}

			if (!empty($setting['attachment'])) {
				if (!is_array($setting['attachment']))
					$setting['attachment'] = array($setting['attachment']);

				foreach ($setting['attachment'] as $filename) {
					$mail->addAttachment($filename);
				}
			}

			$emails = is_array($setting['email']) ? $setting['email'] : array($setting['email']);

			foreach ($emails as $email) {
				$email = trim($email);

				if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
					$mail->setTo($email);
					$mail->send();
				}
			}
		}
	}
}
