<?php
/**
 * @package		OpenCart
 * @author		Daniel Kerr
 * @copyright	Copyright (c) 2005 - 2017, OpenCart, Ltd. (https://www.opencart.com/)
 * @license		https://opensource.org/licenses/GPL-3.0
 * @link		https://www.opencart.com
 */

/**
 * Loader class
 */
final class Loader {
	protected $registry;

	/**
	 * Constructor
	 *
	 * @param    object $registry
	 */
	public function __construct($registry) {
		$this->registry = $registry;
	}

	/**
	 *
	 *
	 * @param    string $route
	 * @param    array $data
	 *
	 * @return    mixed
	 */
	public function controller($route) {
		$args = func_get_args();

		array_shift($args);

		// Sanitize the call
		$route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route);

		// Keep the original trigger
		$trigger = $route;

		// Trigger the pre events
		$result = $this->registry->get('event')->trigger('controller/' . $trigger . '/before', array(&$route, &$args));

		// Make sure its only the last event that returns an output if required.
		if ($result != null && !$result instanceof Exception) {
			$output = $result;
		} else {
			$action = new Action($route);
			$output = $action->execute($this->registry, $args);
		}

		// Trigger the post events
		$result = $this->registry->get('event')->trigger('controller/' . $trigger . '/after', array(&$route, &$args, &$output));

		if ($result && !$result instanceof Exception) {
			$output = $result;
		}

		if (!$output instanceof Exception) {
			return $output;
		}
	}

	/**
	 *
	 *
	 * @param    string $route
	 */
	public function model($route) {
		// Sanitize the call
		$route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route);

		if (!$this->registry->has('model_' . str_replace('/', '_', $route))) {
			$file = DIR_MODEL . $route . '.php';
			$class = '\\Model\\' . preg_replace(array('/[^a-zA-Z0-9\/]/', '/\//'), array('', '\\'), $route);

			if (is_file($file)) {
				include_once($file);

				$proxy = new Proxy();

				// Overriding models is a little harder so we have to use PHP's magic methods
				foreach (get_class_methods($class) as $method) {
					$proxy->{$method} = $this->callback($route . '/' . $method);
				}

				$this->registry->set('model_' . str_replace('/', '_', (string)$route), $proxy);
			} else {
				throw new \Exception('Error: Could not load model ' . $route . '!');
			}
		}
	}

	/**
	 *
	 *
	 * @param    string $route
	 * @param    array $data
	 *
	 * @return   string
	 */
	public function view($route, $data = array()) {
		// Sanitize the call
		$route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route);

		// Keep the original trigger
		$trigger = $route;

		$template = new Template($this->registry->get('config')->get('template_engine'));

		// Trigger the pre events
		$result = $this->registry->get('event')->trigger('view/' . $trigger . '/before', array(&$route, &$data, &$template));

		// Make sure its only the last event that returns an output if required.
		if ($result && !$result instanceof Exception) {
			$output = $result;
		} else {
			foreach ($data as $key => $value) {
				$template->set($key, $value);
			}

			$output = $template->render($this->registry->get('config')->get('template_directory') . $route, $this->registry->get('config')->get('template_cache'));
		}

		// Trigger the post events
		$result = $this->registry->get('event')->trigger('view/' . $trigger . '/after', array(&$route, &$data, &$output));

		if ($result && !$result instanceof Exception) {
			$output = $result;
		}

		return $output;
	}

	/**
	 *
	 *
	 * @param    string $route
	 */
	public function library($route) {
		// Sanitize the call
		$route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route);

		$file = DIR_SYSTEM . 'library/' . $route . '.php';
		$class = str_replace('/', '\\', $route);

		if (is_file($file)) {
			include_once($file);

			$this->registry->set(basename($route), new $class($this->registry));
		} else {
			throw new \Exception('Error: Could not load library ' . $route . '!');
		}
	}

	/**
	 *
	 *
	 * @param    string $route
	 */
	public function helper($route) {
		$file = DIR_SYSTEM . 'helper/' . preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route) . '.php';

		if (is_file($file)) {
			include_once($file);
		} else {
			throw new \Exception('Error: Could not load helper ' . $route . '!');
		}
	}

	/**
	 *
	 *
	 * @param    string $route
	 */
	public function config($route) {
		$this->registry->get('event')->trigger('config/' . $route . '/before', array(&$route));

		$this->registry->get('config')->load($route);

		$this->registry->get('event')->trigger('config/' . $route . '/after', array(&$route));
	}

	/**
	 *
	 *
	 * @param    string $route
	 * @param    string $key
	 *
	 * @return    array
	 */
	public function language($route, $key = '') {
		// Sanitize the call
		$route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route);

		// Keep the original trigger
		$trigger = $route;

		$result = $this->registry->get('event')->trigger('language/' . $trigger . '/before', array(&$route, &$key));

		if ($result && !$result instanceof Exception) {
			$output = $result;
		} else {
			$output = $this->registry->get('language')->load($route, $key);
		}

		$result = $this->registry->get('event')->trigger('language/' . $trigger . '/after', array(&$route, &$key, &$output));

		if ($result && !$result instanceof Exception) {
			$output = $result;
		}

		return $output;
	}

	protected function callback($route) {
		return function () use ($route) {
			// Grab args using function because we don't know the number of args being passed.
			$args = func_get_args();

			$route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route);

			// Keep the original trigger
			$trigger = $route;

			// Trigger the pre events
			$result = $this->registry->get('event')->trigger('model/' . $trigger . '/before', array(&$route, &$args));

			if ($result && !$result instanceof Exception) {
				$output = $result;
			} else {
				$class = '\\Model\\' . preg_replace(array('/[^a-zA-Z0-9\/]/', '/\//'), array('', '\\'), substr($route, 0, strrpos($route, '/')));

				// Store the model object
				$key = substr($route, 0, strrpos($route, '/'));

				// Check if the model has already been initialised or not
				if (!$this->registry->has($key)) {
					$this->registry->set($key, new $class($this->registry));
				}

				$method = substr($route, strrpos($route, '/') + 1);

				$callable = array($this->registry->get($key), $method);

				if (is_callable($callable)) {
					$output = call_user_func_array($callable, $args);
				} else {
					throw new \Exception('Error: Could not call model/' . $route . '!');
				}
			}

			// Trigger the post events
			$result = $this->registry->get('event')->trigger('model/' . $trigger . '/after', array(&$route, &$args, &$output));

			if ($result && !$result instanceof Exception) {
				$output = $result;
			}

			return $output;
		};
	}
}