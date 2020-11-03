<?php
namespace Controller\Event;

class Info extends \Controller {
	public function startup() {
		if (isset($this->request->get['route']) && $this->config->get('info_home_page') && $this->request->get['route'] == 'common/home')
			$this->request->get['info_article_id'] = $this->config->get('info_home_page');
		if (isset($this->request->get['route']) && $this->config->get('info_contact_page') && $this->request->get['route'] == 'common/contact')
			$this->request->get['info_article_id'] = $this->config->get('info_contact_page');

		$this->event->register('model/design/seo_url/getSeoUrlsByQuery/before', new \Action('event/info/getSeoUrlsByQuery'), 0);

		$this->event->register('controller/common/template/before', new \Action('event/info/template'), 0);
		$this->event->register('controller/common/search/before', new \Action('event/info/search'), -1);
		$this->event->register('controller/common/search/autocomplete/before', new \Action('event/info/search_autocomplete'), -1);

		$this->event->register('view/common/home/before', new \Action('event/info/home'), 0);
		$this->event->register('view/common/contact/before', new \Action('event/info/contact'), 0);

		$this->load->controller('event/info_permission');
	}

	public function getSeoUrlsByQuery($route, &$data) {
		if (preg_match('/info_article_id=(\d+)/', $data[0], $match)) {
			if ($match[1] == $this->config->get('info_home_page'))
				$data[0] = 'route=common/home';
			elseif ($match[1] == $this->config->get('info_contact_page'))
				$data[0] = 'route=common/contact';
		}
	}

	public function template($route, &$data) {
		if (isset($this->request->get['route']) && $this->request->get['route'] == 'info/category' && isset($this->request->get['info_category_id'])) {
			$data[1]['user_route'] = 'info/category/edit';
			$data[1]['user_args'] = 'category_id=' . $this->request->get['info_category_id'];
		}

		if (isset($this->request->get['route']) && $this->request->get['route'] == 'info/article' && isset($this->request->get['article_id'])) {
			$data[1]['user_route'] = 'info/article/edit';
			$data[1]['user_args'] = 'article_id=' . $this->request->get['article_id'];
		}
	}

	public function home($route, &$data, $output) {
		if ($this->config->get('info_home_page')) {
			$this->load->model('info/article');

			$info_article = $this->model_info_article->getArticle($this->config->get('info_home_page'));

			if ($info_article) {
				$this->document->setTitle($info_article['meta_title'] ? $info_article['meta_title'] : $info_article['name']);
				$this->document->setDescription($info_article['meta_description']);

				$data['info'] = $info_article;

				$data['article_id'] = $info_article['article_id'];
				$data['name'] = $info_article['name'];
				$data['title'] = $info_article['title'];
				$data['heading_title'] = $info_article['title'] ? $info_article['title'] : $info_article['name'];
				$data['category_id'] = 0;

				$this->load->model('core/custom_field');

				$data['custom_fields'] = array();

				$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('info_home');
				$custom_field_values = $this->model_info_article->getArticleCustomFields($info_article['article_id']);

				foreach ($custom_fields as $custom_field) {
					$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

					if (isset($custom_field_values[$custom_field['custom_field_id']]) && isset($custom_field_values[$custom_field['custom_field_id']][$language_id])) {
						$value = $custom_field_values[$custom_field['custom_field_id']][$language_id];
					} elseif (isset($custom_field['setting']['default']))
						$value = $custom_field['setting']['default'];
					else
						$value = '';

					$data['custom_fields'][$custom_field['code']] = $this->model_core_custom_field->getAsValue($value, $custom_field);
				}
			}
		}
	}

