<?php
namespace Model;
class DBTable {
	private $data = array();
	private $suffix = '';

	public function __construct($registry) {
		$this->db = $registry->get('db');
		$this->load = $registry->get('load');
	}

	public function setSuffix($suffix = '') {
		$this->suffix = $suffix;
	}

	public function setData($date) {
		$this->data = $date;
	}

	public function getData() {
		return $this->data;
	}

	public function create($schema_name = '') {
		$tables = $this->schema($schema_name);

		foreach ($tables as $table) {
			if ($this->suffix != '')
				$table['name'] .= '_' . $this->suffix; 

			$table_query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . $table['name'] . "'");

			if ($table_query->num_rows) {
				$this->db->query("DROP TABLE `" . DB_PREFIX . $table['name'] . "`");
			}

			$sql = "CREATE TABLE `" . DB_PREFIX . $table['name'] . "` (" . "\n";

			foreach ($table['field'] as $field) {
				$sql .= "  `" . $field['name'] . "` " . $field['type'] . (!empty($field['not_null']) ? " NOT NULL" : "") . (isset($field['default']) ? " DEFAULT '" . $this->db->escape($field['default']) . "'" : "") . (!empty($field['auto_increment']) ? " AUTO_INCREMENT" : "") . ",\n";
			}

			if (isset($table['primary'])) {
				$primary_data = array();
				foreach ($table['primary'] as $primary) {
					$primary_data[] = "`" . $primary . "`";
				}
				$sql .= "  PRIMARY KEY (" . implode(",", $primary_data) . "),\n";
			}

			if (isset($table['index'])) {
				foreach ($table['index'] as $index) {
					$index_data = array();
					foreach ($index['key'] as $key) {
						$index_data[] = "`" . $key . "`";
					}
					$sql .= "  KEY `" . $index['name'] . "` (" . implode(",", $index_data) . "),\n";
				}
			}

			$sql = rtrim($sql, ",\n") . "\n";
			$sql .= ") ENGINE=" . $table['engine'] . " CHARSET=" . $table['charset'] . " COLLATE=" . $table['collate'] . ";\n";

			$this->db->query($sql);
		}

		$sqls = $this->base($schema_name);

