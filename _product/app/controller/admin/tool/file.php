<?php
namespace Controller\Tool;

class File extends \Controller {
	public function index() {
		if (empty($this->request->get['file']))
			return new \Action('error/not_found');

		$this->load->model('tool/file');

		$file = $this->model_tool_file->link($this->request->get['file'], 'file/');

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