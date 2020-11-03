<?php
namespace Controller\Event;

class InfoContent extends \Controller {
	public function startup() {
		$this->event->register('view/block/column_left/before', new \Action('event/info_content/menu'), 0);
		$this->event->register('view/setting/custom_field/*_setting/before', new \Action('event/info_content/custom_field_setting'), 0);
		$this->event->register('view/setting/custom_field/*_render/before', new \Action('event/info_content/custom_field_render'), 0);

		$this->event->register('view/setting/custom_field_list/before', new \Action('event/info_content/custom_field_list'), 0);
		$this->event->register('model/core/custom_field/getLocations/after', new \Action('event/info_content/cf_getLocations'), 0);
		$this->event->register('model/core/custom_field/getTypes/after', new \Action('event/info_content/cf_getTypes'), 0);

		$this->event->register('view/info/article_form/before', new \Action('event/info_content/view_article'), 0);
		$this->event->register('model/info/article_modify/*Article/after', new \Action('event/info_content/model_article'), 0);
	}

	public function menu($route, &$data) {
		$this->load->language('extension/system/info_content', 'temp');

		$language = $this->language->get('temp');

		$route = isset($this->request->get['route']) ? $this->request->get['route'] : $this->config->get('active_default');

		$info = array();
		$info_active = false;

		if ($this->user->hasPermission('access', 'info/content')) {
			if ($active = strpos($route, 'info/content') === 0) {
				$info_active = true;
			}

			$this->load->model('info/content');

			$contents = $this->model_info_content->getArticleContents();

			foreach ($contents as $content) {
				$info[(string)$content['article_id']] = array(
					'name'	   => $content['name'],
					'href'     => $this->url->link('info/content', 'filter_article_id=' . $content['article_id']),
					'children' => array(),
					'active'   => $active && isset($this->request->get['filter_article_id']) && $this->request->get['filter_article_id'] == $content['article_id']
				);
			}

			foreach ($data['menus'] as &$value) {
				if ($value['id'] == 'menu-info') {
					$value['children'][] = array(
						'name'	   => $language->get('text_content'),
						'href'     => '',
						'children' => $info,
						'active'   => $info_active
					);

					if ($info_active)
						$value['active'] = true;

					break;
				}
			}
			unset($value);
		}
	}

	public function custom_field_list($route, &$data) {
		$this->load->language('extension/system/info_content', 'temp');

		$language = $this->language->get('temp');

		$data['text_info_content'] = $language->get('text_content') . ' > ' . $language->get('menu_content');

		$this->load->model('info/content');

		$contents = $this->model_info_content->getArticleContents();

		foreach ($contents as $content) {
			$data['text_info_content_' . $content['article_id']] = $language->get('text_content') . ' > ' . $content['name'];
		}
	}

	public function cf_getLocations($route, $data, &$output) {
		$this->load->language('extension/system/info_content', 'temp');

		$language = $this->language->get('temp');

		$output['content'] = array(
			'label' => $language->get('text_content'),
			'options' => array()
		);

		$this->load->model('info/content');

		$contents = $this->model_info_content->getArticleContents();

		foreach ($contents as $content) {
			$output['content']['options'][] = array(
				'value'    => 'info_content_' . $content['article_id'],
				'name'	   => $content['name']
			);
		}
	}

	public function view_article($route, &$data) {
		$this->load->model('info/content');

		$this->load->language('extension/system/info_content', 'temp');

		$data_group = $this->language->get('temp')->all();

		if (isset($this->request->post['content_status'])) {
			$data_group['content_status'] = (int)$this->request->post['content_status'];
		} elseif (isset($this->request->get['info_article_id'])) {
			$data_group['content_status'] = $this->model_info_content->getArticleContent($this->request->get['info_article_id']);
		} else {
			$data_group['content_status'] = 0;
		}

		$data['additional_fields'] .= $this->load->view('info/content_view', $data_group);
	}

	public function model_article($route, $data, $output = '') {
		if ((int)$output) {
			if ($route == 'info/article_modify/addArticle') {
				$this->load->model('info/content');

				if (!isset($data[0]))
					$data[0] = array();

				$this->model_info_content->updateArticleContent((int)$output, $data[0]);
			}
		} elseif (isset($data[0]) && (int)$data[0]) {
			if ($route == 'info/article_modify/editArticle') {
				$this->load->model('info/content');

				if (!isset($data[1]))
					$data[1] = array();

				$this->model_info_content->updateArticleContent((int)$data[0], $data[1]);
			} elseif ($route == 'info/article_modify/deleteArticle') {
				$this->load->model('info/content');

				$this->model_info_content->deleteArticleContent((int)$data[0]);
			}
		}
	}
}