<?php
namespace  Model\Localisation;

class Location extends \Model {
	public function addLocation($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "location SET geocode = '" . $this->db->escape((string)$data['geocode']) . "', image = '" . $this->db->escape((string)$data['image']) . "', issue = '" . (int)$data['issue'] . "', sort_order = '" . (int)$data['sort_order']. "', status = '" . (int)$data['status'] . "'");
	
		$location_id = $this->db->getLastId();

		foreach ($data['location_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "location_description SET location_id = '" . (int)$location_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape((string)$value['name']) . "', address = '" . $this->db->escape((string)$value['address']) . "', telephone = '" . $this->db->escape((string)$value['telephone']) . "', open = '" . $this->db->escape((string)$value['open']) . "', comment = '" . $this->db->escape((string)$value['comment']) . "'");
		}
		
		return $location_id;
	}

	public function editLocation($location_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "location SET geocode = '" . $this->db->escape((string)$data['geocode']) . "', image = '" . $this->db->escape((string)$data['image']) . "', issue = '" . (int)$data['issue'] . "', sort_order = '" . (int)$data['sort_order']. "', status = '" . (int)$data['status'] . "' WHERE location_id = '" . (int)$location_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "location_description WHERE location_id = '" . (int)$location_id . "'");

		foreach ($data['location_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "location_description SET location_id = '" . (int)$location_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape((string)$value['name']) . "', address = '" . $this->db->escape((string)$value['address']) . "', telephone = '" . $this->db->escape((string)$value['telephone']) . "', open = '" . $this->db->escape((string)$value['open']) . "', comment = '" . $this->db->escape((string)$value['comment']) . "'");
		}
	}

	public function deleteLocation($location_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "location WHERE location_id = " . (int)$location_id);
		$this->db->query("DELETE FROM " . DB_PREFIX . "location_description WHERE location_id = " . (int)$location_id);
	}

	public function getLocation($location_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "location` l LEFT JOIN " . DB_PREFIX . "location_description ld ON (l.location_id = ld.location_id) WHERE l.location_id = '" . (int)$location_id . "' AND ld.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getLocations($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "location l LEFT JOIN " . DB_PREFIX . "location_description ld ON (l.location_id = ld.location_id) WHERE ld.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		$sort_data = array(
			'ld.name',
			'ld.address',
			'l.sort_order',
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY ld.name";
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

		return $query->rows;
	}

	public function getLocationDescriptions($location_id) {
		$location_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "location_description WHERE location_id = '" . (int)$location_id . "'");

		foreach ($query->rows as $row) {
			$location_data[$row['language_id']] = $row;
		}

		return $location_data;
	}

	public function getTotalLocations() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "location");

		return $query->row['total'];
	}
}
