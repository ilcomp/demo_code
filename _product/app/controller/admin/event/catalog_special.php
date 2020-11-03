<?php
namespace Controller\Event;

class CatalogSpecial extends \Controller {
	public function startup() {
		if ($this->config->get('catalog_special_status')) {
			$this->event->register('model/catalog/product_modify/*Product/after', new \Action('event/catalog_special/model_product'), 0);

			$this->event->register('view/catalog/product_form/before', new \Action('event/catalog_special/view_product'), 0);
		}
	}

	public function model_product($route, $data, $output = '') {
		if ((int)$output) {
			if ($route == 'catalog/product_modify/addProduct') {
				$this->load->model('extension/system/catalog_special');

				if (!isset($data[0]))
					$data[0] = array();

				$this->model_extension_system_catalog_special->updateCatalogSpecials((int)$output, $data[0]);
			}
		} elseif (isset($data[0]) && (int)$data[0]) {
			if ($route == 'catalog/product_modify/editProduct') {
				$this->load->model('extension/system/catalog_special');

				if (!isset($data[1]))
					$data[1] = array();

				$this->model_extension_system_catalog_special->updateCatalogSpecials((int)$data[0], $data[1]);
			} elseif ($route == 'catalog/product_modify/deleteProduct') {
				$this->load->model('extension/system/catalog_special');

				$this->model_extension_system_catalog_special->updateCatalogSpecials((int)$data[0]);
			}
		}
	}

	public function view_product($route, &$data) {
		$this->load->language('extension/system/catalog_special', 'temp');

		$this->load->model('extension/system/catalog_special');

		$language = $this->language->get('temp');

		$data_catalog_special = $language->all();

		$data_catalog_special['user_token'] = $this->session->data['user_token'];
		$data_catalog_special['product_id'] = isset($this->request->get['catalog_product_id']) ? $this->request->get['catalog_product_id'] : 0;
		$data_catalog_special['catalog_special_operator'] = $this->config->get('catalog_special_operator');

		if (isset($this->request->post['catalog_special'])) {
			$data_catalog_special['catalog_specials'] = (array)$this->request->post['catalog_special'];
		} elseif (isset($this->request->get['catalog_product_id'])) {
			$data_catalog_special['catalog_specials'] = $this->model_extension_system_catalog_special->getCatalogSpecials($this->request->get['catalog_product_id']);
		} else {
			$data_catalog_special['catalog_specials'] = array();
		}

		// foreach ($data_catalog_special['catalog_specials'] as &$catalog_special) {
		// 	if ($catalog_special['date_start'] == '0000-00-00')
		// 		$catalog_special['date_start'] = '';
		// 	if ($catalog_special['date_end'] == '0000-00-00')
		// 		$catalog_special['date_end'] = '';
		// }
		// unset($catalog_special);

		$data['additional_fields'] .= $this->load->view('catalog/catalog_special_view', $data_catalog_special);
	}
}