	public function contact($route, &$data, $output) {
		if ($this->config->get('info_contact_page')) {
			$this->load->model('info/article');

			$info_article = $this->model_info_article->getArticle($this->config->get('info_contact_page'));

			if ($info_article) {
				$this->document->setTitle($info_article['meta_title'] ? $info_article['meta_title'] : $info_article['name']);
				$this->document->setDescription($info_article['meta_description']);

				$data['info'] = $info_article;

				$data['article_id'] = $info_article['article_id'];
				$data['name'] = $info_article['name'];
				$data['title'] = $info_article['title'];
				$data['heading_title'] = $info_article['title'] ? $info_article['title'] : $info_article['name'];
				$data['category_id'] = 0;

				$this->load->model('core/custom_field');

				$data['custom_fields'] = array();

				$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('info_contact');
				$custom_field_values = $this->model_info_article->getArticleCustomFields($info_article['article_id']);

				foreach ($custom_fields as $custom_field) {
					$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

					if (isset($custom_field_values[$custom_field['custom_field_id']]) && isset($custom_field_values[$custom_field['custom_field_id']][$language_id])) {
						$value = $custom_field_values[$custom_field['custom_field_id']][$language_id];
					} elseif (isset($custom_field['setting']['default']))
						$value = $custom_field['setting']['default'];
					else
						$value = '';

					$data['custom_fields'][$custom_field['code']] = $this->model_core_custom_field->getAsValue($value, $custom_field);
				}
			}
		}
	}

	public function search($route, &$args) {
		if (isset($this->request->get['search']) && preg_replace('/[\s]/', '', $this->request->get['search'])) {
			$search = $this->request->get['search'];
		} else {
			$search = '';
		}

		if ($search) {
			if (isset($this->request->get['page'])) {
				$page = $this->request->get['page'];
			} else {
				$page = 1;
			}

			if ($this->config->get('theme_' . $this->config->get('config_theme') . '_search_limit')) {
				$limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_search_limit');
			} elseif ($this->config->get('config_limit')) {
				$limit = (int)$this->config->get('config_limit');
			} else {
				$limit = 12;
			}

			if (!isset($args[0]['total']))
				$args[0]['total'] = 0;

			if (!isset($args[0]['count']))
				$args[0]['count'] = 0;

			$this->load->model('info/article');

			$filter_data = array(
				'filter' => array('search' => $search),
				'sort'   => 'name',
				'order'  => 'ASC',
				'start'  => ($page - 1) * $limit - $args[0]['total'] + $args[0]['count'],
				'limit'  => $limit - $args[0]['count']
			);

			if ($limit - $args[0]['count'] > 0) {
				$articles = $this->model_info_article->getArticles($filter_data);

				$args[0]['count'] += count($articles);

				$args[0]['articles'] = $this->load->controller('info/article_list', array('articles' => $articles));
			}

			$args[0]['total'] += $this->model_info_article->getTotalArticles($filter_data);
		}
	}

	public function search_autocomplete($route, &$args) {
		if (isset($this->request->get['search']) && preg_replace('/[\s]/', '', $this->request->get['search'])) {
			$search = $this->request->get['search'];
		} else {
			$search = '';
		}

		if ($search) {
			if ($this->config->get('theme_' . $this->config->get('config_theme') . '_search_limit')) {
				$limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_search_limit');
			} elseif ($this->config->get('config_limit')) {
				$limit = (int)$this->config->get('config_limit');
			} else {
				$limit = 12;
			}

			if (!isset($args[0]['search_results']))
				$args[0]['search_results'] = array();

			$count = count($args[0]['search_results']);

			if ($limit - $count > 0) {
				$this->load->model('info/article');

				$filter_data = array(
					'filter' => array('search' => $search),
					'sort'   => 'name',
					'order'  => 'ASC',
					'limit'  => $limit - $count
				);

				$articles = $this->model_info_article->getArticles($filter_data);

				foreach ($articles as $article) {
					if (empty($article['title']))
						$article['title'] = $article['name'];

					$article['href'] = $this->url->link('info/article', 'info_article_id=' . $article['article_id']);

					$args[0]['search_results'][] = $article;
				}
			}
		}
	}
}