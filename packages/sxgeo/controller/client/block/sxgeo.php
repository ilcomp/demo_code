<?php
namespace Controller\Block;

class SxGeo extends \Controller {
	public function index($json = array()) {
		$SxGeo = new \Model\Registry\SxGeo('SxGeoCity.dat');

		$ip = $this->request->server['REMOTE_ADDR'];

		$geo = $SxGeo->getCity($ip);

		if (isset($geo['city'])) {
			$json['name'] = $geo['city']['name_ru'];
			$json['city_id'] = $geo['city']['id'];
		} elseif (isset($geo['region'])) {
			$json['name'] = $geo['region']['name_ru'];
			$json['city_id'] = $geo['region']['id'];
		}

		$this->load->model('extension/system/sxgeo');

		$json['cities'] = $this->model_extension_system_sxgeo->getSxGeos(array(
			'sort' => 'sort_order',
			'limit' => 5
		));

		foreach ($json['cities'] as &$city) {
			$city['city_id'] = $city['sxgeo_id'];
		}
		unset($city);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}

	public function auto($json = array()) {
		$SxGeo = new \Model\Registry\SxGeo('SxGeoCity.dat');

		$ip = $this->request->server['REMOTE_ADDR'];

		$json = $SxGeo->getCityFull($ip);

		// if (isset($geo['city'])) {
		// 	$json['name'] = $geo['city']['name_ru'];
		// 	$json['city_id'] = $geo['city']['id'];
		// } elseif (isset($geo['region'])) {
		// 	$json['name'] = $geo['region']['name_ru'];
		// 	$json['city_id'] = $geo['region']['id'];
		// }

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}

	public function form() {
		if (isset($this->request->post['link_city_id'])) {
			$city_id = (int)$this->request->post['link_city_id'];
		} elseif (isset($this->request->post['city_id'])) {
			$city_id = (int)$this->request->post['city_id'];
		} else {
			$city_id = 0;
		}

		if ($city_id) {
			$this->load->model('extension/system/sxgeo');

			$geo = $this->model_extension_system_sxgeo->getSxGeo(2, $city_id);

			if ($geo) {
				$this->session->data['sxgeo_id'] = $geo['sxgeo_id'];

				if (!isset($this->session->data['address']))
					unset($this->session->data['address']);
			}
		}

		$url = $this->request->get;
		$route = isset($this->request->get['route']) ? $this->request->get['route'] : $this->config->get('action_default');
		unset($url['_route_']);
		unset($url['route']);

		$this->response->redirect($this->url->link($route, $url));
	}

	public function autocomplete() {
		$name = isset($this->request->get['filter_name']) ? $this->request->get['filter_name'] : '';
		$country = isset($this->request->get['filter_country']) ? $this->request->get['filter_country'] : '';

		$this->load->model('extension/system/sxgeo');

		$json = $this->model_extension_system_sxgeo->getSxGeosByName($name, $country);

		foreach ($json as &$city) {
			$city['city_id'] = $city['sxgeo_id'];
		}
		unset($city);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}
}