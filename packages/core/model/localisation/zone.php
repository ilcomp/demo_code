<?php
namespace  Model\Localisation;

class Zone extends \Model {
	public function addZone($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "zone SET status = '" . (int)$data['status'] . "', name = '" . $this->db->escape((string)$data['name']) . "', code = '" . $this->db->escape((string)$data['code']) . "', country_id = '" . (int)$data['country_id'] . "'");

		$this->cache->delete('zone');
		
		return $this->db->getLastId();
	}

	public function editZone($zone_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "zone SET status = '" . (int)$data['status'] . "', name = '" . $this->db->escape((string)$data['name']) . "', code = '" . $this->db->escape((string)$data['code']) . "', country_id = '" . (int)$data['country_id'] . "' WHERE zone_id = '" . (int)$zone_id . "'");

		$this->cache->delete('zone');
	}

	public function deleteZone($zone_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "zone WHERE zone_id = '" . (int)$zone_id . "'");

		$this->cache->delete('zone');
	}

	public function getZone($zone_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "zone WHERE zone_id = '" . (int)$zone_id . "'");

		return $query->row;
	}

	public function getZoneByName($name) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone WHERE name LIKE '" . $this->db->escape((string)$name) . "' LIMIT 1");

		return $query->row;
	}

	public function getZoneIdByName($name) {
		$query = $this->db->query("SELECT zone_id FROM " . DB_PREFIX . "zone WHERE name LIKE '" . $this->db->escape((string)$name) . "' AND status = '1' LIMIT 1");

		return $query->num_rows ? $query->row['zone_id'] : '';
	}

	public function getZones($data = array()) {
		$filter = isset($data['filter']) ? $data['filter'] : array();

		$sql = "SELECT z.*, cd.name AS country FROM " . DB_PREFIX . "zone z LEFT JOIN " . DB_PREFIX . "country_description cd ON (z.country_id = cd.country_id AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "')";

		if (isset($filter['status']))
			$sql .= " AND z.status = '" . (int)$filter['status'] . "'";

		if (isset($filter['name']))
			$sql .= " AND z.name LIKE '" . $this->db->escape((string)$filter['name']) . "'";

		if (isset($filter['country_id']))
			$sql .= " AND z.country_id LIKE '" . (int)$filter['country_id'] . "'";

		$sort_data = array(
			'country' => 'cd.name',
			'name' => 'z.name',
			'code' => 'z.code',
			'c.name' => 'cd.name',
			'z.name' => 'z.name',
			'z.code' => 'z.code'
		);

		if (isset($data['sort']) && isset($sort_data[$data['sort']])) {
			$sql .= " ORDER BY " . $sort_data[$data['sort']];
		} else {
			$sql .= " ORDER BY cd.name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		$sql .= ", z.name ASC";

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

	public function getZonesByCountryId($country_id) {
		$zone_data = $this->cache->get('zone.' . (int)$country_id);

		if (!$zone_data) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone WHERE country_id = '" . (int)$country_id . "' AND status = '1' ORDER BY name");

			$zone_data = $query->rows;

			$this->cache->set('zone.' . (int)$country_id, $zone_data);
		}

		return $zone_data;
	}

	public function getTotalZones() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "zone");

		return $query->row['total'];
	}

	public function getTotalZonesByCountryId($country_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "zone WHERE country_id = '" . (int)$country_id . "'");

		return $query->row['total'];
	}
}