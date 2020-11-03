<?php
namespace  Model\Core;

class CustomField extends \Model {
	public function addCustomField($data) {
		if (isset($data['setting']))
			$data['setting'] = is_array($data['setting']) ? json_encode($data['setting']) : html_entity_decode($data['setting'], ENT_QUOTES);
		else
			$data['setting'] = '';

		$this->db->query("INSERT INTO `" . DB_PREFIX . "custom_field` SET type = '" . $this->db->escape((string)$data['type']) . "', code = '" . $this->db->escape((string)$data['code']) . "', setting = '" . $this->db->escape($data['setting']) . "', status = '" . (int)$data['status'] . "', sort_order = '" . (int)$data['sort_order'] . "', multilanguage = '" . (int)$data['multilanguage'] . "', search = '" . (int)$data['search'] . "'");

		$custom_field_id = $this->db->getLastId();

		foreach ($data['custom_field_location'] as $language_id => $value) {
			if (!empty($value['location']))
				$this->db->query("INSERT INTO " . DB_PREFIX . "custom_field_location SET custom_field_id = '" . (int)$custom_field_id . "', location = '" . $this->db->escape((string)$value['location']) . "', required = '" . (!empty($value['required']) ? '1' : '0') . "', readonly = '" . (!empty($value['readonly']) ? '1' : '0') . "'");
		}

		foreach ($data['custom_field_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "custom_field_description SET custom_field_id = '" . (int)$custom_field_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', help = '" . $this->db->escape($value['help']) . "'");
		}
		
		return $custom_field_id;
	}

	public function editCustomField($custom_field_id, $data) {
		if (isset($data['setting']))
			$data['setting'] = is_array($data['setting']) ? json_encode($data['setting']) : html_entity_decode($data['setting'], ENT_QUOTES);
		else
			$data['setting'] = '';

		$this->db->query("UPDATE `" . DB_PREFIX . "custom_field` SET type = '" . $this->db->escape((string)$data['type']) . "', code = '" . $this->db->escape((string)$data['code']) . "', setting = '" . $this->db->escape($data['setting']) . "', status = '" . (int)$data['status'] . "', sort_order = '" . (int)$data['sort_order'] . "', multilanguage = '" . (int)$data['multilanguage'] . "', search = '" . (int)$data['search'] . "' WHERE custom_field_id = '" . (int)$custom_field_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "custom_field_location WHERE custom_field_id = '" . (int)$custom_field_id . "'");

		foreach ($data['custom_field_location'] as $value) {
			if (!empty($value['location']))
				$this->db->query("INSERT INTO " . DB_PREFIX . "custom_field_location SET custom_field_id = '" . (int)$custom_field_id . "', location = '" . $this->db->escape((string)$value['location']) . "', required = '" . (!empty($value['required']) ? '1' : '0') . "', readonly = '" . (!empty($value['readonly']) ? '1' : '0') . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "custom_field_description WHERE custom_field_id = '" . (int)$custom_field_id . "'");

		foreach ($data['custom_field_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "custom_field_description SET custom_field_id = '" . (int)$custom_field_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', help = '" . $this->db->escape($value['help']) . "'");
		}
	}

	public function deleteCustomField($custom_field_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "custom_field` WHERE custom_field_id = '" . (int)$custom_field_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "custom_field_location` WHERE custom_field_id = '" . (int)$custom_field_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "custom_field_description` WHERE custom_field_id = '" . (int)$custom_field_id . "'");
	}

	public function getCustomField($custom_field_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "custom_field` cf LEFT JOIN " . DB_PREFIX . "custom_field_description cfd ON (cf.custom_field_id = cfd.custom_field_id) WHERE cf.custom_field_id = '" . (int)$custom_field_id . "' AND cfd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		if ($query->num_rows) {
			$query->row['setting'] = json_decode($query->row['setting'], true);
			$query->row['custom_field_location'] = $this->getCustomFieldLocations($query->row['custom_field_id']);
		}

