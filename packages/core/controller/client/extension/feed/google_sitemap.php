<?php
namespace Controller\Extension\Feed;

class GoogleSitemap extends \Controller {
	public function index() {
		$output  = '<?xml version="1.0" encoding="UTF-8"?>';
		$output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:xhtml="http://www.w3.org/1999/xhtml">';

		$this->load->model('localisation/language');

		$languages = $this->model_localisation_language->getLanguages();
		$multi_language = count($languages) > 1;

		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		$products = $this->model_catalog_product->getProducts();

		foreach ($products as $product) {
			$output .= '<url>';
			$output .= '  <loc>' . $this->url->link('catalog/product', 'catalog_product_id=' . $product['product_id']) . '</loc>';
			if ($multi_language) {
				foreach ($languages as $language)
					$output .= '  <xhtml:link rel="alternate" hreflang="' . $language['code'] . '" href="' . $this->url->link('catalog/product', 'language=' . $language['code'] . '&catalog_product_id=' . $product['product_id']) . '"/>';
			}
			$output .= '  <changefreq>weekly</changefreq>';
			$output .= '  <lastmod>' . date('Y-m-d\TH:i:sP', strtotime($product['date_modified'])) . '</lastmod>';
			$output .= '  <priority>1.0</priority>';

			$image = $this->model_catalog_product->getProductImageMain($product['product_id']);
			if ($image) {
				$output .= '  <image:image>';
				$output .= '  <image:loc>' . HTTP_SERVER . $this->model_tool_image->link($image['image']) . '</image:loc>';
				$output .= '  <image:caption>' . $product['name'] . '</image:caption>';
				$output .= '  <image:title>' . $product['name'] . '</image:title>';
				$output .= '  </image:image>';
			}
			$output .= '</url>';
		}

		$this->load->model('catalog/category');

		$categories = $this->model_catalog_category->getCategories();

		foreach ($categories as $category) {
			$output .= '<url>';
			$output .= '  <loc>' . $this->url->link('catalog/category', 'catalog_category_id=' . $category['category_id']) . '</loc>';
			if ($multi_language) {
				foreach ($languages as $language)
					$output .= '  <xhtml:link rel="alternate" hreflang="' . $language['code'] . '" href="' . $this->url->link('catalog/category', 'language=' . $language['code'] . '&catalog_category_id=' . $category['category_id']) . '"/>';
			}
			$output .= '  <changefreq>weekly</changefreq>';
			$output .= '  <priority>0.7</priority>';
			$output .= '</url>';
		}

		$this->load->model('info/category');

		$categories = $this->model_info_category->getCategories();

		foreach ($categories as $category) {
			$output .= '<url>';
			$output .= '  <loc>' . $this->url->link('info/category', 'info_category_id=' . $category['category_id']) . '</loc>';
			if ($multi_language) {
				foreach ($languages as $language)
					$output .= '  <xhtml:link rel="alternate" hreflang="' . $language['code'] . '" href="' . $this->url->link('info/category', 'language=' . $language['code'] . '&info_category_id=' . $category['category_id']) . '"/>';
			}
			$output .= '  <changefreq>weekly</changefreq>';
			$output .= '  <priority>0.5</priority>';
			$output .= '</url>';
		}

		$this->load->model('info/article');

		$articles = $this->model_info_article->getArticles();

		foreach ($articles as $article) {
			$output .= '<url>';
			$output .= '  <loc>' . $this->url->link('info/article', 'info_article_id=' . $article['article_id']) . '</loc>';
			if ($multi_language) {
				foreach ($languages as $language)
					$output .= '  <xhtml:link rel="alternate" hreflang="' . $language['code'] . '" href="' . $this->url->link('info/article', 'language=' . $language['code'] . '&info_article_id=' . $article['article_id']) . '"/>';
			}
			$output .= '  <changefreq>weekly</changefreq>';
			$output .= '  <priority>0.5</priority>';
			$output .= '</url>';
		}

		$output .= '</urlset>';

		$this->response->addHeader('Content-Type: application/xml');
		$this->response->setOutput($output);
	}
}