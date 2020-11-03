<?php
namespace Controller\Extension\Menu;

class InfoCategory extends \Controller {
	private $death = 0;
	private $active_filter = false;
	private $category_path = array();
	private $categories = array();

	public function index($menu_item) {
		if (isset($menu_item['get']['route']) && $menu_item['get']['route'] == 'info/category' && isset($menu_item['get']['info_category_id'])) {
			$active_route = isset($this->request->get['route']) && $this->request->get['route'] == 'info/category' && isset($this->request->get['info_category_id']);
			$active = $active_route && $this->request->get['info_category_id'] == $menu_item['get']['info_category_id'];

			$data['category_id'] = $menu_item['get']['info_category_id'];

			$this->load->model('info/category');

			if ($active_route)
				$this->category_path = $this->model_info_category->getCategoryPathId($this->request->get['info_category_id']);

			if (isset($menu_item['setting']['depth']) && $menu_item['setting']['depth'] != '')
				$this->death = (int)$menu_item['setting']['depth'];
			if (isset($menu_item['setting']['active_filter']))
				$this->active_filter = (int)$menu_item['setting']['active_filter'];

			if ($this->config->get('cache_menu_info_category'))
				$this->categories = (array)$this->config->get('cache_menu_info_category');

			$submenu = $this->submenu($menu_item['get']['info_category_id'], 0);

			$this->config->set('cache_menu_info_category', $this->categories);

			if (isset($submenu['submenu']))
				$data['submenu'] = $submenu['submenu'];

			if (isset($submenu['active']))
				$data['active'] = $submenu['active'];

			return $data;
		}
	}

	public function submenu($parent_id, $level) {
		if ($level < $this->death && (!$this->active_filter || in_array($parent_id, $this->category_path))) {
			$level++;

			if (!isset($this->categories[$parent_id])) {
				$this->categories[$parent_id] = $this->model_info_category->getCategories(array(
					'filter' => array('parent_id' => $parent_id)
				));
			}

			$menu_data = array(
				'position' => 'category_sub',
				'menu_items' => array()
			);

			$active = false;

			foreach ($this->categories[$parent_id] as $category) {
				if ($active_category = in_array($category['category_id'], $this->category_path)) {
					$active = true;
				}

				if (!$category['title'])
					$category['title'] = $category['name'];

				$submenu = $this->submenu($category['category_id'], $level);

				$menu_data['menu_items'][] = array(
					'title' => $category['title'],
					'active' => $active_category || $submenu['active'],
					'href' => $this->url->link('info/category', 'info_category_id=' . $category['category_id']),
					'submenu' => $submenu['submenu'],
					'category_id' => $category['category_id']
				);
			}

			return array(
				'active' => $active,
				'submenu' => !empty($menu_data['menu_items']) ? $this->load->view('block/menu', $menu_data) : ''
			);
		} else {
			return array(
				'active' => false,
				'submenu' => ''
			);
		}
	}
}