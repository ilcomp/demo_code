<?php
namespace Controller\Event;

class SxGeo extends \Controller {
	public function startup() {
		$this->event->register('view/block/column_left/before', new \Action('event/sxgeo/menu'), 0);
	}

	public function menu(&$route, &$data) {
		$this->load->language('localisation/sxgeo', 'temp');

		$language = $this->language->get('temp');

		if ($this->user->hasPermission('access', 'localisation/sxgeo')) {
			$active = strpos($route, 'localisation/sxgeo') === 0;

			$menu = array(
				'name'	   => $language->get('heading_title'),
				'href'     => $this->url->link('localisation/sxgeo'),
				'children' => array(),
				'active'   => $active
			);

			foreach ($data['menus'] as &$value) {
				if ($value['id'] == 'menu-system') {
					foreach ($value['children'] as &$child) {
						if (isset($child['id']) && $child['id'] == 'menu-localisation') {
							$child['children'][] = $menu;

							if ($active)
								$child['active'] = true;

							break;
						}
					}

					if ($active)
						$value['active'] = true;
				}
			}
			unset($value);
		}
	}
}
