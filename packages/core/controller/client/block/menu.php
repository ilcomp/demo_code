<?php
namespace Controller\Block;

class Menu extends \Controller {
	public function index() {
		if (!$this->config->get('cache_menu')) {
			$this->load->language('block/menu');

			$this->load->model('design/menu');

			$data['menus'] = array();

			$menus = $this->model_design_menu->getMenus();

			foreach ($menus as $menu) {
				$menu_items = $this->model_design_menu->getMenuItemsByLanguage($menu['menu_id']);

				$menu_items = $this->get_menu_items($menu_items);

				$data['menus'][$menu['position']] = array(
					'title'		=> $menu['name'],
					'position'	=> $menu['position'],
					'setting'	=> $menu['setting'],
					'menu_items'=> $menu_items
				);

				$data['menus'][$menu['position']]['view'] = $this->load->view('block/menu', $data['menus'][$menu['position']]);
			}
			ksort($data['menus']);

			$this->config->set('cache_menu', $data['menus']);
		}

		return $this->config->get('cache_menu');
	}

	public function get_menu($menu_id) {
		if (!$this->config->get('cache_menu_' . $menu_id)) {
			$this->load->language('block/menu');

			$this->load->model('design/menu');

			$menu = $this->model_design_menu->getMenu($menu_id);

			if ($menu) {
				$menu['menu_items'] = $this->model_design_menu->getMenuItemsByLanguage($menu['menu_id']);

				$menu['menu_items'] = $this->get_menu_items($menu['menu_items']);

				$menu['view'] = $this->load->view('block/menu', $menu);
			}

			$this->config->set('cache_menu_' . $menu_id, $menu);
		}

		return $this->config->get('cache_menu_' . $menu_id);
	}

	public function get_menu_items($menu_items) {
		foreach ($menu_items as &$menu_item) {
			$menu_item['title'] = html_entity_decode($menu_item['title'], ENT_QUOTES, 'UTF-8');
			$menu_item['submenu'] = '';

			// Href
			$url_info = parse_url(str_replace('&amp;', '&', $menu_item['link']));

			if (!empty($url_info['query']))
				parse_str($url_info['query'], $menu_item['get']);
			else
				$menu_item['get'] = array();

			if (isset($menu_item['get']['route'])) {
				$url = $menu_item['get'];
				unset($url['route']);

				$menu_item['href'] = $this->url->link($menu_item['get']['route'], $url);
			} else {
				$menu_item['href'] = $menu_item['link'];
			}

			// Active
			$url = $this->request->get;
			unset($url['route']);
			unset($url['_route_']);

			$route = isset($this->request->get['route']) ? $this->request->get['route'] : $this->config->get('action_default');

			$menu_item['active'] = $menu_item['href'] == $this->url->link($route, $url);

			// Extension
			if ($this->config->get('menu_' . $menu_item['code'] . '_status')) {
				$result = (array)$this->load->controller('extension/menu/' . $menu_item['code'], $menu_item);

				$menu_item = array_merge($menu_item, $result);
			}

			// Fragment
			if (!empty($url_info['fragment']))
				$menu_item['href'] .= '#' . $url_info['fragment'];
		}
		unset($menu_item);

		return $menu_items;
	}
}