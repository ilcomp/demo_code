<?php
namespace  Model\Localisation;

class Country extends \Model {
	public function addCountry($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "country SET iso_code_2 = '" . $this->db->escape((string)$data['iso_code_2']) . "', iso_code_3 = '" . $this->db->escape((string)$data['iso_code_3']) . "', iso_number= '" . (int)$data['iso_number'] . "', status = '" . (int)$data['status'] . "'");
	
		$country_id = $this->db->getLastId();

		foreach ($data['country_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "country_description SET country_id = '" . (int)$country_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape((string)$value['name']) . "', full_name = '" . $this->db->escape((string)$value['full_name']) . "'");
		}
		
		return $this->db->getLastId();
	}

	public function editCountry($country_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "country SET iso_code_2 = '" . $this->db->escape((string)$data['iso_code_2']) . "', iso_code_3 = '" . $this->db->escape((string)$data['iso_code_3']) . "', iso_number= '" . (int)$data['iso_number'] . "', status = '" . (int)$data['status'] . "' WHERE country_id = '" . (int)$country_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "country_description WHERE country_id = '" . (int)$country_id . "'");

		foreach ($data['country_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "country_description SET country_id = '" . (int)$country_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape((string)$value['name']) . "', full_name = '" . $this->db->escape((string)$value['full_name']) . "'");
		}
	}

	public function deleteCountry($country_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "country WHERE country_id = '" . (int)$country_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "country_description WHERE country_id = " . (int)$country_id);
	}

	public function getCountry($country_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "country c LEFT JOIN " . DB_PREFIX . "country_description cd ON (c.country_id = cd.country_id) WHERE c.country_id = '" . (int)$country_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getCountryByIsoCode2($iso_code_2) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "country c LEFT JOIN " . DB_PREFIX . "country_description cd ON (c.country_id = cd.country_id) WHERE c.iso_code_2 = '" . $this->db->escape((string)$iso_code_2) . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' LIMIT 1");

		return $query->row;
	}

	public function getCountries($data = array()) {
		if ($data) {
			$filter = isset($data['filter']) ? $data['filter'] : array();

			$sql = "SELECT * FROM " . DB_PREFIX . "country c LEFT JOIN " . DB_PREFIX . "country_description cd ON (c.country_id = cd.country_id) WHERE cd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

			if (isset($filter['status']))
				$sql .= " AND c.status = '" . (int)$filter['status'] . "'";

			if (isset($filter['name']))
				$sql .= " AND cd.name LIKE '" . $this->db->escape((string)$filter['name']) . "'";

			if (isset($filter['full_name']))
				$sql .= " AND cd.full_name LIKE '" . $this->db->escape((string)$filter['full_name']) . "'";

			$sort_data = array(
				'name' => 'cd.name',
				'iso_code_2' => 'c.iso_code_2',
				'iso_code_3' => 'c.iso_code_3',
				'sort_order' => 'c.sort_order'
			);

			if (isset($data['sort']) && isset($sort_data[$data['sort']])) {
				$sql .= " ORDER BY " . $sort_data[$data['sort']];
			} else {
				$sql .= " ORDER BY c.sort_order";
			}

			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$sql .= " DESC";
			} else {
				$sql .= " ASC";
			}

			$sql .= ", cd.name ASC";

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
		} else {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "country_description WHERE language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY name ASC");

			return $query->rows;
		}
	}

	public function getTotalCountries($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "country";

		if (isset($filter['status']))
			$sql .= " WHERE status = '" . (int)$filter['status'] . "'";

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getCountryDescriptions($country_id) {
		$country_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "country_description WHERE country_id = '" . (int)$country_id . "'");

		foreach ($query->rows as $row) {
			$country_data[$row['language_id']] = $row;
		}

		return $country_data;
	}
}