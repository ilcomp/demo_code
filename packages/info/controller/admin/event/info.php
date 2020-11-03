<?php
namespace Controller\Event;

class Info extends \Controller {
	public function startup() {
		$this->event->register('view/block/column_left/before', new \Action('event/info/menu'), 0);
		$this->event->register('view/setting/custom_field/*_setting/before', new \Action('event/info/custom_field_setting'), 0);
		$this->event->register('view/setting/custom_field/*_render/before', new \Action('event/info/custom_field_render'), 0);

		$this->event->register('view/setting/custom_field_list/before', new \Action('event/info/custom_field_list'), 0);
		$this->event->register('model/core/custom_field/getLocations/after', new \Action('event/info/cf_getLocations'), 0);
		$this->event->register('model/core/custom_field/getTypes/after', new \Action('event/info/cf_getTypes'), 0);

		$this->load->controller('event/info_permission');
	}

	public function menu($route, &$data) {
		$this->load->language('extension/system/info', 'temp');

		$language = $this->language->get('temp');

		$route = isset($this->request->get['route']) ? $this->request->get['route'] : $this->config->get('active_default');

		$info = array();
		$info_active = false;

		if ($this->user->hasPermission('access', 'info/category')) {
			if ($active = strpos($route, 'info/category') === 0) {
				$info_active = true;
			}

			$info[] = array(
				'name'	   => $language->get('menu_category'),
				'href'     => $this->url->link('info/category', 'user_token=' . $this->session->data['user_token']),
				'children' => array(),
				'active'   => $active
			);
		}

		if ($this->user->hasPermission('access', 'info/article')) {
			if ($active = strpos($route, 'info/article') === 0) {
				$info_active = true;
			}

			$info[] = array(
				'name'	   => $language->get('menu_article'),
				'href'     => $this->url->link('info/article', 'user_token=' . $this->session->data['user_token']),
				'children' => array(),
				'active'   => $active
			);

			$this->load->model('info/category');

			$categories = $this->model_info_category->getCategories(array('filter' => array('parent_id' => 0)));

			foreach ($categories as $category) {
				$info[(string)$category['category_id']] = array(
					'name'	   => $category['name'],
					'href'     => $this->url->link('info/article', 'user_token=' . $this->session->data['user_token'] . '&filter_category_id=' . $category['category_id']),
					'children' => array(),
					'active'   => $active && isset($this->request->get['filter_category_id']) && $this->request->get['filter_category_id'] == $category['category_id']
				);
			}

			$info["-1"] = array(
				'name'	   => $language->get('menu_static'),
				'href'     => $this->url->link('info/article', 'user_token=' . $this->session->data['user_token'] . '&filter_category_id=-1'),
				'children' => array(),
				'active'   => $active && isset($this->request->get['filter_category_id']) && $this->request->get['filter_category_id'] == -1
			);
		}

		if ($info) {
			$menu = array_shift($data['menus']);

			$data['menus'] = array_merge(array('info' => array(
				'id'       => 'menu-info',
				'icon'	   => 'fa-tags', 
				'name'	   => $language->get('menu_info'),
				'href'     => '',
				'children' => $info,
				'active'   => $info_active
			)), $data['menus']);

			array_unshift($data['menus'], $menu);
		}
	}

	public function custom_field_list($route, &$data) {
		$this->load->language('extension/system/info', 'temp');

		$language = $this->language->get('temp');

		$data['text_info_home'] = $language->get('menu_info') . ' > ' . $language->get('text_home');
		$data['text_info_contact'] = $language->get('menu_info') . ' > ' . $language->get('text_contact');
		$data['text_info_article'] = $language->get('menu_info') . ' > ' . $language->get('menu_static');
		$data['text_info_category'] = $language->get('menu_info') . ' > ' . $language->get('menu_category');

		$this->load->model('info/category');

		$categories = $this->model_info_category->getCategories(array('filter' => array('parent_id' => 0)));

		foreach ($categories as $category) {
			$data['text_info_article_' . $category['category_id']] = $language->get('menu_info') . ' > ' . $category['name'];
		}
	}

	public function cf_getLocations($route, $data, &$output) {
		$this->load->language('extension/system/info', 'temp');

		$language = $this->language->get('temp');

		$output['info'] = array(
			'label' => $language->get('menu_info'),
			'options' => array(
				array(
					'value' => 'info_home',
					'name' => $language->get('text_home'),
				),
				array(
					'value' => 'info_contact',
					'name' => $language->get('text_contact'),
				),
				array(
					'value' => 'info_article',
					'name' => $language->get('menu_static'),
				),
				array(
					'value' => 'info_category',
					'name' => $language->get('menu_info') . ' > ' . $language->get('menu_category'),
				),
			)
		);

		$this->load->model('info/category');

		$categories = $this->model_info_category->getCategories(array('filter' => array('parent_id' => 0)));

		foreach ($categories as $category) {
			$output['info']['options'][] = array(
				'value'    => 'info_article_' . $category['category_id'],
				'name'	   => $language->get('menu_info') . ' > ' . $category['name']
			);
		}
	}

	public function cf_getTypes($route, $data, &$output) {
		$this->load->language('extension/system/info', 'temp');

		$language = $this->language->get('temp');

		$output['link']['options'][] = array(
			'value' => 'info_article',
			'name' => $language->get('menu_info') . ' > ' . $language->get('menu_article'),
		);

		$output['link']['options'][] = array(
			'value' => 'info_category',
			'name' => $language->get('menu_info') . ' > ' . $language->get('menu_category'),
		);
	}
}
