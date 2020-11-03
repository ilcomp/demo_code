<?php
namespace Controller\Extension\Module;

class Slider extends \Controller {
	public function index($setting) {
		$this->load->model('tool/image');

		$data = $setting;

		$image_width = $setting['width'] ? $setting['width'] : 300;
		$image_height = $setting['height'] ? $setting['height'] : 300;

		$image_width_desktop = $setting['width_desktop'] ? $setting['width_desktop'] : 300;
		$image_height_desktop = $setting['height_desktop'] ? $setting['height_desktop'] : 300;

		$data['slides'] = array();

		foreach ($setting['slides'] as $slide) {
			if (is_file(DIR_IMAGE . $slide['image'])) {
				$slide['thumb'] = $this->model_tool_image->resize(html_entity_decode($slide['image'], ENT_QUOTES, 'UTF-8'), $image_width, $image_height);
				$slide['thumb_desktop'] = $this->model_tool_image->resize(html_entity_decode($slide['image_desktop'], ENT_QUOTES, 'UTF-8'), $image_width_desktop, $image_height_desktop);

				if ($slide['link']) {
					$url_info = parse_url($slide['link']);

					if (!empty($url_info['query']))
						parse_str($url_info['query'], $query);
					else
						$query = array();

					$route = isset($query['route']) ? $query['route'] : '';

					if ($route) {
						unset($query['route']);

						$slide['link'] = $this->url->link($route, $query);
					}
				}

				$data['slides'][] = $slide;
			}
		}

		return $this->load->view('extension/module/slider', $data);
	}
}