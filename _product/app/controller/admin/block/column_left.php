<?php
namespace Controller\Block;

class ColumnLeft extends \Controller {
	public function index() {
		if ($this->user->isLogged()) {
			$this->load->language('block/column_left');

			$route = isset($this->request->get['route']) ? $this->request->get['route'] : $this->config->get('active_default');

			// Create a 3 level menu array
			// Level 2 can not have children

			// Menu

			$data['menus'][] = array(
				'id'       => 'menu-dashboard',
				'icon'	   => 'fa-tachometer-alt',
				'name'	   => $this->language->get('text_dashboard'),
				'href'     => $this->url->link('common/dashboard'),
				'children' => array(),
				'active'   => strpos($route, 'common/dashboard') === 0
			);

			// Marketing
			$data['menus'][] = array(
				'id'       => 'menu-marketing',
				'icon'	   => 'fa-share-alt',
				'name'	   => $this->language->get('text_marketing'),
				'href'     => '',
				'children' => array(),
				'active'   => false
			);

			if ($this->user->hasPermission('access', 'common/filemanager')) {
				$data['menus'][] = array(
					'id'       => 'menu-filemanager',
					'icon'	   => 'fa-file-image',
					'name'	   => $this->language->get('text_filemanager'),
					'href'     => $this->url->link('common/filemanager/view'),
					'children' => array(),
					'active'   => strpos($route, 'common/filemanager') === 0
				);
			}

			if ($this->user->hasPermission('access', 'common/file')) {
				$data['menus'][] = array(
					'id'       => 'menu-file',
					'icon'	   => 'fa-file-alt',
					'name'	   => $this->language->get('text_file'),
					'href'     => $this->url->link('common/file/view'),
					'children' => array(),
					'active'   => strpos($route, 'common/file') === 0 && strpos($route, 'common/file_') === false && strpos($route, 'common/filemanager') === false
				);
			}

			// Design
			$design = array();
			$design_active = false;

			if ($this->user->hasPermission('access', 'design/layout')) {
				if ($active = strpos($route, 'design/layout') === 0) {
					$design_active = true;
				}

				$design[] = array(
					'name'	   => $this->language->get('text_layout'),
					'href'     => $this->url->link('design/layout'),
					'children' => array(),
					'active'   => $active
				);
			}

			if ($this->user->hasPermission('access', 'design/menu')) {
				if ($active = strpos($route, 'design/menu') === 0) {
					$design_active = true;
				}

				$design[] = array(
					'name'	   => $this->language->get('text_menu'),
					'href'     => $this->url->link('design/menu'),
					'children' => array(),
					'active'   => $active
				);
			}

			if ($this->user->hasPermission('access', 'design/form')) {
				if ($active = strpos($route, 'design/form') === 0) {
					$design_active = true;
				}

				$design[] = array(
					'name'	   => $this->language->get('text_form'),
					'href'     => $this->url->link('design/form'),
					'children' => array(),
					'active'   => $active
				);
			}

			if ($this->user->hasPermission('access', 'design/theme')) {
				if ($active = strpos($route, 'design/theme') === 0) {
					$design_active = true;
				}

				$design[] = array(
					'name'	   => $this->language->get('text_theme'),
					'href'     => $this->url->link('design/theme'),
					'children' => array(),
					'active'   => $active
				);
			}

			if ($this->user->hasPermission('access', 'design/translation')) {
				if ($active = strpos($route, 'design/translation') === 0) {
					$design_active = true;
				}

				$design[] = array(
					'name'	   => $this->language->get('text_language_editor'),
					'href'     => $this->url->link('design/translation'),
					'children' => array(),
					'active'   => $active
				);
			}

			$seo = array();
			$seo_active = false;

			if ($this->user->hasPermission('access', 'design/seo_regex')) {
				if ($active = strpos($route, 'design/seo_regex') === 0) {
					$seo_active = true;
				}

				$seo[] = array(
					'name'	   => $this->language->get('text_seo_regex'),
					'href'     => $this->url->link('design/seo_regex'),
					'children' => array(),
					'active'   => $active
				);
			}

			if ($this->user->hasPermission('access', 'design/seo_url')) {
				if ($active = strpos($route, 'design/seo_url') === 0) {
					$seo_active = true;
				}

				$seo[] = array(
					'name'	   => $this->language->get('text_seo_url'),
					'href'     => $this->url->link('design/seo_url'),
					'children' => array(),
					'active'   => $active
				);
			}

			if ($seo) {
				if ($seo_active) {
					$design_active = true;
				}

				$design[] = array(
					'name'	   => $this->language->get('text_seo'),
					'href'     => '',
					'children' => $seo,
					'active'   => $seo_active
				);
			}

			if ($this->user->hasPermission('access', 'marketplace/extension')) {
				$extension_active = false;

				$children = array();

				$files = glob(DIR_CONTROLLER . 'design/extension/*.php', GLOB_BRACE);

				foreach ($files as $file) {
					$extension = basename($file, '.php');

					if ($active = (strpos($route, 'design/extension/' . $extension) === 0 || strpos($route, 'extension/' . $extension) === 0)) {
						$extension_active = true;
						$design_active = true;
					}

					// Compatibility code for old extension folders
					$this->load->language('design/extension/' . $extension, 'extension');

					if ($this->user->hasPermission('access', 'design/extension/' . $extension)) {
						$files = glob(DIR_CONTROLLER . 'extension/' . $extension . '/*.php', GLOB_BRACE);

						$children[] = array(
							'name'     => $this->language->get('extension')->get('heading_title') . ' (' . count($files) .')',
							'href'     => $this->url->link('design/extension/' . $extension),
							'children' => array(),
							'active'   => $active
						);
					}
				}

				if (!empty($children))
					$design[] = array(
						'name'	   => $this->language->get('text_extension'),
						'href'     => '',
						'children' => $children,
						'active'   => $extension_active
					);
			}

			if ($design) {
				$data['menus'][] = array(
					'id'       => 'menu-design',
					'icon'	   => 'fa-desktop',
					'name'	   => $this->language->get('text_design'),
					'href'     => '',
					'children' => $design,
					'active'   => $design_active
				);
			}

			// Lists
			$listing = array();
			$listing_active = false;

			if ($this->user->hasPermission('access', 'localisation/listing')) {
				if ($active = strpos($route, 'localisation/listing') === 0 && strpos($route, 'localisation/listing_') === false) {
					$listing_active = true;
				}

				$listing[] = array(
					'name'	   => $this->language->get('text_listing'),
					'href'     => $this->url->link('localisation/listing'),
					'children' => array(),
					'active'   => $active
				);
			}

			if ($this->user->hasPermission('access', 'localisation/listing_item')) {
				if ($active = strpos($route, 'localisation/listing_item') === 0) {
					$listing_active = true;
				}

				$this->load->model('localisation/listing');

				$listings = $this->model_localisation_listing->getListings();

				foreach ($listings as $item) {
					$listing[(string)$item['listing_id']] = array(
						'name'	   => $item['name'],
						'href'     => $this->url->link('localisation/listing_item', 'listing_id=' . $item['listing_id']),
						'children' => array(),
						'active'   => $active && isset($this->request->get['listing_id']) && $this->request->get['listing_id'] == $item['listing_id']
					);
				}
			}

			if ($listing) {
				$data['menus'][] = array(
					'id'       => 'menu-listing',
					'icon'	   => 'fa-book',
					'name'	   => $this->language->get('text_listing'),
					'href'     => '',
					'children' => $listing,
					'active'   => $listing_active
				);
			}

			// Extension
			$marketplace = array();
			$marketplace_active = false;

			// if ($this->user->hasPermission('access', 'marketplace/marketplace')) {
				// if ($active = strpos($route, 'marketplace/marketplace') === 0) {
				// 	$marketplace_active = true;
				// }

			// 	$marketplace[] = array(
			// 		'name'	   => $this->language->get('text_marketplace'),
			// 		'href'     => $this->url->link('marketplace/marketplace'),
			// 		'children' => array(),
					// 'active'   => $active
			// 	);
			// }

			// if ($this->user->hasPermission('access', 'marketplace/installer')) {
				// if ($active = strpos($route, 'marketplace/installer') === 0) {
				// 	$marketplace_active = true;
				// }

			// 	$marketplace[] = array(
			// 		'name'	   => $this->language->get('text_installer'),
			// 		'href'     => $this->url->link('marketplace/installer'),
			// 		'children' => array(),
					// 'active'   => $active
			// 	);
			// }

			if ($this->user->hasPermission('access', 'marketplace/system')) {
				if ($active = (strpos($route, 'marketplace/system') === 0 || strpos($route, 'extension/system') === 0)) {
					$marketplace_active = true;
				}

				$marketplace[] = array(
					'name'	   => $this->language->get('text_systems'),
					'href'     => $this->url->link('marketplace/system'),
					'children' => array(),
					'active'   => $active
				);
			}

			// if ($this->user->hasPermission('access', 'marketplace/modification')) {
			// 	$marketplace[] = array(
			// 		'name'	   => $this->language->get('text_modification'),
			// 		'href'     => $this->url->link('marketplace/modification'),
			// 		'children' => array()
			// 	);
			// }

			if ($this->user->hasPermission('access', 'marketplace/event')) {
				if ($active = strpos($route, 'marketplace/event') === 0) {
					$marketplace_active = true;
				}

				$marketplace[] = array(
					'name'	   => $this->language->get('text_event'),
					'href'     => $this->url->link('marketplace/event'),
					'children' => array(),
					'active'   => $active
				);
			}

			if ($this->user->hasPermission('access', 'marketplace/cron')) {
				if ($active = strpos($route, 'marketplace/cron') === 0) {
					$marketplace_active = true;
				}

				$marketplace[] = array(
					'name'	   => $this->language->get('text_cron'),
					'href'     => $this->url->link('marketplace/cron'),
					'children' => array(),
					'active'   => $active
				);
			}

			if ($this->user->hasPermission('access', 'marketplace/extension')) {
				if ($active = strpos($route, 'marketplace/extension') === 0) {
					$marketplace_active = true;
				}

				$extensions = array();

				$files = glob(DIR_CONTROLLER . 'extension/extension/*.php', GLOB_BRACE);

				foreach ($files as $file) {
					$extension = basename($file, '.php');

					// Compatibility code for old extension folders
					$this->load->language('extension/extension/' . $extension, 'extension');

					if ($this->user->hasPermission('access', 'extension/extension/' . $extension)) {
						$files = glob(DIR_CONTROLLER . 'extension/' . $extension . '/*.php', GLOB_BRACE);

						$extensions[] = array(
							'name'	   => $this->language->get('extension')->get('heading_title') . ' (' . count($files) .')',
							'href'     => $this->url->link('marketplace/extension', 'type=' . $extension),
							'children' => array(),
							'active'   => $active && isset($this->request->get['type']) && $this->request->get['type'] == $extension
						);
					}
				}

				$marketplace[] = array(
					'name'	   => $this->language->get('text_extension'),
					'href'     => $this->url->link('marketplace/extension'),
					'children' => $extensions,
					'active'   => $active
				);
			}

			if ($marketplace) {
				$data['menus'][] = array(
					'id'       => 'menu-extension',
					'icon'	   => 'fa-puzzle-piece',
					'name'	   => $this->language->get('text_extension'),
					'href'     => '',
					'children' => $marketplace,
					'active'   => $marketplace_active
				);
			}

			// System
			$system = array();
			$system_active = false;

			if ($this->user->hasPermission('access', 'setting/custom_field')) {
				if ($active = strpos($route, 'setting/custom_field') === 0) {
					$system_active = true;
				}

				$system[] = array(
					'name'	   => $this->language->get('text_custom_field'),
					'href'     => $this->url->link('setting/custom_field'),
					'children' => array(),
					'active'   => $active
				);
			}

			// Settings
			$setting = array();
			$setting_active = false;

			if ($this->user->hasPermission('access', 'setting/app_admin')) {
				if ($active = strpos($route, 'setting/app_admin') === 0) {
					$setting_active = true;
				}

				$setting[] = array(
					'name'	   => $this->language->get('text_app_admin'),
					'href'     => $this->url->link('setting/app_admin'),
					'children' => array(),
					'active'   => $active
				);
			}

			if ($this->user->hasPermission('access', 'setting/app_api')) {
				if ($active = strpos($route, 'setting/app_api') === 0) {
					$setting_active = true;
				}

				$setting[] = array(
					'name'	   => $this->language->get('text_app_api'),
					'href'     => $this->url->link('setting/app_api'),
					'children' => array(),
					'active'   => $active
				);
			}

			if ($this->user->hasPermission('access', 'setting/app_client')) {
				if ($active = strpos($route, 'setting/app_client') === 0) {
					$setting_active = true;
				}

				$setting[] = array(
					'name'	   => $this->language->get('text_app_client'),
					'href'     => $this->url->link('setting/app_client'),
					'children' => array(),
					'active'   => $active
				);
			}

			if ($this->user->hasPermission('access', 'setting/mail')) {
				if ($active = strpos($route, 'setting/mail') === 0) {
					$setting_active = true;
				}

				$setting[] = array(
					'name'	   => $this->language->get('text_mail'),
					'href'     => $this->url->link('setting/mail'),
					'children' => array(),
					'active'   => $active
				);
			}

			if ($this->user->hasPermission('access', 'setting/sms')) {
				if ($active = strpos($route, 'setting/sms') === 0) {
					$setting_active = true;
				}

				$setting[] = array(
					'name'	   => $this->language->get('text_sms'),
					'href'     => $this->url->link('setting/sms'),
					'children' => array(),
					'active'   => $active
				);
			}

			if ($this->user->hasPermission('access', 'setting/system')) {
				if ($active = strpos($route, 'setting/system') === 0) {
					$setting_active = true;
				}

				$setting[] = array(
					'name'	   => $this->language->get('text_system'),
					'href'     => $this->url->link('setting/system'),
					'children' => array(),
					'active'   => $active
				);
			}

			if ($this->user->hasPermission('access', 'setting/setting')) {
				if ($active = strpos($route, 'setting/setting') === 0) {
					$setting_active = true;
				}

				$setting[] = array(
					'name'	   => $this->language->get('text_setting'),
					'href'     => $this->url->link('setting/store'),
					'children' => array(),
					'active'   => $active
				);
			}

			if ($setting) {
				if ($setting_active) {
					$system_active = true;
				}

				$system[] = array(
					'name'	   => $this->language->get('text_settings'),
					'href'     => '',
					'children' => $setting,
					'active'   => $setting_active
				);
			}

			// Users
			$user = array();
			$user_active = false;

			if ($this->user->hasPermission('access', 'user/user')) {
				if ($active = strpos($route, 'user/user') === 0 && strpos($route, 'common/user_') === false) {
					$user_active = true;
				}

				$user[] = array(
					'name'	   => $this->language->get('text_users'),
					'href'     => $this->url->link('user/user'),
					'children' => array(),
					'active'   => $active
				);
			}

			if ($this->user->hasPermission('access', 'user/user_permission')) {
				if ($active = strpos($route, 'user/user_permission') === 0) {
					$user_active = true;
				}

				$user[] = array(
					'name'	   => $this->language->get('text_user_group'),
					'href'     => $this->url->link('user/user_permission'),
					'children' => array(),
					'active'   => $active
				);
			}

			if ($this->user->hasPermission('access', 'user/api')) {
				if ($active = strpos($route, 'user/api') === 0) {
					$user_active = true;
				}

				$user[] = array(
					'name'	   => $this->language->get('text_api'),
					'href'     => $this->url->link('user/api'),
					'children' => array(),
					'active'   => $active
				);
			}

			if ($user) {
				if ($user_active) {
					$system_active = true;
				}

				$system[] = array(
					'id'       => 'menu-setting',
					'name'	   => $this->language->get('text_users'),
					'href'     => '',
					'children' => $user,
					'active'   => $user_active
				);
			}

			// Localisation
			$localisation = array();
			$localisation_active = false;

			if ($this->user->hasPermission('access', 'localisation/location')) {
				if ($active = strpos($route, 'localisation/location') === 0) {
					$localisation_active = true;
				}

				$localisation[] = array(
					'name'	   => $this->language->get('text_location'),
					'href'     => $this->url->link('localisation/location'),
					'children' => array(),
					'active'   => $active
				);
			}

			if ($this->user->hasPermission('access', 'localisation/language')) {
				if ($active = strpos($route, 'localisation/language') === 0) {
					$localisation_active = true;
				}

				$localisation[] = array(
					'name'	   => $this->language->get('text_language'),
					'href'     => $this->url->link('localisation/language'),
					'children' => array(),
					'active'   => $active
				);
			}

			if ($this->user->hasPermission('access', 'localisation/country')) {
				if ($active = strpos($route, 'localisation/country') === 0) {
					$localisation_active = true;
				}

				$localisation[] = array(
					'name'	   => $this->language->get('text_country'),
					'href'     => $this->url->link('localisation/country'),
					'children' => array(),
					'active'   => $active
				);
			}

			if ($this->user->hasPermission('access', 'localisation/zone')) {
				if ($active = strpos($route, 'localisation/zone') === 0) {
					$localisation_active = true;
				}

				$localisation[] = array(
					'name'	   => $this->language->get('text_zone'),
					'href'     => $this->url->link('localisation/zone'),
					'children' => array(),
					'active'   => $active
				);
			}

			if ($this->user->hasPermission('access', 'localisation/geo_zone')) {
				if ($active = strpos($route, 'localisation/geo_zone') === 0) {
					$localisation_active = true;
				}

				$localisation[] = array(
					'name'	   => $this->language->get('text_geo_zone'),
					'href'     => $this->url->link('localisation/geo_zone'),
					'children' => array(),
					'active'   => $active
				);
			}

			if ($localisation) {
				if ($localisation_active) {
					$system_active = true;
				}

				$system[] = array(
					'id'       => 'menu-localisation',
					'name'	   => $this->language->get('text_localisation'),
					'href'     => '',
					'children' => $localisation,
					'active'   => $localisation_active
				);
			}

			// Tools
			$maintenance = array();
			$maintenance_active = false;

			if ($this->user->hasPermission('access', 'tool/upgrade')) {
				if ($active = strpos($route, 'tool/upgrade') === 0) {
					$maintenance_active = true;
				}

				$maintenance[] = array(
					'name'	   => $this->language->get('text_upgrade'),
					'href'     => $this->url->link('tool/upgrade'),
					'children' => array(),
					'active'   => $active
				);
			}

			if ($this->user->hasPermission('access', 'tool/backup')) {
				if ($active = strpos($route, 'tool/backup') === 0) {
					$maintenance_active = true;
				}

				$maintenance[] = array(
					'name'	   => $this->language->get('text_backup'),
					'href'     => $this->url->link('tool/backup'),
					'children' => array(),
					'active'   => $active
				);
			}

			if ($this->user->hasPermission('access', 'tool/upload')) {
				if ($active = strpos($route, 'tool/upload') === 0) {
					$maintenance_active = true;
				}

				$maintenance[] = array(
					'name'	   => $this->language->get('text_upload'),
					'href'     => $this->url->link('tool/upload'),
					'children' => array(),
					'active'   => $active
				);
			}

			if ($this->user->hasPermission('access', 'tool/log')) {
				if ($active = strpos($route, 'tool/log') === 0) {
					$maintenance_active = true;
				}

				$maintenance[] = array(
					'name'	   => $this->language->get('text_log'),
					'href'     => $this->url->link('tool/log'),
					'children' => array(),
					'active'   => $active
				);
			}

			if ($maintenance) {
				if ($maintenance_active) {
					$system_active = true;
				}

				$system[] = array(
					'id'       => 'menu-maintenance',
					'icon'	   => 'fa-cog',
					'name'	   => $this->language->get('text_maintenance'),
					'href'     => '',
					'children' => $maintenance,
					'active'   => $maintenance_active
				);
			}

			if ($system) {
				$data['menus'][] = array(
					'id'       => 'menu-system',
					'icon'	   => 'fa-cog',
					'name'	   => $this->language->get('text_system'),
					'href'     => '',
					'children' => $system,
					'active'   => $system_active
				);
			}

			$report = array();
			$report_active = false;

			if ($this->user->hasPermission('access', 'report/report')) {
				if ($active = strpos($route, 'report/report') === 0) {
					$education_active = true;
				}

				$report[] = array(
					'name'	   => $this->language->get('text_reports'),
					'href'     => $this->url->link('report/report'),
					'children' => array(),
					'active'   => $active
				);
			}

			if ($this->user->hasPermission('access', 'report/online')) {
				if ($active = strpos($route, 'report/online') === 0) {
					$education_active = true;
				}

				$report[] = array(
					'name'	   => $this->language->get('text_online'),
					'href'     => $this->url->link('report/online'),
					'children' => array(),
					'active'   => $active
				);
			}

			if ($this->user->hasPermission('access', 'report/statistics')) {
				if ($active = strpos($route, 'report/statistics') === 0) {
					$education_active = true;
				}

				$report[] = array(
					'name'	   => $this->language->get('text_statistics'),
					'href'     => $this->url->link('report/statistics'),
					'children' => array(),
					'active'   => $active
				);
			}

			if ($report) {
				$data['menus'][] = array(
					'id'       => 'menu-report',
					'icon'     => 'fa-chart-bar',
					'name'     => $this->language->get('text_reports'),
					'href'     => '',
					'children' => $report,
					'active'   => $report_active
				);
			}

			return $this->load->view('block/column_left', $data);
		}
	}
}
