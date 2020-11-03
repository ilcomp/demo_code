<?php
namespace  Model\Design;

class Form extends \Model {
	public function addForm($data) {
		if (isset($data['setting']))
			$data['setting'] = is_array($data['setting']) ? json_encode($data['setting']) : html_entity_decode($data['setting'], ENT_QUOTES);
		else
			$data['setting'] = '';

		$this->db->query("INSERT INTO `" . DB_PREFIX . "form` SET setting = '" . $this->db->escape($data['setting']) . "', email = '" . $this->db->escape($data['email']) . "', status = '" . (int)$data['status'] . "'");

		$form_id = $this->db->getLastId();

		foreach ($data['description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "form_description SET form_id = '" . (int)$form_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', title = '" . $this->db->escape($value['title']) . "'");
		}

		foreach ($data['form_field'] as $field) {
			if (isset($field['setting']))
				$field['setting'] = is_array($field['setting']) ? json_encode($field['setting']) : html_entity_decode($field['setting'], ENT_QUOTES);
			else
				$field['setting'] = '';

			if (!isset($field['required']))
				$field['required'] = 0;

			$this->db->query("INSERT INTO " . DB_PREFIX . "form_field SET form_id = '" . (int)$form_id . "', `type` = '" . $this->db->escape($field['type']) . "', `code` = '" . $this->db->escape($field['code']) . "', `default` = '" . $this->db->escape($field['default']) . "', `required` = '" . (int)$field['required'] . "', sort_order = '" . (int)$field['sort_order'] . "', setting = '" . $this->db->escape($field['setting']) . "'");

			$form_field_id = $this->db->getLastId();

			foreach ($field['description'] as $language_id => $value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "form_field_description SET form_field_id = '" . (int)$form_field_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', help = '" . $this->db->escape($value['help']) . "', error = '" . $this->db->escape($value['error']) . "'");
			}
		}
		
		return $form_id;
	}

	public function editForm($form_id, $data) {
		if (isset($data['setting']))
			$data['setting'] = is_array($data['setting']) ? json_encode($data['setting']) : html_entity_decode($data['setting'], ENT_QUOTES);
		else
			$data['setting'] = '';

		$this->db->query("UPDATE `" . DB_PREFIX . "form` SET setting = '" . $this->db->escape($data['setting']) . "', email = '" . $this->db->escape($data['email']) . "', status = '" . (int)$data['status'] . "' WHERE form_id = '" . (int)$form_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "form_description WHERE form_id = '" . (int)$form_id . "'");

		foreach ($data['description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "form_description SET form_id = '" . (int)$form_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', title = '" . $this->db->escape($value['title']) . "'");
		}

		$this->db->query("DELETE ff, ffd FROM `" . DB_PREFIX . "form_field` ff LEFT JOIN `" . DB_PREFIX . "form_field_description` ffd ON (ff.form_field_id = ffd.form_field_id) WHERE ff.form_id = '" . (int)$form_id . "'");

		foreach ($data['form_field'] as $field) {
			if (isset($field['setting']))
				$field['setting'] = is_array($field['setting']) ? json_encode($field['setting']) : html_entity_decode($field['setting'], ENT_QUOTES);
			else
				$field['setting'] = '';

			if (!isset($field['required']))
				$field['required'] = 0;

			$this->db->query("INSERT INTO " . DB_PREFIX . "form_field SET form_id = '" . (int)$form_id . "', `type` = '" . $this->db->escape($field['type']) . "', `code` = '" . $this->db->escape($field['code']) . "', `default` = '" . $this->db->escape($field['default']) . "', `required` = '" . (int)$field['required'] . "', sort_order = '" . (int)$field['sort_order'] . "', setting = '" . $this->db->escape($field['setting']) . "'");

			$form_field_id = $this->db->getLastId();

			foreach ($field['description'] as $language_id => $value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "form_field_description SET form_field_id = '" . (int)$form_field_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', help = '" . $this->db->escape($value['help']) . "', error = '" . $this->db->escape($value['error']) . "'");
			}
		}
	}

