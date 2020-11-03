<?php
namespace Controller\Startup;

class Event extends \Controller {
	public function index() {
		$this->load->model('core/event');

		$results = $this->model_core_event->getEvents(array(
			'filter_trigger' => 'api/',
			'filter_status' => 1,
		));

		foreach ($results as $result) {
			$this->event->register(substr($result['trigger'], 4), new \Action($result['action']), $result['sort_order']);
		}

		$result = $this->event->trigger('controller/startup/event/after', array());
	}
}