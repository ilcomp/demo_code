<?php
namespace Controller\Extension\Theme;

class ClientWSTTG extends \Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/theme/client_ws_ttg');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('core/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_core_setting->editSetting('theme_client_ws_ttg', $this->request->post, $this->request->get['store_id']);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('design/extension/theme'));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('design/extension/theme')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/theme/client_ws_ttg', 'store_id=' . $this->request->get['store_id'])
		);

		$data['action'] = $this->url->link('extension/theme/client_ws_ttg', 'store_id=' . $this->request->get['store_id']);

		$data['actions']['save'] = true;
		$data['actions']['cancel'] = $this->url->link('design/extension/theme');

		if (isset($this->request->get['store_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$setting_info = $this->model_core_setting->getSetting('theme_client_ws_ttg', $this->request->get['store_id']);
		}

		if (isset($this->request->post['theme_client_ws_ttg_status'])) {
			$data['theme_client_ws_ttg_status'] = $this->request->post['theme_client_ws_ttg_status'];
		} elseif (isset($setting_info['theme_client_ws_ttg_status'])) {
			$data['theme_client_ws_ttg_status'] = $setting_info['theme_client_ws_ttg_status'];
		} else {
			$data['theme_client_ws_ttg_status'] = '';
		}

		if (isset($this->request->post['theme_client_ws_ttg_modules'])) {
			$data['theme_client_ws_ttg_modules'] = $this->request->post['theme_client_ws_ttg_modules'];
		} elseif (isset($setting_info['theme_client_ws_ttg_modules'])) {
			$data['theme_client_ws_ttg_modules'] = $setting_info['theme_client_ws_ttg_modules'];
		} else {
			$data['theme_client_ws_ttg_modules'] = '';
		}

		if (isset($this->request->post['theme_client_ws_ttg_confidentiality'])) {
			$data['theme_client_ws_ttg_confidentiality'] = $this->request->post['theme_client_ws_ttg_confidentiality'];
		} elseif (isset($setting_info['theme_client_ws_ttg_confidentiality'])) {
			$data['theme_client_ws_ttg_confidentiality'] = $setting_info['theme_client_ws_ttg_confidentiality'];
		} else {
			$data['theme_client_ws_ttg_confidentiality'] = '';
		}

		if (isset($this->request->post['theme_client_ws_ttg_agreement'])) {
			$data['theme_client_ws_ttg_agreement'] = $this->request->post['theme_client_ws_ttg_agreement'];
		} elseif (isset($setting_info['theme_client_ws_ttg_agreement'])) {
			$data['theme_client_ws_ttg_agreement'] = $setting_info['theme_client_ws_ttg_agreement'];
		} else {
			$data['theme_client_ws_ttg_agreement'] = '';
		}

		if (isset($this->request->post['theme_client_ws_ttg_menus'])) {
			$data['theme_client_ws_ttg_menus'] = $this->request->post['theme_client_ws_ttg_menus'];
		} elseif (isset($setting_info['theme_client_ws_ttg_menus'])) {
			$data['theme_client_ws_ttg_menus'] = $setting_info['theme_client_ws_ttg_menus'];
		} else {
			$data['theme_client_ws_ttg_menus'] = '';
		}

		if (isset($this->request->post['theme_client_ws_ttg_header_link'])) {
			$data['theme_client_ws_ttg_header_link'] = $this->request->post['theme_client_ws_ttg_header_link'];
		} elseif (isset($setting_info['theme_client_ws_ttg_header_link'])) {
			$data['theme_client_ws_ttg_header_link'] = $setting_info['theme_client_ws_ttg_header_link'];
		} else {
			$data['theme_client_ws_ttg_header_link'] = '';
		}

		$this->load->model('info/article');

		$data['confidentiality'] = $this->model_info_article->getArticle($data['theme_client_ws_ttg_confidentiality']);
		$data['agreement'] = $this->model_info_article->getArticle($data['theme_client_ws_ttg_agreement']);

		$this->load->model('design/menu');

		$data['menus'] = $this->model_design_menu->getMenus();

		$images_name = array('location');

		$data['theme_client_ws_ttg_image'] = array();

		foreach ($images_name as $value) {
			$data['theme_client_ws_ttg_image'][$value]['name'] = $this->language->get('entry_image_' . $value);

			if (isset($this->request->post['theme_client_ws_ttg_image_' . $value . '_width'])) {
				$data['theme_client_ws_ttg_image'][$value]['width'] = $this->request->post['theme_client_ws_ttg_image_' . $value . '_width'];
			} elseif (isset($setting_info['theme_client_ws_ttg_image_' . $value . '_width'])) {
				$data['theme_client_ws_ttg_image'][$value]['width'] = $setting_info['theme_client_ws_ttg_image_' . $value . '_width'];
			} else {
				$data['theme_client_ws_ttg_image'][$value]['width'] = 300;
			}

			if (isset($this->request->post['theme_client_ws_ttg_image_' . $value . '_height'])) {
				$data['theme_client_ws_ttg_image'][$value]['height'] = $this->request->post['theme_client_ws_ttg_image_' . $value . '_height'];
			} elseif (isset($setting_info['theme_client_ws_ttg_image_' . $value . '_height'])) {
				$data['theme_client_ws_ttg_image'][$value]['height'] = $setting_info['theme_client_ws_ttg_image_' . $value . '_height'];
			} else {
				$data['theme_client_ws_ttg_image'][$value]['height'] = 300;
			}
		}
		
		// if (isset($this->request->post['theme_client_ws_ttg_image_cart_width'])) {
		// 	$data['theme_client_ws_ttg_image_cart_width'] = $this->request->post['theme_client_ws_ttg_image_cart_width'];
		// } elseif (isset($setting_info['theme_client_ws_ttg_image_cart_width'])) {
		// 	$data['theme_client_ws_ttg_image_cart_width'] = $setting_info['theme_client_ws_ttg_image_cart_width'];
		// } else {
		// 	$data['theme_client_ws_ttg_image_cart_width'] = 47;
		// }
		
		// if (isset($this->request->post['theme_client_ws_ttg_image_cart_height'])) {
		// 	$data['theme_client_ws_ttg_image_cart_height'] = $this->request->post['theme_client_ws_ttg_image_cart_height'];
		// } elseif (isset($setting_info['theme_client_ws_ttg_image_cart_height'])) {
		// 	$data['theme_client_ws_ttg_image_cart_height'] = $setting_info['theme_client_ws_ttg_image_cart_height'];
		// } else {
		// 	$data['theme_client_ws_ttg_image_cart_height'] = 47;
		// }

		if (isset($this->request->post['theme_client_ws_ttg_construstor_product_id'])) {
			$construstor_product_id = (int)$this->request->post['theme_client_ws_ttg_construstor_product_id'];
		} elseif (isset($setting_info['theme_client_ws_ttg_construstor_product_id'])) {
			$construstor_product_id = (int)$setting_info['theme_client_ws_ttg_construstor_product_id'];
		} else {
			$construstor_product_id = 0;
		}

		if (isset($this->request->post['theme_client_ws_ttg_construstor_exlude'])) {
			$construstor_exlude = (array)$this->request->post['theme_client_ws_ttg_construstor_exlude'];
		} elseif (isset($setting_info['theme_client_ws_ttg_construstor_exlude'])) {
			$construstor_exlude = (array)$setting_info['theme_client_ws_ttg_construstor_exlude'];
		} else {
			$construstor_exlude = array();
		}

		if (isset($this->request->post['theme_client_ws_ttg_construstor_min'])) {
			$data['theme_client_ws_ttg_construstor_min'] = (int)$this->request->post['theme_client_ws_ttg_construstor_min'];
		} elseif (isset($setting_info['theme_client_ws_ttg_construstor_min'])) {
			$data['theme_client_ws_ttg_construstor_min'] = (int)$setting_info['theme_client_ws_ttg_construstor_min'];
		} else {
			$data['theme_client_ws_ttg_construstor_min'] = 0;
		}

		if (isset($this->request->post['theme_client_ws_ttg_construstor_max'])) {
			$data['theme_client_ws_ttg_construstor_max'] = (int)$this->request->post['theme_client_ws_ttg_construstor_max'];
		} elseif (isset($setting_info['theme_client_ws_ttg_construstor_max'])) {
			$data['theme_client_ws_ttg_construstor_max'] = (int)$setting_info['theme_client_ws_ttg_construstor_max'];
		} else {
			$data['theme_client_ws_ttg_construstor_max'] = 0;
		}

		$this->load->model('catalog/product');

		$product = $this->model_catalog_product->getProduct($construstor_product_id);

		if ($product) {
			$data['construstor_product'] = $product;
		} else {
			$data['construstor_product'] = array();
		}

		$data['products_exlude'] = array();

		foreach ($construstor_exlude as $product_id) {
			$product = $this->model_catalog_product->getProduct($product_id);

			if ($product) {
				$data['products_exlude'][] = $product;
			}
		}

		$this->load->model('core/extension');
		$this->load->model('core/module');

		$data['extensions'] = array();

		// Get a list of installed modules
		$extensions = $this->model_core_extension->getInstalled('module');

		// Add all the modules which have multiple settings for each module
		foreach ($extensions as $code) {
			$this->load->language('extension/module/' . $code, 'extension');

			$module_data = array();

			$modules = $this->model_core_module->getModulesByCode($code);

			foreach ($modules as $module) {
				$module_data[] = array(
					'name' => strip_tags($module['name']),
					'code' => $code . '.' .  $module['module_id']
				);
			}

			if ($this->config->has('module_' . $code . '_status') || $module_data) {
				$data['extensions'][] = array(
					'name'   => strip_tags($this->language->get('extension')->get('heading_title')),
					'code'   => $code,
					'module' => $module_data
				);
			}
		}

		$data['user_token'] = $this->request->get['user_token'];

		$data['content'] = $this->load->view('extension/theme/client_ws_ttg', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/theme/client_ws_ttg')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function install() {
		$this->load->model('core/event');
		$this->model_core_event->addEvent('client_ws_ttg', 'api/controller/startup/event/after', 'event/theme_client_ws_ttg/startup');
		$this->model_core_event->addEvent('client_ws_ttg', 'admin/controller/startup/event/after', 'event/theme_client_ws_ttg/startup');
	}

	public function uninstall() {
		$this->load->model('core/event');
		$this->model_core_event->deleteEventByCode('client_ws_ttg');
	}
}
