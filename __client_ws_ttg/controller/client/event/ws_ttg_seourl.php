<?php
namespace Controller\Event;

class WSTTGSeoUrl extends \Controller {
	public function index() {
		$this->event->register('model/design/seo_url/getSeoUrlsByKeyword/after', new \Action('event/ws_ttg_seourl/getSeoUrlsByKeyword'), 0);
		$this->event->register('model/design/seo_url/getSeoUrlsByQuery/after', new \Action('event/ws_ttg_seourl/getSeoUrlsByQuery'), 0);

		$this->event->register('controller/startup/seo_url/redirect/before', new \Action('event/ws_ttg_seourl/seo_url_redirect'), 0);
	}

	public function getSeoUrlsByKeyword($route, $data, &$output) {
		if (empty($output)) {
			if ($data[0] == 'sitemap.xml') {
				$this->url->addRewrite($this);

				$output = array(array(
					'seo_url_id' => 0,
					'store_id' => $this->config->get('config_store_id'),
					'language_id' => $this->config->get('config_language_id'),
					'query' => 'route=extension/feed/google_sitemap',
					'keyword' => 'sitemap.xml',
					'push' => 'route=extension/feed/google_sitemap',
				));
			} elseif ($data[0] == 'catalog') {
				$output = array(array(
					'seo_url_id' => 0,
					'store_id' => $this->config->get('config_store_id'),
					'language_id' => $this->config->get('config_language_id'),
					'query' => 'route=catalog/category',
					'keyword' => 'catalog',
					'push' => 'route=catalog/category',
				));
			} elseif ($data[0] == 'product') {
				$output = array(array(
					'seo_url_id' => 0,
					'store_id' => $this->config->get('config_store_id'),
					'language_id' => $this->config->get('config_language_id'),
					'query' => 'route=catalog/product',
					'keyword' => 'product',
					'push' => 'route=catalog/product',
				));
			} elseif (isset($this->request->get['route']) && is_numeric($data[0])) {
				if ($this->request->get['route'] == 'catalog/category' && !isset($this->request->get['catalog_category_id'])) {
					$output = array(array(
						'seo_url_id' => 0,
						'store_id' => $this->config->get('config_store_id'),
						'language_id' => $this->config->get('config_language_id'),
						'query' => 'catalog_category_id=' . $data[0],
						'keyword' => $data[0],
						'push' => 'catalog_category_id=' . $data[0],
					));
				} elseif ($this->request->get['route'] == 'catalog/product' && !isset($this->request->get['catalog_product_id'])) {
					$output = array(array(
						'seo_url_id' => 0,
						'store_id' => $this->config->get('config_store_id'),
						'language_id' => $this->config->get('config_language_id'),
						'query' => 'catalog_product_id=' . $data[0],
						'keyword' => $data[0],
						'push' => 'catalog_product_id=' . $data[0],
					));
				} elseif ($this->request->get['route'] == 'info/category' && !isset($this->request->get['info_article_id'])) {
					$output = array(array(
						'seo_url_id' => 0,
						'store_id' => $this->config->get('config_store_id'),
						'language_id' => $this->config->get('config_language_id'),
						'query' => 'info_article_id=' . $data[0],
						'keyword' => $data[0],
						'push' => 'route=info/article&info_article_id=' . $data[0],
					));
				}
			}
		}
	}

	public function getSeoUrlsByQuery($route, $data, &$output) {
		if ($data[0] == 'route=extension/feed/google_sitemap') {
			$output = array(array(
				'store_id' => $this->config->get('config_store_id'),
				'language_id' => $this->config->get('config_language_id'),
				'query' => 'route=extension/feed/google_sitemap',
				'keyword' => 'sitemap.xml',
				'push' => 'route=extension/feed/google_sitemap',
			));
		} elseif (preg_match('/catalog_category_id=(\d+)/', $data[0], $match)) {
			if (empty($output))
				$output = array(array(
					'store_id' => $this->config->get('config_store_id'),
					'language_id' => $this->config->get('config_language_id'),
					'query' => $match[0],
					'keyword' => $match[1],
					'push' => $match[0]
				));

			array_unshift($output, array(
				'store_id' => $this->config->get('config_store_id'),
				'language_id' => $this->config->get('config_language_id'),
				'query' => 'route=catalog/category',
				'keyword' => 'catalog',
				'push' => 'route=catalog/category'
			));
		} elseif (preg_match('/catalog_product_id=(\d+)/', $data[0], $match)) {
			if (empty($output))
				$output = array(array(
					'store_id' => $this->config->get('config_store_id'),
					'language_id' => $this->config->get('config_language_id'),
					'query' => $match[0],
					'keyword' => $match[1],
					'push' => $match[0]
				));

			array_unshift($output, array(
				'store_id' => $this->config->get('config_store_id'),
				'language_id' => $this->config->get('config_language_id'),
				'query' => 'route=catalog/product',
				'keyword' => 'product',
				'push' => 'route=catalog/product'
			));
		} elseif (preg_match('/info_category_id=(\d+)/', $data[0], $match)) {
			if (empty($output))
				$output = array(array(
					'store_id' => $this->config->get('config_store_id'),
					'language_id' => $this->config->get('config_language_id'),
					'query' => $match[0],
					'keyword' => $match[1],
					'push' => $match[0]
				));

			$this->load->model('info/category');
			$this->load->model('design/seo_url');

			$paths = $this->model_info_category->getCategoryPathId($match[1]);
			$seo_url = count($paths) > 1 ? $this->model_design_seo_url->getSeoUrlsByQuery('info_category_id=' . $paths[0]) : '';

			if (!empty($seo_url)) {
				array_unshift($output, $seo_url[0]);
			}
		} elseif (preg_match('/info_article_id=(\d+)/', $data[0], $match)) {
			if (empty($output))
				$output = array(array(
					'store_id' => $this->config->get('config_store_id'),
					'language_id' => $this->config->get('config_language_id'),
					'query' => $match[0],
					'keyword' => $match[1],
					'push' => $match[0]
				));

			$this->load->model('info/article');
			$this->load->model('info/category');
			$this->load->model('design/seo_url');

			$category_id = $this->model_info_article->getArtcileCategoryIdMain($match[1]);
			$seo_url = !empty($category_id) ? $this->model_design_seo_url->getSeoUrlsByQuery('info_category_id=' . $category_id) : '';

			if (!empty($seo_url)) {
				array_unshift($output, $seo_url[0]);
			}
		}
	}

	public function rewrite($link) {
		return preg_replace('/sitemap\.xml\/$/', 'sitemap.xml', $link);
	}

	public function seo_url_redirect($route, $args) {
		if ($this->request->server['HTTP_ACCEPT'] != 'application/json' && $this->request->server['HTTP_ACCEPT'] != 'text/html') {
			if (isset($this->request->get['route']) && in_array($this->request->get['route'], array('account/order', 'account/edit', 'account/password'))) {
				$url = $this->request->get;
				unset($url['_route_']);
				unset($url['route']);

				$this->response->redirect($this->url->link('account/account', $url));
			}

			$url_info = parse_url($this->request->server['REQUEST_URI']);

			if (!empty($url_info['path']) && in_array(trim($url_info['path'], '/'), array('catalog', 'product'))) {
				$this->load->model('catalog/category');

				$categories = $this->model_catalog_category->getCategoriesCatalog();

				$category = array_shift($categories);

				$this->response->redirect($this->url->link('catalog/category', 'catalog_category_id=' . $category['category_id']));
			}
		}
	}
}