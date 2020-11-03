<?php
namespace  Model\Design;

class Menu extends \Model {
	public function addMenu($data) {
		$data['setting'] = isset($data['setting']) ? json_encode($data['setting']) : '';
		$menu_id = false;

		foreach ($data['name'] as $language_id => $name) {
			if ($menu_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "menu SET menu_id = '" . (int)$menu_id . "', store_id = '" . (int)$data['store_id'] . "', language_id = '" . (int)$language_id . "', name = '" .  $this->db->escape($name) . "', position = '" . $this->db->escape($data['position']) . "', setting = '" . $this->db->escape($data['setting']) . "', status = '" . (int)$data['status'] . "'");
			} else {
				$this->db->query("INSERT INTO " . DB_PREFIX . "menu SET store_id = '" . (int)$data['store_id'] . "', language_id = '" . (int)$language_id . "', name = '" .  $this->db->escape($name) . "', position = '" . $this->db->escape($data['position']) . "', setting = '" . $this->db->escape($data['setting']) . "', status = '" . (int)$data['status'] . "'");

				$menu_id = $this->db->getLastId();
			}

			if (isset($data['menu_item'][$language_id])) {
				foreach ($data['menu_item'][$language_id] as $menu_item) {
					$menu_item['setting'] = isset($menu_item['setting']) ? json_encode($menu_item['setting']) : '';

					$this->db->query("INSERT INTO " . DB_PREFIX . "menu_item SET menu_id = '" . (int)$menu_id . "', language_id = '" . (int)$language_id . "', code = '" . $this->db->escape($menu_item['code']) . "', sort_order = '" . (int)$menu_item['sort_order'] . "', title = '" .  $this->db->escape($menu_item['title']) . "', link = '" .  $this->db->escape($menu_item['link']) . "', setting = '" . $this->db->escape($menu_item['setting']) . "'");
				}
			}
		}

		return $menu_id;
	}

	public function editMenu($menu_id, $data) {
		$data['setting'] = isset($data['setting']) ? json_encode($data['setting']) : '';

		$this->db->query("DELETE FROM " . DB_PREFIX . "menu WHERE menu_id = '" . (int)$menu_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "menu_item WHERE menu_id = '" . (int)$menu_id . "'");

		foreach ($data['name'] as $language_id => $name) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "menu SET menu_id = '" . (int)$menu_id . "', store_id = '" . (int)$data['store_id'] . "', language_id = '" . (int)$language_id . "', name = '" .  $this->db->escape($name) . "', position = '" . $this->db->escape($data['position']) . "', setting = '" . $this->db->escape($data['setting']) . "', status = '" . (int)$data['status'] . "'");


			if (isset($data['menu_item'][$language_id])) {
				foreach ($data['menu_item'][$language_id] as $menu_item) {
					$menu_item['setting'] = isset($menu_item['setting']) ? json_encode($menu_item['setting']) : '';

					$this->db->query("INSERT INTO " . DB_PREFIX . "menu_item SET menu_id = '" . (int)$menu_id . "', language_id = '" . (int)$language_id . "', code = '" . $this->db->escape($menu_item['code']) . "', sort_order = '" . (int)$menu_item['sort_order'] . "', title = '" .  $this->db->escape($menu_item['title']) . "', link = '" .  $this->db->escape($menu_item['link']) . "', setting = '" . $this->db->escape($menu_item['setting']) . "'");
				}
			}
		}
	}

	public function deleteMenu($menu_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "menu WHERE menu_id = '" . (int)$menu_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "menu_item WHERE menu_id = '" . (int)$menu_id . "'");
	}

	public function getMenu($menu_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "menu WHERE menu_id = '" . (int)$menu_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

		if ($query->row)
			$query->row['setting'] = json_decode($query->row['setting'], true);

		return $query->row;
	}

	public function getMenuIdByPosition($position) {
		$query = $this->db->query("SELECT menu_id FROM " . DB_PREFIX . "menu WHERE position = '" . $this->db->escape($position) . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "' LIMIT 1");

		return $query->row ? (int)$query->row['menu_id'] : 0;
	}

	public function getMenus($data = array()) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "menu WHERE language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY position, language_id, name");

		foreach ($query->rows as $key => &$value) {
			$value['setting'] = json_decode($value['setting'], true);
		}
		unset($value);

		return $query->rows;
	}

	public function getMenuDescriptions($menu_id) {
		$menu_data = array();

		$query = $this->db->query("SELECT name, language_id FROM " . DB_PREFIX . "menu WHERE menu_id = '" . (int)$menu_id . "'");

		foreach ($query->rows as $result) {
			$menu_data[$result['language_id']] = $result['name'];
		}

		return $menu_data;
	}

	public function getMenuItems($menu_id) {
		$menu_item_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "menu_item WHERE menu_id = '" . (int)$menu_id . "' ORDER BY sort_order");

		foreach ($query->rows as $result) {
			$result['setting'] = json_decode($result['setting'], true);

			$menu_item_data[$result['language_id']][] = $result;
		}

		return $menu_item_data;
	}

	public function getMenuItemsByLanguage($menu_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "menu_item WHERE menu_id = '" . (int)$menu_id . "' AND language_id='" . (int)$this->config->get('config_language_id') . "' ORDER BY sort_order");

		foreach ($query->rows as &$row) {
			$row['setting'] = json_decode($row['setting'], true);
		}
		unset($row);

		return $query->rows;
	}
}
