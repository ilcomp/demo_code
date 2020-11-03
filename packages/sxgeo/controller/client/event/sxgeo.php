<?php
namespace Controller\Event;

class SXGeo extends \Controller {
	public function startup() {
		if ($this->config->get('sxgeo_default')) {
			$this->event->register('controller/common/template/before', new \Action('event/sxgeo/template'), 0);
			$this->event->register('controller/order/checkout/reset/after', new \Action('event/sxgeo/checkout_reset'), 10);
			$this->event->register('view/block/order/address/before', new \Action('event/sxgeo/view'), 0);

			if (isset($this->request->get['city_id'])) {
				$city_id = (int)$this->request->get['city_id'];

				if ($city_id) {
					$this->load->model('extension/system/sxgeo');

					$geo = $this->model_extension_system_sxgeo->getSxGeo(2, $city_id);

					if ($geo)
						$this->session->data['sxgeo_id'] = $geo['sxgeo_id'];
				}

				$url = $this->request->get;
				$route = isset($this->request->get['route']) ? $this->request->get['route'] : $this->config->get('action_default');
				unset($url['_route_']);
				unset($url['route']);
				unset($url['city_id']);

				$this->response->redirect($this->url->link($route, $url));
			}

			if (!isset($this->session->data['sxgeo_id'])) {
				$SxGeo = new \Model\Registry\SxGeo('SxGeoCity.dat');

				$ip = $this->request->server['REMOTE_ADDR'];

				$geo = $SxGeo->getCityFull($ip);

				if (isset($geo['city']) && !empty($geo['city']['id']))
					$this->session->data['sxgeo_id'] = $geo['city']['id'];
				elseif (isset($geo['region']) && !empty($geo['region']['id']))
					$this->session->data['sxgeo_id'] = $geo['region']['id'];
				elseif (isset($geo['country']) && !empty($geo['country']['id']))
					$this->session->data['sxgeo_id'] = $geo['country']['id'];
				else
					$this->session->data['sxgeo_id'] = $this->config->get('sxgeo_default');
			}
		}
	}

	public function template($route, &$args) {
		if (isset($this->session->data['sxgeo_id'])) {
			$this->load->model('extension/system/sxgeo');

			$geo = $this->model_extension_system_sxgeo->getSxGeo(2, $this->session->data['sxgeo_id']);
		}

		if (!empty($geo)) {
			$args[0]['location_name'] = $geo['name'];
		}

		$args[0]['actions']['location'] = $this->url->link('common/home');

		$data_view['actions']['data'] = $this->url->link('block/sxgeo');
		$data_view['actions']['location_autocomplete'] = $this->url->link('block/sxgeo/autocomplete');
		$data_view['actions']['location'] = $this->url->link('block/sxgeo/auto');

		$args[0]['blocks']['location'] = $this->load->view('block/location', $data_view);
	}

	public function checkout_reset($route, &$data) {
		if (isset($this->session->data['sxgeo_id'])) {
			if (!isset($this->session->data['address']))
				$this->session->data['address'] = array();

			if (!isset($this->session->data['address']['custom_field']))
				$this->session->data['address']['custom_field'] = array();

			$this->load->model('core/custom_field');
			$this->load->model('extension/system/sxgeo');

			$geo = $this->model_extension_system_sxgeo->getSxGeo(2, $this->session->data['sxgeo_id']);

			$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('order_address');

			foreach ($custom_fields as $custom_field) {
				if ($custom_field['code'] == 'country_id') {
					// $language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

					// if (empty($this->session->data['address']['custom_field'][$custom_field['custom_field_id']]) || empty($this->session->data['address']['custom_field'][$custom_field['custom_field_id']][$language_id]))
					// 	$this->session->data['address']['custom_field'][$custom_field['custom_field_id']][$language_id] = $geo['name'];

					// break;
				} elseif ($custom_field['code'] == 'city' && isset($geo['name'])) {
					$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

					if (empty($this->session->data['address']['custom_field'][$custom_field['custom_field_id']]) || empty($this->session->data['address']['custom_field'][$custom_field['custom_field_id']][$language_id]))
						$this->session->data['address']['custom_field'][$custom_field['custom_field_id']][$language_id] = $geo['name'];

					break;
				}
			}
		}
	}

	public function view($route, &$data) {
		$data['actions']['country_autocomplete'] = $this->url->link('tool/localisation/country');
		$data['actions']['location_autocomplete'] = $this->url->link('block/sxgeo/autocomplete');
	}
}