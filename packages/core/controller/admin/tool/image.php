<?php
namespace Controller\Tool;

class Image extends \Controller {
	public function index() {
		if (empty($this->request->get['image']))
			return new \Action('error/not_found');

		if (isset($this->request->get['type']) && in_array($this->request->get['type'], array('link', 'resize', 'crop')))
			$type = $this->request->get['type'];
		else
			$type = 'resize';

		if (isset($this->request->get['height']))
			$height = (int)$this->request->get['height'];
		else
			$height = 0;

		if (isset($this->request->get['width']))
			$width = (int)$this->request->get['width'];
		else
			$width = 0;

		$this->load->model('tool/image');

		switch ($type) {
			case 'link':
				$file = $this->model_tool_image->link($this->request->get['image'], 'image/');
				break;
			case 'resize':
				$file = $this->model_tool_image->resize($this->request->get['image'], $height, $width, 'image/');
				break;
			case 'crop':
				$file = $this->model_tool_image->crop($this->request->get['image'], $height, $width, 'image/');
				break;
		}

		$file = DIR_CACHE . $file;

		if (!headers_sent()) {
			if (file_exists($file)) {
				header('Content-Type: ' . mime_content_type($file));
				header('Content-Disposition: inline; filename="' . basename($file) . '"');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
				header('Content-Length: ' . filesize($file));

				if (ob_get_level()) {
					ob_end_clean();
				}

				readfile($file, 'rb');

				exit();
			} else {
				header($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

				exit('Error: Could not find file ' . $file . '!');
			}
		} else {
			header($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			exit('Error: Headers already sent out!');
		}
	}
}