		foreach ($sqls as $sql) {
			$this->db->query(str_replace('oc_', DB_PREFIX, $sql));
		}
	}

	public function upgrade($schema_name) {
		$tables = $this->schema($schema_name);

		foreach ($tables as $table) {
			if ($this->suffix != '')
				$table['name'] .= '_' . $this->suffix; 

			$table_query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . $table['name'] . "'");

			if (!$table_query->num_rows) {
				$sql = "CREATE TABLE `" . DB_PREFIX . $table['name'] . "` (" . "\n";

				foreach ($table['field'] as $field) {
					$sql .= "  `" . $field['name'] . "` " . $field['type'] . (!empty($field['not_null']) ? " NOT NULL" : "") . (isset($field['default']) ? " DEFAULT '" . $this->db->escape($field['default']) . "'" : "") . (!empty($field['auto_increment']) ? " AUTO_INCREMENT" : "") . ",\n";
				}

				if (isset($table['primary'])) {
					$primary_data = array();

					foreach ($table['primary'] as $primary) {
						$primary_data[] = "`" . $primary . "`";
					}

					$sql .= "  PRIMARY KEY (" . implode(",", $primary_data) . "),\n";
				}

				if (isset($table['index'])) {
					foreach ($table['index'] as $index) {
						$index_data = array();

						foreach ($index['key'] as $key) {
							$index_data[] = "`" . $key . "`";
						}

						$sql .= "  KEY `" . $index['name'] . "` (" . implode(",", $index_data) . "),\n";
					}
				}

				$sql = rtrim($sql, ",\n") . "\n";
				$sql .= ") ENGINE=" . $table['engine'] . " CHARSET=" . $table['charset'] . " COLLATE=" . $table['collate'] . ";\n";

				$this->db->query($sql);
			} else {
				for ($i = 0; $i < count($table['field']); $i++) {
					$sql = "ALTER TABLE `" . DB_PREFIX . $table['name'] . "`";

					$field_query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . $table['name'] . "' AND COLUMN_NAME = '" . $table['field'][$i]['name'] . "'");

					if (!$field_query->num_rows) {
						$sql .= " ADD";
					} else {
						$sql .= " MODIFY";
					}

					$sql .= " `" . $table['field'][$i]['name'] . "` " . $table['field'][$i]['type'];

					if (!empty($table['field'][$i]['not_null'])) {
						$sql .= " NOT NULL";
					}

					if (isset($table['field'][$i]['default'])) {
						$sql .= " DEFAULT '" . $table['field'][$i]['default'] . "'";
					}

					if (!isset($table['field'][$i - 1])) {
						$sql .= " FIRST";
					} else {
						$sql .= " AFTER `" . $table['field'][$i - 1]['name'] . "`";
					}

					$this->db->query($sql);
				}

				// Remove all primary keys and indexes
				$keys = array();

				$query = $this->db->query("SHOW INDEXES FROM `" . DB_PREFIX . $table['name'] . "`");

				foreach ($query->rows as $result) {
					if (!in_array($result['Key_name'], $keys)) {
						$keys[] = $result['Key_name'];
					}
				}

				foreach ($keys as $key) {
					if ($key == 'PRIMARY') {
						$this->db->query("ALTER TABLE `" . DB_PREFIX . $table['name'] . "` DROP PRIMARY KEY");
					} else {
						$this->db->query("ALTER TABLE `" . DB_PREFIX . $table['name'] . "` DROP INDEX `" . $key . "`");
					}
				}

				// Primary Key
				if (isset($table['primary'])) {
					$primary_data = array();

					foreach ($table['primary'] as $primary) {
						$primary_data[] = "`" . $primary . "`";
					}

					$this->db->query("ALTER TABLE `" . DB_PREFIX . $table['name'] . "` ADD PRIMARY KEY(" . implode(",", $primary_data) . ")");
				}

				// Indexes
				if (isset($table['index'])) {
					foreach ($table['index'] as $index) {
						$index_data = array();

						foreach ($index['key'] as $key) {
							$index_data[] = "`" . $key . "`";
						}

						$this->db->query("ALTER TABLE `" . DB_PREFIX . $table['name'] . "` ADD INDEX `" . $index['name'] . "` (" . implode(",", $index_data) . ")");
					}
				}

				// DB Engine
				if (isset($table['engine'])) {
					$this->db->query("ALTER TABLE `" . DB_PREFIX . $table['name'] . "` ENGINE = `" . $table['engine'] . "`");
				}

				// Charset
				if (isset($table['charset'])) {
					$sql = "ALTER TABLE `" . DB_PREFIX . $table['name'] . "` DEFAULT CHARACTER SET `" . $table['charset'] . "`";

					if (isset($table['collate'])) {
						$sql .= " COLLATE `" . $table['collate'] . "`";
					}

					$this->db->query($sql);
				}
			}
		}
	}

	public function delete($schema_name) {
		$tables = $this->schema($schema_name);

		foreach ($tables as $table) {
			if ($this->suffix != '')
				$table['name'] .= '_' . $this->suffix; 

			$table_query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . $table['name'] . "'");

			if ($table_query->num_rows) {
				$this->db->query("DROP TABLE `" . DB_PREFIX . $table['name'] . "`");
			}
		}
	}

	public function update($schema_name = '') {
		$this->select($schema_name);
		$this->delete($schema_name);
		$this->create($schema_name);
		$this->insert($schema_name);
	}

	public function insert($schema_name = '') {
		$tables = $this->schema($schema_name);

		foreach ($tables as $table) {
			if ($this->suffix != '')
				$table['name'] .= '_' . $this->suffix; 

			$table_query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . $table['name'] . "'");

			if ($table_query->num_rows && isset($this->data[$table['name']])) {
				$fields = '';
				$values = array();

				foreach ($this->data[$table['name']] as $row) {
					$value = array();

					foreach ($table['field'] as $field) {
						if (isset($row[$field['name']])) {
							$value[$field['name']] = $row[$field['name']];
						}
					}

					if (!empty($value)) {
						if (!$fields) $fields = array_keys($value);

						$values[] = "('" . implode("','", $value) . "')";
					}
				}

				if (!empty($values))
					$this->db->query("INSERT INTO `" . DB_PREFIX . $table['name'] . "` (`" . implode('`,`', $fields) . "`) VALUE " . implode(',', $values) . "");
			}
		}
	}

	public function select($schema_name = '') {
		$tables = $this->schema($schema_name);

		$this->data = array();

		foreach ($tables as $table) {
			if ($this->suffix != '')
				$table['name'] .= '_' . $this->suffix; 

			$table_query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . $table['name'] . "'");

			if ($table_query->num_rows) {
				$table_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . $table['name'] . "`");
				if ($table_query->num_rows) {
					$this->data[$table['name']] = $table_query->rows;
				}
			}
		}
	}

	private function schema($schema_name = '') {
		$tables = array();

		if ($schema_name) {
			$files = glob(__DIR__ . '/schema/' . $schema_name . '.php');

			foreach ($files as $file) {
				require($file);
			}
		}

		return $tables;
	}

	private function base($base_name = '') {
		$base = array();

		if ($base_name) {
			$files = glob(DIR_MODEL . '/base/' . $base_name . '/*.sql');

			foreach ($files as $file) {
				foreach (file($file) as $line) {
					$base[] = $line;
				}
			}
		}

		return $base;
	}
}