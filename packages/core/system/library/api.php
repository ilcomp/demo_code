<?php
use GuzzleHttp\Client;
use GuzzleHttp\Event\EndEvent;

/**
 * @package		OpenCart
 * @author		Daniel Kerr
 * @copyright	Copyright (c) 2005 - 2017, OpenCart, Ltd. (https://www.opencart.com/)
 * @license		https://opensource.org/licenses/GPL-3.0
 * @link		https://www.opencart.com
*/

/**
* API class
*/
class API {
	private $client;
	private $last_status;

	/**
	 * Constructor
	 *
	 * @param	string	$link
	 * @param	string	$token
	 *
 	*/
	public function __construct($link, $login, $password) {
		if (!defined('CURL_HTTP_VERSION_2_0')) {
    define('CURL_HTTP_VERSION_2_0', 3);
}
		$client = new Client();

		$response = $client->get(rtrim($link, '/') . '/common/login', [
			'version' => 2.0,
			'auth' =>  [$login, $password],
			'allow_redirects' => false,
			'exceptions' => false
		]);

echo $response->getProtocolVersion();
		$this->last_status = $response->getStatusCode();

		$data = $response->json();

		if (isset($data['token'])) {
			$this->client = new Client([
				'base_url' => rtrim($link, '/') . '/',
				'version' => 2.0,
				'defaults' => [
					'headers'  => [
						'Accept' => 'application/json',
						'Content-type' => 'application/json',
						'Authorization' => 'bearer ' . $data['token'],
					],
					'connect_timeout' => 10,
					'allow_redirects' => false,
					'exceptions' => false
				],
			]);
		} else {
			$this->client = new Client([
				'base_url' => rtrim($link, '/') . '/',
				'version' => 2.0,
				'defaults' => [
					'headers'  => [
						'Accept' => 'application/json',
						'Content-type' => 'application/json',
						//'Authorization' => 'bearer ' . $data['token'],
					],
					'connect_timeout' => 10,
					'allow_redirects' => false,
					'exceptions' => false
				],
			]);
			// throw new \Exception($data['error']);
		}
	}

	/**
	 * 
	 *
	 * @param	string	$sql
	 * @param	array	$args
	 * 
	 * @return	array
	 */
	public function get($route, $args = array()) {
		$response = $this->client->get($route, $args);

		$this->last_status = $response->getStatusCode();

		return $response->json();
	}

	/**
	 * 
	 *
	 * @param	string	$sql
	 * @param	array	$args
	 * 
	 * @return	array
	 */
	public function post($route, $args = array()) {
		$response = $this->client->post($route, $args);

		$this->last_status = $response->getStatusCode();

		return $response->json();
	}

	/**
	 * 
	 *
	 * @param	string	$sql
	 * @param	array	$args
	 * 
	 * @return	array
	 */
	public function put($route, $args = array()) {
		$response = $this->client->put($route, $args);

		$this->last_status = $response->getStatusCode();

		return $response->json();
	}

	/**
	 * 
	 *
	 * @param	string	$sql
	 * @param	array	$args
	 * 
	 * @return	array
	 */
	public function delete($route, $args = array()) {
		$response = $this->client->delete($route, $args);

		$this->last_status = $response->getStatusCode();

		return $response->json();
	}

	/**
	 * 
	 *
	 * @return	string
	 */
	public function getStatus() {
		return $this->last_status;
	}
}