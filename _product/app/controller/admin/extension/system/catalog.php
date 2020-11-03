<?php
namespace Controller\Extension\System;

class Catalog extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('setting/setting');
		$this->load->language('extension/system/catalog');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_core_setting->editSetting('catalog', $this->request->post, $this->request->get['store_id']);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/system', 'user_token=' . $this->session->data['user_token']));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/system', 'user_token=' . $this->session->data['user_token'])
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/system/catalog', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $this->request->get['store_id'])
		);

		$data['action'] = $this->url->link('extension/system/catalog', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $this->request->get['store_id']);

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('marketplace/system', 'user_token=' . $this->session->data['user_token']);

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->get['store_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$setting_info = $this->model_core_setting->getSetting('catalog', $this->request->get['store_id']);
		}

		if (isset($this->request->post['catalog_status'])) {
			$data['catalog_status'] = $this->request->post['catalog_status'];
		} elseif (isset($setting_info['catalog_status'])) {
			$data['catalog_status'] = $setting_info['catalog_status'];
		} else {
			$data['catalog_status'] = 0;
		}

		if (isset($this->request->post['catalog_category_general'])) {
			$data['catalog_category_general'] = $this->request->post['catalog_category_general'];
		} elseif (isset($setting_info['catalog_category_general'])) {
			$data['catalog_category_general'] = $setting_info['catalog_category_general'];
		} else {
			$data['catalog_category_general'] = 0;
		}

		if (isset($this->request->post['catalog_category_manufacturer'])) {
			$data['catalog_category_manufacturer'] = $this->request->post['catalog_category_manufacturer'];
		} elseif (isset($setting_info['catalog_category_manufacturer'])) {
			$data['catalog_category_manufacturer'] = $setting_info['catalog_category_manufacturer'];
		} else {
			$data['catalog_category_manufacturer'] = 0;
		}

		if (isset($this->request->post['catalog_currency'])) {
			$data['catalog_currency'] = $this->request->post['catalog_currency'];
		} elseif (isset($setting_info['catalog_currency'])) {
			$data['catalog_currency'] = $setting_info['catalog_currency'];
		} else {
			$data['catalog_currency'] = '';
		}

		if (isset($this->request->post['catalog_product_limit'])) {
			$data['catalog_product_limit'] = $this->request->post['catalog_product_limit'];
		} elseif (isset($setting_info['catalog_product_limit'])) {
			$data['catalog_product_limit'] = $setting_info['catalog_product_limit'];
		} else {
			$data['catalog_product_limit'] = 25;
		}

		if (isset($this->request->post['catalog_product_sub_category'])) {
			$data['catalog_product_sub_category'] = $this->request->post['catalog_product_sub_category'];
		} elseif (isset($setting_info['catalog_product_sub_category'])) {
			$data['catalog_product_sub_category'] = $setting_info['catalog_product_sub_category'];
		} else {
			$data['catalog_product_sub_category'] = 0;
		}

		if (isset($this->request->post['catalog_product_description_length'])) {
			$data['catalog_product_description_length'] = $this->request->post['catalog_product_description_length'];
		} elseif (isset($setting_info['catalog_product_description_length'])) {
			$data['catalog_product_description_length'] = $setting_info['catalog_product_description_length'];
		} else {
			$data['catalog_product_description_length'] = 100;
		}

		if (isset($this->request->post['catalog_currency_engine'])) {
			$data['catalog_currency_engine'] = $this->request->post['catalog_currency_engine'];
		} elseif (isset($setting_info['catalog_currency_engine'])) {
			$data['catalog_currency_engine'] = $setting_info['catalog_currency_engine'];
		} else {
			$data['catalog_currency_engine'] = '';
		}

		if (isset($this->request->post['catalog_currency_auto'])) {
			$data['catalog_currency_auto'] = $this->request->post['catalog_currency_auto'];
		} elseif (isset($setting_info['catalog_currency_auto'])) {
			$data['catalog_currency_auto'] = $setting_info['catalog_currency_auto'];
		} else {
			$data['catalog_currency_auto'] = 0;
		}

		if (isset($this->request->post['catalog_path_status'])) {
			$data['catalog_path_status'] = $this->request->post['catalog_path_status'];
		} elseif (isset($setting_info['catalog_path_status'])) {
			$data['catalog_path_status'] = $setting_info['catalog_path_status'];
		} else {
			$data['catalog_path_status'] = 1;
		}

		if (isset($this->request->post['catalog_product_default_image'])) {
			$data['catalog_product_default_image'] = $this->request->post['catalog_product_default_image'];
		} elseif (isset($setting_info['catalog_product_default_image'])) {
			$data['catalog_product_default_image'] = $setting_info['catalog_product_default_image'];
		} else {
			$data['catalog_product_default_image'] = '';
		}

		$images_name = array('list', 'thumb', 'popup', 'additional', 'related', 'compare', 'wishlist');

		$data['catalog_image'] = array();

		foreach ($images_name as $value) {
			$data['catalog_image'][$value]['name'] = $this->language->get('entry_image_' . $value);

			if (isset($this->request->post['catalog_image_' . $value . '_width'])) {
				$data['catalog_image'][$value]['width'] = $this->request->post['catalog_image_' . $value . '_width'];
			} elseif (isset($setting_info['catalog_image_' . $value . '_width'])) {
				$data['catalog_image'][$value]['width'] = $setting_info['catalog_image_' . $value . '_width'];
			} else {
				$data['catalog_image'][$value]['width'] = 300;
			}

			if (isset($this->request->post['catalog_image_' . $value . '_height'])) {
				$data['catalog_image'][$value]['height'] = $this->request->post['catalog_image_' . $value . '_height'];
			} elseif (isset($setting_info['catalog_image_' . $value . '_height'])) {
				$data['catalog_image'][$value]['height'] = $setting_info['catalog_image_' . $value . '_height'];
			} else {
				$data['catalog_image'][$value]['height'] = 300;
			}
		}

		$this->load->model('tool/image');

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', $this->config->get('admin_image_thumb_width'), $this->config->get('admin_image_thumb_height'));

		if ($data['catalog_product_default_image']) {
			$data['thumb'] = $this->model_tool_image->resize($data['catalog_product_default_image'], $this->config->get('admin_image_thumb_width'), $this->config->get('admin_image_thumb_height'));
		} else {
			$data['thumb'] = $data['placeholder'];
		}

		$this->load->model('catalog/category');

		$data['categories'] = $this->model_catalog_category->getCategories(array('filter' => array(
			'parent_id' => 0
		)));

		$this->load->model('localisation/currency');

		$data['currencies'] = $this->model_localisation_currency->getCurrencies();

		$this->load->model('core/extension');

		$extensions = $this->model_core_extension->getInstalled('currency');

		$data['currency_engines'] = array();

		foreach ($extensions as $code) {
			if ($this->config->get('currency_' . $code . '_status')) {
				$this->load->language('extension/currency/' . $code, 'extension');

				$data['currency_engines'][] = array(
					'text'  => $this->language->get('extension')->get('heading_title'),
					'value' => $code
				);
			}
		}

		$data['content'] = $this->load->view('extension/system/catalog', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	public function install() {
		$table = new \Model\DBTable($this->registry);
		$table->create('catalog');
		$table->create('catalog_category');
		$table->create('catalog_product');

		$this->load->model('core/event');
		$this->model_core_event->addEvent('catalog', 'api/controller/startup/event/after', 'event/catalog/startup');
		$this->model_core_event->addEvent('catalog', 'admin/controller/startup/event/after', 'event/catalog/startup');
		$this->model_core_event->addEvent('catalog', 'client/controller/startup/event/after', 'event/catalog/startup');

		$this->load->model('design/seo_regex');
		$this->model_design_seo_regex->addSeoRegex(array('name' => 'Catalog Path', 'regex' => '(catalog_path=[\d_])(?:[_&]|$)', 'sort_order' => 1));
		$this->model_design_seo_regex->addSeoRegex(array('name' => 'Catalog Path', 'regex' => '(catalog_path=)', 'sort_order' => 1));
		$this->model_design_seo_regex->addSeoRegex(array('name' => 'Catalog Category', 'regex' => '(catalog_category_id=)', 'sort_order' => 99));
		$this->model_design_seo_regex->addSeoRegex(array('name' => 'Catalog Product', 'regex' => '(catalog_product_id=)', 'sort_order' => 99));
		$this->model_design_seo_regex->addSeoRegex(array('name' => 'Catalog Product', 'regex' => '(catalog_product_id=\d+)(?:[&]|$)', 'sort_order' => 100));
		$this->model_design_seo_regex->addSeoRegex(array('name' => 'Catalog Category', 'regex' => '(catalog_category_id=\d+)(?:[&]|$)', 'sort_order' => 100));

		$this->load->model('design/seo_url');
		$this->model_design_seo_url->addSeoUrl(array('store_id' => 0, 'language_id' => 1, 'query' => 'route=catalog/category', 'keyword' => '', 'push' => ''));
		$this->model_design_seo_url->addSeoUrl(array('store_id' => 0, 'language_id' => 1, 'query' => 'route=catalog/product', 'keyword' => '', 'push' => ''));
	}

	public function uninstall() {
		$table = new \Model\DBTable($this->registry);
		$table->delete('catalog');
		$table->delete('catalog_category');
		$table->delete('catalog_product');

		$this->load->model('core/event');
		$this->model_core_event->deleteEventByCode('catalog');

		$this->load->model('design/seo_regex');
		$this->model_design_seo_regex->deleteSeoRegexByName('Catalog Path');
		$this->model_design_seo_regex->deleteSeoRegexByName('Catalog Category');
		$this->model_design_seo_regex->deleteSeoRegexByName('Catalog Product');

		$this->load->model('design/seo_url');
		$this->model_design_seo_url->deleteSeoUrlByQuery('route=catalog/');
		$this->model_design_seo_url->deleteSeoUrlByQuery('catalog_');
	}

	public function update() {
		$table = new \Model\DBTable($this->registry);
		$table->update('catalog');
		$table->update('catalog_category');
		$table->update('catalog_product');
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/system/catalog')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}
}