<?php
namespace Model\Extension\System;

class SxGeo extends \Model {
	private $count = 5;

	public function editSxGeo($sxgeo_id, $data) {
		if (!isset($data['auto_update']))
			$data['auto_update'] = 0;

		$this->db->query("UPDATE " . DB_PREFIX . "sxgeo SET name = '" . $this->db->escape((string)$data['name']) . "', speed_min = '" . (int)$data['speed_min'] . "', speed_max = '" . (int)$data['speed_max'] . "', sort_order = '" . (int)$data['sort_order'] . "', auto_update = '" . (int)$data['auto_update'] . "' WHERE sxgeo_id = '" . (int)$sxgeo_id . "'");
	}

	public function getSxGeo($level, $sxgeo_id) {
		$query = $this->db->query("SELECT *  FROM " . DB_PREFIX . "sxgeo WHERE `level` = '" . (int)$level . "' AND sxgeo_id = '" . (int)$sxgeo_id . "'");

		return $query->row;
	}

	public function getSxGeos($data = array()) {
		$sql = "SELECT s2.*, s1.name as region  FROM " . DB_PREFIX . "sxgeo s2 LEFT JOIN sxgeo s1 ON (s1.level = '2' AND s1.sxgeo_id = s2.parent_id)";

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$order = " DESC";
		} else {
			$order = " ASC";
		}

		if (isset($data['sort'])) {
			switch ($data['sort']) {
				case 'sort_order': $order_by[] = "s2.sort_order" . $order; break;
				default: $order_by[] = "s1.name" . $order; break;
			}
		}

		$order_by[] = "s2.name" . $order;

		$order_by = array_unique($order_by);

		if (!empty($order_by))
			$sql .= ' ORDER BY ' . implode(', ', $order_by);

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

	public function getTotalSxGeos($data = array()) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "sxgeo");

		return $query->row['total'];
	}

	public function getSxGeosByNameA($name, $country = '') {
		$sql = "SELECT * FROM " . DB_PREFIX . "sxgeo WHERE `name` LIKE '%" . $this->db->escape((string)$name) . "%'";

		if ($country)
			$sql .= " AND `country` LIKE '" . $this->db->escape((string)$country) . "'";

		$sql .= " ORDER BY sort_order ASC, name ASC LIMIT 5";

		$query = $this->db->query($sql);

		$result = array_column($query->rows, null, 'sxgeo_id');

		if ($query->num_rows < 5) {
			foreach ($query->rows as $row) {
				if ($row['level'] == 1) {
					$query_region = $this->db->query("SELECT * FROM " . DB_PREFIX . "sxgeo WHERE `level` = 2 AND parent_id = '" . int($row['sxgeo_id']) . "' ORDER BY sort_order ASC, `name` ASC LIMIT " . (5 - count($result)));

					foreach ($query_region->rows as $value) {
						$result[$value['sxgeo_id']] = $value;
					}

					if (count($result) >= 5)
						break;
				}
			}
		}

		return $result;
	}

	public function getSxGeosByName($name, $country = '') {
		$sql = "SELECT s2.*, s1.name as region FROM " . DB_PREFIX . "sxgeo s2 LEFT JOIN " . DB_PREFIX . "sxgeo s1 ON (s1.`level` = '1' AND s1.sxgeo_id = s2.parent_id) WHERE s2.`level` = 2";

		if ($name) {
			$names = explode(',', str_replace(', ', ',', $name));

			$name1 = $names[0];
			$name2 = isset($names[1]) && $names[0] != $names[1] ? $names[1] : '';

			$implode = array();

			if ($name2) {
				$implode[] = "(s2.`name` LIKE '" . $this->db->escape((string)$name1) . "%' AND s1.`name` LIKE '" . $this->db->escape((string)$name2) . "%')";
				$implode[] = "(s2.`name` LIKE '" . $this->db->escape((string)$name2) . "%' AND s1.`name` LIKE '" . $this->db->escape((string)$name1) . "%')";
			} else {
				$implode[] = "s2.`name` LIKE '" . $this->db->escape((string)$name1) . "%'";
			}

			$sql .= " AND (" . implode(" OR ", $implode) . ")";
		}

		if ($country)
			$sql .= " AND s.country LIKE '" . $this->db->escape((string)$country) . "'";

		$sql .= " ORDER BY s2.sort_order ASC, s2.name ASC LIMIT " . (int)$this->count;

		$query = $this->db->query($sql);

		$result = $query->rows;

		if ($query->num_rows < $this->count && !$name2) {
			$cities = array();

			foreach ($query->rows as $row) {
				if ($row['name'])
					$cities[] = "'" . $this->db->escape((string)$row['name']) . "'";
			}

			$cities = !empty($cities) ? "AND s2.`name` NOT IN (" . implode(",", $cities) . ") " : '';

			$regions = array();

			$sql = "SELECT s2.*, s1.name as region FROM " . DB_PREFIX . "sxgeo s2 LEFT JOIN " . DB_PREFIX . "sxgeo s1 ON (s1.`level` = '1' AND s1.sxgeo_id = s2.parent_id) WHERE s2.`level` = 2 AND (" . str_replace("s2.", "s1.", implode(" OR ", $implode)) . ") " . $cities;

			if ($country)
				$sql .= " AND s.country LIKE '" . $this->db->escape((string)$country) . "'";

			$sql .= " ORDER BY s1.sort_order ASC, s1.name ASC, s2.sort_order ASC, s2.name ASC LIMIT " . ($this->count - $query->num_rows);

			$query = $this->db->query($sql);

			$result = array_merge($result, $query->rows);
		}

		foreach ($result as &$row) {
			$row['fullname'] = $row['name'] . ($row['region'] && $row['region'] != $row['name'] ? ', ' . $row['region'] : '');
		}
		unset($row);

		return $result;
	}

	public function update($data) {
		$table = new \Model\DBTable($this->registry);
		$table->setSuffix('temp');
		$table->create('sxgeo');

		$implode = array();

		foreach ($data as $item) {
			if ($item['name'] != '')
				$implode[] = "('" . (int)$item['level'] . "','" . (int)$item['sxgeo_id'] . "','" . (int)$item['parent_id']  . "','" . $this->db->escape((string)$item['name']) . "','" . $this->db->escape((string)$item['country']) . "')";
		}

		// $this->db->query("TRUNCATE " . DB_PREFIX . "sxgeo");
		$this->db->query("TRUNCATE " . DB_PREFIX . "sxgeo_temp");

		foreach (array_chunk($implode, 1000) as $part) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "sxgeo_temp (`level`, `sxgeo_id`, `parent_id`, `name`, `country`) VALUES " . implode(',', $part));
		}

		$this->db->query("INSERT INTO " . DB_PREFIX . "sxgeo SELECT st.* FROM " . DB_PREFIX . "sxgeo_temp st LEFT JOIN " . DB_PREFIX . "sxgeo s ON (st.`level` = s.`level` AND st.sxgeo_id = s.sxgeo_id) WHERE s.auto_update = 1 IS NULL OR s.auto_update = 1 ON DUPLICATE KEY UPDATE parent_id = st.parent_id, name = st.name, country = st.country");

		$this->db->query("UPDATE " . DB_PREFIX . "sxgeo s2 LEFT JOIN " . DB_PREFIX . "sxgeo s1 ON (s1.`level` = '1' AND s1.sxgeo_id = s2.parent_id) SET s2.country = s1.country WHERE s2.`level` = '2'");

		$table->delete('sxgeo');
	}
}