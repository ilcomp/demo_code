<?php
namespace Template;
final class Twig {
	private $data = array();

	public function set($key, $value) {
		$this->data[$key] = $value;
	}

	public function render($template, $cache = true) {
		$config = array(
			'autoescape' => false,
			'debug'      => true,
			'cache'      => DIR_CACHE . 'template/'
		);

		if (!$cache) {
			$config['auto_reload'] = true;
		}

		$loader = new \Twig\Loader\FilesystemLoader(DIR_TEMPLATE);

		$twig = new \Twig\Environment($loader, $config);

		try {
			$template = $twig->loadTemplate($template . '.twig');

			return $template->render($this->data);
		} catch (\Twig\Error\Error $e) {
			trigger_error('Error: Could not load template ' . $template . ': ' . $e->getRawMessage());
		}
	}
}