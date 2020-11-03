<?php
/**
 * @package		OpenCart
 * @author		Daniel Kerr
 * @copyright	Copyright (c) 2005 - 2017, OpenCart, Ltd. (https://www.opencart.com/)
 * @license		https://opensource.org/licenses/GPL-3.0
 * @link		https://www.opencart.com
*/

/**
* Document class
*/
class Document {
	private $title;
	private $description;
	private $metas = array();
	private $links = array();
	private $styles = array();
	private $scripts = array();

	/**
	 *
	 *
	 * @param	string	$title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 *
	 *
	 * @return	string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 *
	 *
	 * @param	string	$description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}

	/**
	 *
	 *
	 * @param	string	$description
	 *
	 * @return	string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 *
	 *
	 * @param	string	$name
	 * @param	string	$content
	 * @param	boolean	$property
	 */
	public function addMeta($name, $content, $property = 0) {
		$this->metas[$name] = array(
			'name' => !$property ? $name : '',
			'property' => $property ? $name : '',
			'content'  => $content
		);
	}

	/**
	 *
	 *
	 * @return	array
	 */
	public function getMetas() {
		return $this->metas;
	}

	/**
	 *
	 *
	 * @param	string	$link
	 * @param	string	$rel
	 */
	public function addLink($link, $rel) {
		$this->links[$link] = array(
			'link' => $link,
			'rel'  => $rel
		);
	}

	/**
	 *
	 *
	 * @return	array
	 */
	public function getLinks() {
		return $this->links;
	}

	/**
	 *
	 *
	 * @param	string	$link
	 * @param	string	$rel
	 * @param	string	$media
	 */
	public function addStyle($link, $sort_order = 0, $rel = 'stylesheet', $media = 'screen') {
		$this->styles[$link] = array(
			'link'  => $link,
			'rel'   => $rel,
			'media' => $media,
			'sort_order' => $sort_order
		);
	}

	/**
	 *
	 *
	 * @return	array
	 */
	public function getStyles() {
		$sort_order = array_column($this->styles, 'sort_order');

		array_multisort($sort_order, $this->styles);

		return $this->styles;
	}

	/**
	 *
	 *
	 * @param	string	$link
	 * @param	string	$postion
	 */
	public function addScript($link, $sort_order = 0, $type_load = 'defer') {
		if (!in_array($type_load, array('defer', 'async')))
			$type_load = '';

		$this->scripts[$link] = array(
			'link'	   => $link,
			'type_load'  => $type_load,
			'sort_order' => $sort_order
		);
	}

	/**
	 *
	 *
	 * @param	string	$postion
	 *
	 * @return	array
	 */
	public function getScripts() {
		$sort_order = array_column($this->scripts, 'sort_order');

		array_multisort($sort_order, $this->scripts);

		return $this->scripts;
	}
}