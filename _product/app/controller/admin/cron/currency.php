<?php
namespace Controller\Cron;

class Currency extends \Controller {
	public function index($cron_id, $code, $cycle, $date_added, $date_modified) {
		$this->load->controller('extension/currency/' . $this->config->get('catalog_currency_engine') . '/currency', $this->config->get('catalog_currency'));
	}
}