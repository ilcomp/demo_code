<?php
namespace Controller\Tool;

class Localisation extends \Controller {
	public function country() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('localisation/country');

			if (isset($this->request->get['filter_name'])) {
				$filter_name = $this->request->get['filter_name'];
			} else {
				$filter_name = '';
			}

			if (isset($this->request->get['limit'])) {
				$limit = $this->request->get['limit'];
			} else {
				$limit = 5;
			}

			$filter_data = array(
				'filter' => array(
					'name' => $filter_name . '%',
					'status' => 1
				),
				'start'  => 0,
				'limit'  => $limit
			);

			$results = $this->model_localisation_country->getCountries($filter_data);

			$count = count($results);

			if ($count < $limit && !empty($filter_name)) { 
				$filter_data = array(
					'filter' => array(
						'full_name' => $filter_name . '%',
						'status' => 1
					),
					'start'  => 0,
					'limit'  => $limit - $count
				);

				$countries = $this->model_localisation_country->getCountries($filter_data);

				foreach ($countries as $country) {
					$country['name'] = $country['full_name'];

					$results[] = $country;
				}
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($results);
	}
}