		return $query->row;
	}

	public function getCustomFields($data = array()) {
		$sql = "SELECT cf.custom_field_id FROM `" . DB_PREFIX . "custom_field` cf LEFT JOIN " . DB_PREFIX . "custom_field_location cfl ON (cf.custom_field_id = cfl.custom_field_id) LEFT JOIN " . DB_PREFIX . "custom_field_description cfd ON (cf.custom_field_id = cfd.custom_field_id) WHERE cfd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (isset($data['filter_status'])) {
			$sql .= " AND cf.status = '" . (int)$data['filter_status'] . "'";
		}

		if (!empty($data['filter_location'])) {
			$sql .= " AND cfl.location = '" . $this->db->escape((string)$data['filter_location']) . "'";
		}

		$sql .= " GROUP BY cf.custom_field_id";

		$sort_data = array(
			'name' => 'cfd.name',
			'code' => 'cf.code',
			'type' => 'cf.type',
			'location' => 'cfl.location',
			'status' => 'cf.status',
			'sort_order' => 'cf.sort_order'
		);

		if (isset($data['sort']) && isset($sort_data[$data['sort']])) {
			$sql .= " ORDER BY " . $sort_data[$data['sort']];
		} else {
			$sql .= " ORDER BY cf.sort_order";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		foreach ($query->rows as &$row) {
			$row = $this->getCustomField($row['custom_field_id']);
		}
		unset($row);

		return $query->rows;
	}

	public function getCustomFieldLocations($custom_field_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "custom_field_location WHERE custom_field_id = '" . (int)$custom_field_id . "' ORDER BY `location`");

		return $query->rows;
	}

	public function getCustomFieldDescriptions($custom_field_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "custom_field_description WHERE custom_field_id = '" . (int)$custom_field_id . "'");

		return array_column($query->rows, null, 'language_id');
	}

	public function getTotalCustomFields() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "custom_field`");

		return $query->row['total'];
	}

	public function getCustomFieldsByLocation($location) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "custom_field_location` cfl LEFT JOIN " . DB_PREFIX . "custom_field cf ON (cfl.custom_field_id = cf.custom_field_id) LEFT JOIN " . DB_PREFIX . "custom_field_description cfd ON (cf.custom_field_id = cfd.custom_field_id) WHERE cfd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND cf.status = '1' AND cfl.location = '" . $this->db->escape((string)$location) . "' ORDER BY cf.sort_order ASC");

		foreach ($query->rows as &$row) {
			$row['setting'] = json_decode($row['setting'], true);
		}
		unset($row);

		return $query->rows;
	}

	public function getAsValue($value, $custom_field) {
		switch ($custom_field['type']) {
			case 'text':
			case 'textarea':
			case 'editor':
			case 'template':
				$result = html_entity_decode($value, ENT_QUOTES, 'UTF-8');

				break;
			case 'date':
			case 'datetime':
			case 'time':
				$result = strtotime($value);

				break;
			case 'select':
			case 'radio':
					$this->load->model('localisation/listing');
					$this->load->model('tool/image');

					$result = $this->model_localisation_listing->getListingItem($value);

					if (isset($result['image']) && $result['image'])
						$result['image'] = $this->model_tool_image->link($result['image']);

				break;
			case 'checkbox':
					$this->load->model('localisation/listing');
					$this->load->model('tool/image');

					$listing_id = isset($custom_field['setting']['listing_id']) ? $custom_field['setting']['listing_id'] : '';

					if (!empty($value)) {
						$values = json_decode($value, true);
					} else {
						$values = '';
					}

					if (is_numeric($listing_id)) {
						$result = array();

						if (is_array($values)) {
							foreach ($values as $listing_item_id) {
								$listing_item = $this->model_localisation_listing->getListingItem($listing_item_id);

								if ($listing_item['image'])
									$listing_item['image'] = $this->model_tool_image->link($listing_item['image']);

								$result[] = $listing_item;
							}
						}
					} else {
						$result = $value;
					}

				break;
			case 'values':
				if ($value) {
					$result = json_decode($value, true);
				} else {
					$result = '';
				}

				break;
			case 'modules':
				if (!empty($value)) {
					$this->load->model('core/module');

					$result = array();

					foreach (json_decode($value, true) as $module) {
						$part = explode('.', $module['code']);

						if (isset($part[0]) && $this->config->get('module_' . $part[0] . '_status')) {
							$module_data = $this->load->controller('extension/module/' . $part[0]);

							if ($module_data) {
								$result[] = $module_data;
							}
						}

						if (isset($part[1])) {
							$setting_info = $this->model_core_module->getModule($part[1]);

							if ($setting_info && $setting_info['status']) {
								$output = $this->load->controller('extension/module/' . $part[0], $setting_info, $part[1]);

								if ($output) {
									$result[] = $output;
								}
							}
						}
					}
				} else {
					$result = array();
				}

				break;
			case 'image':
				$this->load->model('tool/image');

				$thumb_width = isset($custom_field['setting']['width']) ? $custom_field['setting']['width'] : 100;
				$thumb_height = isset($custom_field['setting']['height']) ? $custom_field['setting']['height'] : 100;
				$thumb_method = isset($custom_field['setting']['method']) ? $custom_field['setting']['method'] : '';

				$image['image'] = $value;

				if ($image['image'] && is_file(DIR_IMAGE . $image['image'])) {
					switch ($thumb_method) {
						case 'link': $image['thumb'] = $this->model_tool_image->link($image['image']); break;
						case 'crop': $image['thumb'] = $this->model_tool_image->crop($image['image'], $thumb_width, $thumb_height); break;
						default: $image['thumb'] = $this->model_tool_image->resize($image['image'], $thumb_width, $thumb_height); break;
					}
				} else {
					$image['thumb'] = '';
				}

				$result = $image;

				break;
			case 'file':
				$file['file'] = $value;

				if ($file['file'] && is_file(DIR_FILE . $file['file'])) {
					$file['href'] = 'file/' . $file['file'];
				} else {
					$file = false;
				}

				$result = $file;

				break;
			case 'country':
					$this->load->model('localisation/country');

					$country = $this->model_localisation_country->getCountry($value);

					$result = $country ? $country['name'] : false;

				break;
			case 'zone':
					$this->load->model('localisation/zone');

					$zone = $this->model_localisation_zone->getZone($value);

					$result = $zone ? $zone['name'] : false;

				break;
			default:
				$result = $value;

				break;
		}

		return $result;
	}

	public function getAsField($values, $location) {
		$this->load->model('localisation/language');
		$this->load->model('localisation/listing');
		$this->load->model('localisation/country');
		$this->load->model('core/extension');
		$this->load->model('core/module');
		$this->load->model('tool/image');

		$custom_fields = $this->getCustomFieldsByLocation($location);

		$languages = $this->model_localisation_language->getLanguages();

		$values = (array)$values;

		foreach ($custom_fields as &$custom_field) {
			$custom_field['value'][-1] = isset($values[$custom_field['custom_field_id']]) && isset($values[$custom_field['custom_field_id']][-1]) ? $values[$custom_field['custom_field_id']][-1] : '';

			foreach ($languages as $language) {
				$custom_field['value'][$language['language_id']] = isset($values[$custom_field['custom_field_id']]) && isset($values[$custom_field['custom_field_id']][$language['language_id']]) ? $values[$custom_field['custom_field_id']][$language['language_id']] : '';
			}

			$value_result = $this->event->trigger('model/core/custom_field/render_value/before', array('model/core/custom_field/render_value/before', &$custom_field));

			if (!$value_result) {
				switch ($custom_field['type']) {
					case 'editor':
						//$this->document->addScript('/assets/babel/babel.js');
						$this->document->addScript('/assets/switcheditor/switcheditor.js', 5);

						$custom_field['language'] = $this->config->get('language');
						$custom_field['editor'] = '';

						$custom_field['dialogs'] = array();

						if (!empty($custom_field['setting']['dialogs'])) {
							foreach ($custom_field['setting']['dialogs'] as $dialog) {
								$this->document->addScript('/assets/switcheditor/dialog-' . $dialog . '.js', 10);

								switch ($dialog) {
									case 'image':
										$custom_field['dialogs'][] = array(
											'code' => $dialog,
											'setting' => array(
												'manager' => $this->url->link('common/filemanager', 'user_token=' . $this->request->get['user_token']),
												'link' => $this->url->link('common/filemanager/get_link', 'user_token=' . $this->request->get['user_token'])
											),
											'icon' => 'fas fa-image'
										);
										break;
									case 'file':
										$custom_field['dialogs'][] = array(
											'code' => $dialog,
											'setting' => array(
												'manager' => $this->url->link('common/file', 'user_token=' . $this->request->get['user_token']),
												'link' => $this->url->link('common/file/get_link', 'user_token=' . $this->request->get['user_token'])
											),
											'icon' => 'fas fa-file'
										);
										break;
									
									default:
										$custom_field['dialogs'][] = array(
											'code' => $dialog,
											'setting' => array(
												'manager' => $this->url->link('design/link', 'user_token=' . $this->request->get['user_token'])
											),
											'icon' => 'fas fa-link'
										);
										break;
								}
							}
						}

						$custom_field['editors'] = array();

						if (!empty($custom_field['setting']['editors'])) {
							foreach ($custom_field['setting']['editors'] as $editor) {
								if (!$custom_field['editor'])
									$custom_field['editor'] = $editor;

								$this->document->addScript('/assets/switcheditor/editor-' . $editor . '.js', 10);

								switch ($editor) {
									case 'ckeditor':
										$custom_field['editors'][] = array(
											'code' => $editor,
											'setting' => array(
												'basehref' => '/'
											),
											'icon' => 'fas fa-eye'
										);
										break;
									
									default:
										$custom_field['editors'][] = array(
											'code' => $editor,
											'setting' => array(),
											'icon' => 'fas fa-code'
										);
										break;
								}
							}
						}

						break;
					case 'select':
					case 'radio':
						$listing_id = isset($custom_field['setting']['listing_id']) ? $custom_field['setting']['listing_id'] : '';

						$custom_field['listing_items'] = (array)$this->model_localisation_listing->getListingItems(array('filter_listing_id' => $listing_id));

						break;
					case 'checkbox':
						$listing_id = isset($custom_field['setting']['listing_id']) ? $custom_field['setting']['listing_id'] : '';

						if (is_numeric($listing_id)) {
							$custom_field['listing_items'] = (array)$this->model_localisation_listing->getListingItems(array('filter_listing_id' => $listing_id));

							foreach ($custom_field['value'] as &$value) {
								$value = json_decode($value, true);
							}
							unset($value);
						} else {
							$custom_field['listing_items'] = '';
						}

						break;
					case 'values':
						foreach ($custom_field['value'] as &$value) {
							$value = json_decode($value, true);
						}
						unset($value);

						break;
					case 'modules':
						$custom_field['extensions'] = array();
						
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
								$custom_field['extensions'][] = array(
									'name'   => strip_tags($this->language->get('extension')->get('heading_title')),
									'code'   => $code,
									'module' => $module_data
								);
							}
						}

						foreach ($custom_field['value'] as &$value) {
							if ($value) {
								$modules = json_decode($value, true);
								$value = array();

								if (!is_array($modules))
									$modules = array();

								// Add all the modules which have multiple settings for each module
								foreach ($modules as $module) {
									$part = explode('.', $module['code']);
								
									$this->load->language('extension/module/' . $part[0], 'extension');

									if (!isset($part[1])) {
										$value[] = array(
											'name'       => strip_tags($this->language->get('extension')->get('heading_title')),
											'code'       => $module['code'],
											'sort_order' => $module['sort_order']
										);
									} else {
										$module_info = $this->model_core_module->getModule($part[1]);

										if ($module_info) {
											$value[] = array(
												'name'       => strip_tags($module_info['name']),
												'code'       => $module['code'],
												'sort_order' => $module['sort_order']
											);
										}
									}
								}
							}
						}
						unset($value);

						break;
					case 'image':
						$thumb_width = $this->config->get('admin_image_thumb_width') ? $this->config->get('admin_image_thumb_width') : 100;
						$thumb_height = $this->config->get('admin_image_thumb_height') ? $this->config->get('admin_image_thumb_height') : 100;

						$custom_field['placeholder'] = $this->model_tool_image->resize('no_image.png', $thumb_width, $thumb_height);

						foreach ($custom_field['value'] as &$value) {
							$image['image'] = $value;

							if ($image['image'] && is_file(DIR_IMAGE . $image['image'])) {
								$image['thumb'] = $this->model_tool_image->resize($image['image'], $thumb_width, $thumb_height);
							} else {
								$image['thumb'] = $custom_field['placeholder'];
							}

							$value = $image;
						}
						unset($value);

						break;
					case 'country':
						$custom_field['countries'] = $this->model_localisation_country->getCountries(array('filter' => array('status' => 1)));

						foreach ($custom_field['value'] as &$value) {
							if (!$value)
								$value = $this->config->get('config_country_id');
						}
						unset($value);

						break;
					case 'zone':
						foreach ($custom_field['value'] as &$value) {
							if (!$value)
								$value = $this->config->get('config_zone_id');
						}
						unset($value);

						break;
				}
			}
		}
		unset($custom_field);

		return $custom_fields;
	}

	public function getLocations() {
		$this->load->language('setting/custom_field');

		return array(
			'setting' => array(
				'label' => $this->language->get('text_setting'),
				'options' => array(
					array(
						'value' => 'setting',
						'name' => $this->language->get('text_setting'),
					)
				)
			)
		);
	}

	public function getTypes() {
		$this->load->language('setting/custom_field');

		return array(
			'choose' => array(
				'label' => $this->language->get('text_choose'),
				'options' => array(
					array(
						'value' => 'select',
						'name' => $this->language->get('text_select'),
					),
					array(
						'value' => 'radio',
						'name' => $this->language->get('text_radio'),
					),
					array(
						'value' => 'checkbox',
						'name' => $this->language->get('text_checkbox'),
					)
				)
			),
			'input' => array(
				'label' => $this->language->get('text_input'),
				'options' => array(
					array(
						'value' => 'text',
						'name' => $this->language->get('text_text'),
					),
					array(
						'value' => 'textarea',
						'name' => $this->language->get('text_textarea'),
					),
					array(
						'value' => 'editor',
						'name' => $this->language->get('text_editor'),
					),
					array(
						'value' => 'email',
						'name' => $this->language->get('text_email'),
					),
					array(
						'value' => 'tel',
						'name' => $this->language->get('text_tel'),
					),
					array(
						'value' => 'number',
						'name' => $this->language->get('text_number'),
					),
					array(
						'value' => 'href',
						'name' => $this->language->get('text_href'),
					)
				)
			),
			'file' => array(
				'label' => $this->language->get('text_file'),
				'options' => array(
					array(
						'value' => 'image',
						'name' => $this->language->get('text_image'),
					),
					array(
						'value' => 'file',
						'name' => $this->language->get('text_file'),
					)
				)
			),
			'date' => array(
				'label' => $this->language->get('text_date'),
				'options' => array(
					array(
						'value' => 'date',
						'name' => $this->language->get('text_date'),
					),
					array(
						'value' => 'time',
						'name' => $this->language->get('text_time'),
					),
					array(
						'value' => 'datetime',
						'name' => $this->language->get('text_datetime'),
					)
				)
			),
			'link' => array(
				'label' => $this->language->get('text_link'),
				'options' => array(
				)
			),
			'block' => array(
				'label' => $this->language->get('text_block'),
				'options' => array(
					array(
						'value' => 'values',
						'name' => $this->language->get('text_values'),
					),
					array(
						'value' => 'modules',
						'name' => $this->language->get('text_modules'),
					)
				)
			),
			'address' => array(
				'label' => $this->language->get('text_address'),
				'options' => array(
					array(
						'value' => 'country',
						'name' => $this->language->get('text_country'),
					),
					array(
						'value' => 'zone',
						'name' => $this->language->get('text_zone'),
					)
				)
			),
			'message' => array(
				'label' => $this->language->get('text_message'),
				'options' => array(
					array(
						'value' => 'info',
						'name' => $this->language->get('text_info'),
					),
					array(
						'value' => 'danger',
						'name' => $this->language->get('text_danger'),
					)
				)
			)
		);
	}
}