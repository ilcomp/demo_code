<?php
namespace Controller\Event;

class WSTTGInfo extends \Controller {
	public function index() {
		$this->event->register('view/block/menu/before', new \Action('event/ws_ttg_info/menu'), 0);
		$this->event->register('view/info/category/before', new \Action('event/ws_ttg_info/category'), 0);
		$this->event->register('view/info/article_list/before', new \Action('event/ws_ttg_info/article_list'), 0);
		$this->event->register('view/info/article/before', new \Action('event/ws_ttg_info/article'), 100);
		$this->event->register('view/common/contact/before', new \Action('event/ws_ttg_info/article'), 100);
		$this->event->register('view/common/home/before', new \Action('event/ws_ttg_info/article'), 100);
	}

	public function menu($route, &$data) {
		if (strpos($data['position'], 'info') === 0) {
			$this->load->model('info/article');
			$this->load->model('info/category');
			$this->load->model('core/custom_field');

			$custom_fields_article = $this->model_core_custom_field->getCustomFieldsByLocation('info_article');
			$custom_fields_category = $this->model_core_custom_field->getCustomFieldsByLocation('info_category');

			foreach ($data['menu_items'] as &$menu_item) {
				$menu_item['icon'] = '';

				if (!empty($menu_item['article_id'])) {
					foreach ($custom_fields_article as $custom_field) {
						if ($custom_field['code'] == 'icon') {
							$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

							$value = $this->model_info_article->getArticleCustomField($menu_item['article_id'], $custom_field['custom_field_id'], $language_id);

							$menu_item['icon'] = $this->model_core_custom_field->getAsValue($value, $custom_field);
						}
					}
				} elseif (!empty($menu_item['category_id'])) {
					foreach ($custom_fields_category as $custom_field) {
						if ($custom_field['code'] == 'icon') {
							$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

							$value = $this->model_info_category->getCategoryCustomField($menu_item['category_id'], $custom_field['custom_field_id'], $language_id);

							$menu_item['icon'] = $this->model_core_custom_field->getAsValue($value, $custom_field);
						}
					}					
				}
			}
			unset($menu_item);
		}
	}

	public function category($route, &$data) {
		if (isset($this->request->get['info_category_id'])) {
			$this->load->model('info/category');

			$categories = $this->model_info_category->getCategoryPath($this->request->get['info_category_id']);

			$category = array_shift($categories);

			if ($category) {
				$categories = $this->model_info_category->getCategories(array(
					'filter' => array(
						'parent_id' => $category['path_id']
					)
				));

				if (!empty($categories))
					array_unshift($categories, $this->model_info_category->getCategory($category['path_id']));
			} else {
				$categories = array();
			}

			if (!empty($categories)) {
				$menu_data['menu_items'] = array();

				$submenu = array(
					'position' => 'category_sub',
					'menu_items' => array()
				);

				$menu_item['get']['route'] = 'info/category';
				$menu_item['setting']['depth'] = 0;
				$menu_item['setting']['active_filter'] = true;

				foreach ($categories as $category) {
					$menu_data['menu_items'][] = array(
						'title' => $category['title'] ? $category['title'] : $category['name'],
						'active' => $category['category_id'] == $this->request->get['info_category_id'],
						'href' => $this->url->link('info/category', 'info_category_id=' . $category['category_id']),
						'route' => 'info/category',
						'category_id' => $category['category_id'],
						'submenu' => ''
					);
				}

				if (!empty($menu_data['menu_items'])) {
					$menu_data['title'] = '';
					$menu_data['position'] = 'info_category';
					$menu_data['setting'] = array();
					$menu_data['name'] = $this->language->get('text_menu_news');

					$menu_data['view'] = !empty($menu_data['menu_items']) ? $this->load->view('block/menu', $menu_data) : '';

					$data['custom_fields']['menu'] = $menu_data;
				}
			}
		}
	}

	public function article_list($route, &$data) {
		if (isset($this->request->get['info_category_id'])) {
			$this->load->model('info/article');
			$this->load->model('info/category');
			$this->load->model('core/custom_field');

			$categories = $this->model_info_category->getCategoryPath($this->request->get['info_category_id']);

			$category = array_shift($categories);

			if ($category) {
				$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('info_article_' . $category['path_id']);

				foreach ($data['articles'] as &$article) {
					$article['custom_fields'] = array();

					$custom_field_values = $this->model_info_article->getArticleCustomFields($article['article_id']);

					$article['custom_field_codes'] = array_column($custom_fields, 'code');

					foreach ($custom_fields as $custom_field) {
						$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

						if (isset($custom_field_values[$custom_field['custom_field_id']]) && isset($custom_field_values[$custom_field['custom_field_id']][$language_id])) {
							$value = $custom_field_values[$custom_field['custom_field_id']][$language_id];
						} elseif (isset($custom_field['setting']['default']))
							$value = $custom_field['setting']['default'];
						else
							$value = '';

						$article['custom_fields'][$custom_field['code']] = $this->model_core_custom_field->getAsValue($value, $custom_field);
					}
				}
				unset($article);
			}
		} elseif (isset($this->request->get['search'])) {
			$this->load->model('info/article');
			$this->load->model('core/custom_field');

			foreach ($data['articles'] as &$article) {
				$category_id = $this->model_info_article->getArtcileCategoryIdMain($article['article_id']);

				if ($category_id) {
					$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('info_article_' . $category_id);

					$article['custom_field_codes'] = array_column($custom_fields, 'code');
				} else {
					$article['date_added'] = '';
				}
			}
			unset($article);
		}
	}

