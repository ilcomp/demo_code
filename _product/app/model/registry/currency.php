<?php
namespace Model\Registry;

class Currency {
	private $by_codes = array();
	private $by_ids = array();

	public function __construct($registry) {
		$this->db = $registry->get('db');
		$this->language = $registry->get('language');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "currency");

		$this->by_codes = array_column($query->rows, null, 'code');
		$this->by_ids = array_column($query->rows, null, 'currency_id');
	}

	public function format($number, $currency) {
		$currency = $this->get($currency);

		if (!isset($currency['decimal_place'])) {
			$currency['decimal_place'] = 0;
		}

		$string = '';

		if (isset($currency['symbol_left'])) {
			$string .= $currency['symbol_left'];
		}

		$string .= number_format($number, (int)$currency['decimal_place'], $this->language->get('decimal_point'), $this->language->get('thousand_point'));

		if (isset($currency['symbol_right'])) {
			$string .= $currency['symbol_right'];
		}

		return $string;
	}

	public function convert($number, $from, $to) {
		$value_from = $this->getValue($from);

		if (!$value_from)
			$value_from = 1;

		$value_to = $this->getValue($to);

		if (!$value_to)
			$value_to = 1;

		return (float)$number * ($value_to / $value_from);
	}

	public function getValue($currency) {
		if (is_numeric($currency)) {
			return isset($this->by_ids[$currency]) ? $this->by_ids[$currency]['value'] : null;
		} else {
			return isset($this->by_codes[$currency]) ? $this->by_codes[$currency]['value'] : null;
		}
	}

	public function get($currency) {
		if (is_numeric($currency)) {
			return isset($this->by_ids[$currency]) ? $this->by_ids[$currency] : null;
		} else {
			return isset($this->by_codes[$currency]) ? $this->by_codes[$currency] : null;
		}
	}

	public function has($currency) {
		if (is_numeric($currency)) {
			return isset($this->by_ids[$currency]);
		} else {
			return isset($this->by_codes[$currency]);
		}
	}
}