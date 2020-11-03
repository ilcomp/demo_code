<?php
namespace Controller\Block;

class Menu extends \Controller {
	public function index() {
		$this->load->language('block/menu');

		$menu_items = array();

		$route = isset($this->request->get['route']) ? $this->request->get['route'] : false;

		$data['menus'] = array();

		$menu_items = array();

		$route = isset($this->request->get['route']) ? $this->request->get['route'] : false;

		// Dashboard
		$menu_items[] = array(
			'icon'     => 'os-icon os-icon-bar-chart-stats-up',
			'title'    => $this->language->get('text_dashboard'),
			'href'     => $this->url->link('common/dashboard'),
			'active'   => $route == 'common/dashboard',
			'submenu'  => ''
		);


			// foreach ($results as $result) {
			// 	if ($this->config->get('menu_' . $result['code'] . '_status')) {
			// 		$result['title'] = html_entity_decode($result['title'], ENT_QUOTES, 'UTF-8');
			// 		$result['link'] = str_replace('&amp;', '&', $result['link']);

			// 		if (preg_match('/^\?(.*?&)?route=(.+?)(&.*?)?(#.*)?$/', $result['link'], $match)) {
			// 			$implode = array(trim($match[1], '&'));
			// 			if (isset($match[3]))
			// 				$implode[] = trim($match[3], '&');

			// 			$result['href'] = $this->url->link($match[2], implode('&', $implode)) . (isset($match[4]) ? $match[4] : '');
			// 		} else {
			// 			$result['href'] = $result['link'];
			// 		}

			// 		$result['setting'] = $this->load->controller('extension/menu/' . $result['code'], $result['setting']);

			// 		if (isset($result['setting']['submenu'])) {
			// 			$result['submenu'] = $result['setting']['submenu'];

			// 			unset($result['setting']['submenu']);
			// 		} else {
			// 			$result['submenu'] = '';
			// 		}

			// 		$menu_items[] = $result;
			// 	}
			// }

		$data['menus']['general'] = array(
			'title'		=> $this->language->get('text_navigation'),
			'position'	=> 'general',
			'setting'	=> array(),
			'menu_items'=> $menu_items
		);

		$data['menus']['general']['view'] = $this->load->view('block/menu', $data['menus']['general']);

		return $data['menus'];
	}
}