	public function article($route, &$data) {
		if (isset($this->request->get['route']) && $this->request->get['route'] == 'common/home') {
			$this->load->model('design/menu');

			$data['menu']['social'] = $this->load->controller('block/menu/get_menu', $this->model_design_menu->getMenuIdByPosition('social'));

			return;
		}

		$this->document->addMeta('og:title', $data['title'] ?  $data['title'] :  $data['name'], 1);
		$this->document->addMeta('og:type', 'website', 1);
		$this->document->addMeta('og:url', $this->url->link('info/article', 'info_article_id=' . $this->request->get['info_article_id']), 1);
		if (!empty($data['custom_fields']['image_preview']) && !empty($data['custom_fields']['image_preview']['image']))
			$this->document->addMeta('og:image', $data['custom_fields']['image_preview']['thumb'], 1);
// Modeles
		$modules = (array)$this->config->get('theme_client_ws_ttg_modules');

		$templates = array_column($modules, 'template');

		if (!isset($data['modules']))
			$data['modules'] = array();

		if (isset($data['category_id']) && in_array('info/article_' . $data['category_id'], $templates)) {
			$this->load->model('core/module');

			foreach ($modules as $module) {
				if ($module['template'] == 'info/article_' . $data['category_id']) {
					if (!isset($data['modules'][$module['code']]))
						$data['modules'][$module['code']] = '';

					$part = explode('.', $module['module']);

					if (isset($part[0]) && $this->config->get('module_' . $part[0] . '_status')) {
						$module_data = $this->load->controller('extension/module/' . $part[0]);

						$data['modules'][$module['code']] .= $module_data;
					}

					if (isset($part[1])) {
						$setting_info = $this->model_core_module->getModule($part[1]);

						if ($setting_info && $setting_info['status']) {
							$output = $this->load->controller('extension/module/' . $part[0], $setting_info);

							$data['modules'][$module['code']] .= $output;
						}
					}
				}
			}
		}
// Custom Fields
		if (empty($data['custom_fields']['menu'])) {
			$this->load->model('info/category');
			$this->load->model('core/custom_field');

			$custom_fields_category = $this->model_core_custom_field->getCustomFieldsByLocation('info_category');

			foreach ($custom_fields_category as $custom_field) {
				if ($custom_field['code'] == 'menu') {
					$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

					$value = $this->model_info_category->getCategoryCustomField($data['category_id'], $custom_field['custom_field_id'], $language_id);

					if ($value)
						$data['custom_fields']['menu'] = $this->model_core_custom_field->getAsValue($value, $custom_field);
				}
			}
		}
// Menu Category
		if (empty($data['custom_fields']['menu'])) {
			$categories = $this->model_info_category->getCategoryPath($data['category_id']);

			$category = array_shift($categories);

			if ($category) {
				$categories = $this->model_info_category->getCategories(array(
					'filter' => array(
						'parent_id' => $category['path_id']
					)
				));

				if (!empty($categories))
					array_unshift($categories, $this->model_info_category->getCategory($category['path_id']));
			} else {
				$categories = array();
			}

			if (!empty($categories)) {
				$menu_data['menu_items'] = array();

				$submenu = array(
					'position' => 'category_sub',
					'menu_items' => array()
				);

				$menu_item['get']['route'] = 'info/category';
				$menu_item['setting']['depth'] = 0;
				$menu_item['setting']['active_filter'] = true;

				foreach ($categories as $category) {
					$menu_data['menu_items'][] = array(
						'title' => $category['title'] ? $category['title'] : $category['name'],
						'active' => $category['category_id'] == $data['category_id'],
						'href' => $this->url->link('info/category', 'info_category_id=' . $category['category_id']),
						'route' => 'info/category',
						'category_id' => $category['category_id'],
						'submenu' => ''
					);
				}

				if (!empty($menu_data['menu_items'])) {
					$menu_data['title'] = '';
					$menu_data['position'] = 'info_category';
					$menu_data['setting'] = array();
					$menu_data['name'] = $this->language->get('text_menu_news');

					$menu_data['view'] = !empty($menu_data['menu_items']) ? $this->load->view('block/menu', $menu_data) : '';

					$data['custom_fields']['menu'] = $menu_data;
				}
			}

			if (isset($this->request->get['route']) && $this->request->get['route'] == 'common/home') {
				$menu = $this->load->controller('block/menu');

				$data['menu']['social'] = $menu['social'];
			}
		}
	}
}