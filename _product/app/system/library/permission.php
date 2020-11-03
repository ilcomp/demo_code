<?php
/**
 * @package		OpenCart
 * @author		Daniel Kerr
 * @copyright	Copyright (c) 2005 - 2017, OpenCart, Ltd. (https://www.opencart.com/)
 * @license		https://opensource.org/licenses/GPL-3.0
 * @link		https://www.opencart.com
*/

/**
* Permission class
*/
class Permission {
	private $data = array();

    /**
     * 
     *
     * @param	string	$key
	 * @param	string	$value
     */
	public function add($key, $value) {
		if (!isset($this->data[$key]))
			$this->data[$key] = array();

		if (!in_array($value, $this->data[$key]))
			$this->data[$key][] = $value;
	}

    /**
     * 
     *
     * @param	string	$key
     * @param	string	$value
	 *
	 * @return	mixed
     */
	public function has($key, $value) {
		return !empty($this->data[$key]) && in_array($value, $this->data[$key]);
	}
}