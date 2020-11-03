<?php
namespace  Model\Localisation;

class Listing extends \Model {
	public function addListing($data) {
		//if (!$this->permission->has($route, 'modify')) {
			$listing_id = false;

			$this->db->query("INSERT INTO " . DB_PREFIX . "listing SET type = '" . $this->db->escape($data['type']) . "', readonly = '" . (int)!empty($data['readonly']) . "', hidden = '" . (int)!empty($data['hidden']) . "'");

			$listing_id = $this->db->getLastId();

			foreach ($data['name'] as $language_id => $name) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "listing_description SET listing_id = '" . (int)$listing_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($name) . "'");
			}

			return $listing_id;
		//}
	}

	public function editListing($listing_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "listing SET type = '" . $this->db->escape($data['type']) . "', readonly = '" . (int)!empty($data['readonly']) . "', hidden = '" . (int)!empty($data['hidden']) . "' WHERE listing_id = '" . (int)$listing_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "listing_description WHERE listing_id = '" . (int)$listing_id . "'");

		foreach ($data['name'] as $language_id => $name) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "listing_description SET listing_id = '" . (int)$listing_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($name) . "'");
		}
	}

	public function deleteListing($listing_id) {
		$this->db->query("DELETE li, lid FROM " . DB_PREFIX . "listing_item li LEFT JOIN listing_item_description lid ON (li.listing_item_id = lid.listing_item_id) WHERE li.listing_id = '" . (int)$listing_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "listing_description WHERE listing_id = '" . (int)$listing_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "listing WHERE listing_id = '" . (int)$listing_id . "'");
	}

	public function getListing($listing_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "listing l LEFT JOIN " . DB_PREFIX . "listing_description ld ON (l.listing_id = ld.listing_id) WHERE l.listing_id = '" . (int)$listing_id . "' AND ld.language_id = '" . (int)$this->config->get('config_language_id') . "' LIMIT 1");

		return $query->row;
	}

	public function getListings($hidden = 0) {
		$sql = "SELECT * FROM " . DB_PREFIX . "listing l LEFT JOIN " . DB_PREFIX . "listing_description ld ON (l.listing_id = ld.listing_id) WHERE ld.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if ($hidden == 0)
			$sql .= " AND l.hidden = '0'";

		$sql .= " ORDER BY ld.name";

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getListingDescriptions($listing_id) {
		$query = $this->db->query("SELECT name, language_id FROM " . DB_PREFIX . "listing_description WHERE listing_id = '" . (int)$listing_id . "'");

		return array_column($query->rows, 'name', 'language_id');
	}

	public function addListingItem($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "listing_item SET listing_id = '" . (int)$data['listing_id'] . "', `value` = '" . $this->db->escape($data['value']) . "', image = '" . $this->db->escape($data['image']) . "', sort_order = '" . (int)$data['sort_order'] . "'");

		$listing_item_id = $this->db->getLastId();

		foreach ($data['description'] as $language_id => $description) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "listing_item_description SET listing_item_id = '" . (int)$listing_item_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($description['name']) . "', description = '" . $this->db->escape($description['description']) . "'");
		}

		return $listing_item_id;
	}

	public function editListingItem($listing_item_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "listing_item SET listing_id = '" . (int)$data['listing_id'] . "', `value` = '" . $this->db->escape($data['value']) . "', image = '" . $this->db->escape($data['image']) . "', sort_order = '" . (int)$data['sort_order'] . "' WHERE listing_item_id = '" . (int)$listing_item_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "listing_item_description WHERE listing_item_id = '" . (int)$listing_item_id . "'");

		foreach ($data['description'] as $language_id => $description) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "listing_item_description SET listing_item_id = '" . (int)$listing_item_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($description['name']) . "', description = '" . $this->db->escape($description['description']) . "'");
		}
	}

	public function deleteListingItem($listing_item_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "listing_item WHERE listing_item_id = '" . (int)$listing_item_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "listing_item_description WHERE listing_item_id = '" . (int)$listing_item_id . "'");
	}

	public function getListingItem($listing_item_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "listing_item li LEFT JOIN " . DB_PREFIX . "listing_item_description lid ON (li.listing_item_id = lid.listing_item_id) LEFT JOIN " . DB_PREFIX . "listing l ON (li.listing_id = l.listing_id) WHERE li.listing_item_id = '" . (int)$listing_item_id . "' AND lid.language_id = '" . (int)$this->config->get('config_language_id') . "' LIMIT 1");

		return $query->row;
	}

	public function getListingItems($data) {
		$sql = "SELECT * FROM " . DB_PREFIX . "listing_item li LEFT JOIN " . DB_PREFIX . "listing_item_description lid ON (li.listing_item_id = lid.listing_item_id AND lid.language_id = '" . (int)$this->config->get('config_language_id') . "') LEFT JOIN " . DB_PREFIX . "listing l ON (li.listing_id = l.listing_id) WHERE 1";

		if (isset($data['filter_listing_id']) && is_numeric($data['filter_listing_id'])) {
			$sql .= " AND li.listing_id = '" . (int)$data['filter_listing_id'] . "'";

			if (!empty($data['filter_name'])) {
				$sql .= " AND lid.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
			}
		} else {
			if (!empty($data['filter_name'])) {
				$sql .= " AND lid.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
			}
		}

		$sql .= " ORDER BY li.sort_order, li.value, lid.name";

		if (isset($data['start']) || isset($data['limit'])) {
			if (!isset($data['start']) || $data['start'] < 0) {
				$data['start'] = 0;
			}

			if (!isset($data['limit']) || $data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getListingItemDescriptions($listing_item_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "listing_item_description WHERE listing_item_id = '" . (int)$listing_item_id . "'");

		return array_column($query->rows, null, 'language_id');
	}
}