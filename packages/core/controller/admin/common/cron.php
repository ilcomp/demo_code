<?php
namespace Controller\Common;

class Cron extends \Controller {
	public function index() {
		$time = time() + 10;

		$this->load->model('core/cron');

		$results = $this->model_core_cron->getCrons();

		foreach ($results as $result) {
			if ($result['status'] && (strtotime($result['cycle'], strtotime($result['date_modified'])) < $time)) {
				$this->load->controller($result['action'], $result['cron_id'], $result['code'], $result['cycle'], $result['date_added'], $result['date_modified']);

				$this->model_core_cron->editCron($result['cron_id']);
			}
		}
	}
}