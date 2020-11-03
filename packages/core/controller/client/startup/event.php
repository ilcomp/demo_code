<?php
namespace Controller\Startup;

class Event extends \Controller {
	public function index() {
		$this->load->model('core/event');

		$results = $this->model_core_event->getEvents(array(
			'filter_trigger' => 'client/',
			'filter_status' => 1,
		));

		foreach ($results as $result) {
			$this->event->register(substr($result['trigger'], 7), new \Action($result['action']), $result['sort_order']);
		}

		// $files = glob(DIR_CONTROLLER . 'event/*.php');

		// if ($files) {
		// 	foreach ($files as $file) {
		// 		$extension = basename($file, '.php');
				
		// 		new \Action('event/' . $extension);
		// 	}
		// }

		$result = $this->event->trigger('controller/startup/event/after', array());
	}
}