	public function deleteForm($form_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "form` WHERE form_id = '" . (int)$form_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "form_description` WHERE form_id = '" . (int)$form_id . "'");
		$this->db->query("DELETE ff, ffd FROM `" . DB_PREFIX . "form_field` ff LEFT JOIN `" . DB_PREFIX . "form_field_description` ffd ON (ff.form_field_id = ffd.form_field_id) WHERE ff.form_id = '" . (int)$form_id . "'");
	}

	public function getForm($form_id) {
		$query = $this->db->query("SELECT fd.*, f.* FROM `" . DB_PREFIX . "form` f LEFT JOIN " . DB_PREFIX . "form_description fd ON (f.form_id = fd.form_id AND fd.language_id = '" . (int)$this->config->get('config_language_id') . "') WHERE f.form_id = '" . (int)$form_id . "'");

		if ($query->num_rows) {
			$query->row['setting'] = json_decode($query->row['setting'], true);
		}

		return $query->row;
	}

	public function getForms($data = array()) {
		$filter = isset($data['filter']) ? (array)$data['filter'] : array();

		$sql = "SELECT f.form_id FROM `" . DB_PREFIX . "form` f LEFT JOIN " . DB_PREFIX . "form_description fd ON (f.form_id = fd.form_id AND fd.language_id = '" . (int)$this->config->get('config_language_id') . "') WHERE 1";

		if (isset($filter['status'])) {
			$sql .= " AND f.status = '" . (int)$filter['status'] . "'";
		}

		$sql .= " GROUP BY f.form_id";

		$sort_data = array(
			'form_id' => 'f.form_id',
			'name' => 'fd.name',
			'status' => 'f.status'
		);

		if (isset($data['sort']) && isset($sort_data[$data['sort']])) {
			$sql .= " ORDER BY " . $sort_data[$data['sort']];
		} else {
			$sql .= " ORDER BY f.form_id";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		$query = $this->db->query($sql);

		foreach ($query->rows as &$row) {
			$row = $this->getForm($row['form_id']);
		}
		unset($row);

		return $query->rows;
	}

	public function getFormDescriptions($form_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "form_description WHERE form_id = '" . (int)$form_id . "'");

		return array_column($query->rows, null, 'language_id');
	}

	public function getFormFields($form_id, $data = array()) {
		$query = $this->db->query("SELECT ffd.*, ff.* FROM `" . DB_PREFIX . "form_field` ff LEFT JOIN " . DB_PREFIX . "form_field_description ffd ON (ff.form_field_id = ffd.form_field_id AND ffd.language_id = '" . (int)$this->config->get('config_language_id') . "') WHERE ff.form_id = '" . (int)$form_id . "' ORDER BY ff.sort_order");

		foreach ($query->rows as &$row) {
			$row['setting'] = json_decode($row['setting'], true);

			if (!empty($data['description']))
				$row['description'] = $this->getFormFieldDescriptions($row['form_field_id']);
		}
		unset($row);

		return $query->rows;
	}

	public function getFormFieldDescriptions($form_field_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "form_field_description WHERE form_field_id = '" . (int)$form_field_id . "'");

		return array_column($query->rows, null, 'language_id');
	}

	public function getAsValue($value, $form) {
		switch ($form['type']) {
			case 'text':
			case 'textarea':
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

					$listing_id = isset($form['setting']['listing_id']) ? $form['setting']['listing_id'] : '';

					if (is_numeric($listing_id) && !empty($value)) {
						$result = array();

						foreach (json_decode($value, true) as $listing_item_id) {
							$listing_item = $this->model_localisation_listing->getListingItem($listing_item_id);

							if ($listing_item['image'])
								$listing_item['image'] = $this->model_tool_image->link($listing_item['image']);

							$result[] = $listing_item;
						}
					} elseif (is_numeric($listing_id)) {
						$result = '';
					} else {
						$result = $value;
					}

				break;

			case 'image':
				$this->load->model('tool/image');

				$thumb_width = isset($form['setting']['width']) ? $form['setting']['width'] : 100;
				$thumb_height = isset($form['setting']['height']) ? $form['setting']['height'] : 100;

				$image['image'] = $value;

				if ($image['image'] && is_file(DIR_IMAGE . $image['image'])) {
					$image['thumb'] = $this->model_tool_image->resize($image['image'], $thumb_width, $thumb_height);
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

					$result = $country['name'];

				break;

			case 'zone':
					$this->load->model('localisation/zone');

					$zone = $this->model_localisation_zone->getZone($value);

					$result = $zone['name'];

				break;

			default:
				$result = $value;

				break;
		}

		return $result;
	}

	public function getAsField($values, $form_id) {
		$this->load->model('localisation/listing');
		$this->load->model('localisation/country');
		$this->load->model('tool/image');

		$fields = $this->getFormFields($form_id);

		$values = (array)$values;

		foreach ($fields as &$field) {
			$field['value'] = isset($values[$field['form_id']]) ? $values[$field['form_id']] : '';

			$value_result = $this->event->trigger('model/design/form/render_value/before', array('model/design/form/render_value/before', &$field));

			if (!$value_result) {
				switch ($field['type']) {
					case 'select':
					case 'radio':
						$listing_id = isset($field['setting']['listing_id']) ? $field['setting']['listing_id'] : '';

						$field['listing_items'] = (array)$this->model_localisation_listing->getListingItems(array('filter_listing_id' => $listing_id));

						break;
					case 'checkbox':
						$listing_id = isset($field['setting']['listing_id']) ? $field['setting']['listing_id'] : '';

						if (is_numeric($listing_id)) {
							$field['listing_items'] = (array)$this->model_localisation_listing->getListingItems(array('filter_listing_id' => $listing_id));

							$field['value'] = json_decode($field['value'], true);
						} else {
							$field['listing_items'] = '';
						}

						break;
					case 'image':
						$thumb_width = $this->config->get('admin_image_thumb_width') ? $this->config->get('admin_image_thumb_width') : 100;
						$thumb_height = $this->config->get('admin_image_thumb_height') ? $this->config->get('admin_image_thumb_height') : 100;

						$field['placeholder'] = $this->model_tool_image->resize('no_image.png', $thumb_width, $thumb_height);

						$image['image'] = $field['value'];

						if ($image['image'] && is_file(DIR_IMAGE . $image['image'])) {
							$image['thumb'] = $this->model_tool_image->resize($image['image'], $thumb_width, $thumb_height);
						} else {
							$image['thumb'] = $field['placeholder'];
						}

						$field['value'] = $image;

						break;
					case 'country':
						$field['countries'] = $this->model_localisation_country->getCountries();

						if (!$field['value'])
							$field['value'] = isset($this->session->data['country_id']) ? $this->session->data['country_id'] : $this->config->get('config_country_id');

						break;
					case 'zone':
						if (!$field['value'])
							$field['value'] = isset($this->session->data['zone_id']) ? $this->session->data['zone_id'] : $this->config->get('config_zone_id');

						break;
				}
			}
		}
		unset($field);

		return $fields;
	}

	public function getTypes() {
		$this->load->language('design/form');

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
					),
					array(
						'value' => 'hidden',
						'name' => $this->language->get('text_hidden'),
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
			)
		);
	}
}