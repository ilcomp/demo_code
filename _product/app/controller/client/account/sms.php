<?php
namespace Controller\Account;

class SMS extends \Controller {
	public function status() {
		$this->load->model('core/sms');

		$sms_id = isset($this->request->get['id']) ? (int)(int)preg_replace('/\D/', '', $this->request->get['id']) : '';
		$status_id = isset($this->request->get['status']) ? (int)$this->request->get['status'] : 0;

		if ($sms_id)
			$this->model_core_sms->updateStatusSmsByCode($sms_id, $status_id);

		$this->response->setOutput('100');
	}
}
