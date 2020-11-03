<?php
namespace Controller\Tool;

class Upload extends \Controller {
	public function index() {
		$this->load->language('tool/upload');

		$json = array();

		if (!empty($this->request->files['file']['name']) && is_file($this->request->files['file']['tmp_name'])) {
			// Sanitize the filename
			$filename = basename(preg_replace('/[^a-zA-Z0-9\.\-\s+]/', '', html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8')));

			// Validate the filename length
			if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 64)) {
				$json['error'] = $this->language->get('error_filename');
			}

			// Allowed file extension types
			$allowed = array();

			$extension_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('system_file_ext_allowed'));

			$filetypes = explode("\n", $extension_allowed);

			foreach ($filetypes as $filetype) {
				$allowed[] = trim($filetype);
			}

			if (!in_array(strtolower(substr(strrchr($filename, '.'), 1)), $allowed)) {
				$json['error'] = $this->language->get('error_filetype');
			}

			// Allowed file mime types
			$allowed = array();

			$mime_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('system_file_mime_allowed'));

			$filetypes = explode("\n", $mime_allowed);

			foreach ($filetypes as $filetype) {
				$allowed[] = trim($filetype);
			}

			if (!in_array($this->request->files['file']['type'], $allowed)) {
				$json['error'] = $this->language->get('error_filetype');
			}

			// Return any upload error
			if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
				$json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
			}
		} else {
			$json['error'] = $this->language->get('error_upload');
		}

		if (!$json) {
			$file = $filename . '.' . token(32);

			move_uploaded_file($this->request->files['file']['tmp_name'], DIR_UPLOAD . $file);

			// Hide the uploaded file name so people can not link to it directly.
			$this->load->model('tool/upload');

			$json['code'] = $this->model_tool_upload->addUpload($filename, $file);

			$json['success'] = $this->language->get('text_upload');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}

	public function image($file, $prefix = '', $dir = '') {
		$result = array();

		$dir = ltrim(trim($dir, '/') . '/', '/');

		if (!empty($file['name']) && !is_array($file['name'])) {
			$directory = DIR_IMAGE . $dir;

			// Check its a directory
			if (!is_dir($directory)) {
				mkdir($directory, 0777, true);
				chmod($directory, 0777);
			}

			$file = array(
				'name'     => $file['name'],
				'type'     => $file['type'],
				'tmp_name' => $file['tmp_name'],
				'error'    => $file['error'],
				'size'     => $file['size']
			);

			if (is_file($file['tmp_name'])) {
				// Sanitize the filename
				$filename = basename(html_entity_decode($file['name'], ENT_QUOTES, 'UTF-8'));

				// Validate the filename length
				if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 255)) {
					$result['error'] = $this->language->get('error_filename');
				}

				// Allowed file extension types
				$allowed = array(
					'jpg',
					'jpeg',
					'png'
				);

				$file_type = utf8_strtolower(utf8_substr(strrchr($filename, '.'), 1));

				if (!in_array($file_type, $allowed)) {
					$result['error'] = $this->language->get('error_filetype');
				}

				// Allowed file mime types
				$allowed = array(
					'image/jpeg',
					'image/pjpeg',
					'image/png',
					'image/x-png'
				);

				if (!in_array($file['type'], $allowed)) {
					$result['error'] = $this->language->get('error_filetype');
				}

				// Return any upload error
				if ($file['error'] != UPLOAD_ERR_OK) {
					$result['error'] = $this->language->get('error_upload_' . $file['error']);
				}
			} else {
				$result['error'] = $this->language->get('error_upload');
			}

			if (empty($result['error'])) {
				$filename = $prefix . time() . '_' .  token(4) . '.' . $file_type;

				move_uploaded_file($file['tmp_name'], $directory . $filename);

				$result['result'] = $dir . $filename;
			}
		}

		return $result;
	}

	public function file($file, $prefix = '', $dir = '') {
		$result = array();

		$dir = ltrim(trim($dir, '/') . '/', '/');

		if (!empty($file['name']) && !is_array($file['name'])) {
			$directory = DIR_FILE . $dir;

			// Check its a directory
			if (!is_dir($directory)) {
				mkdir($directory, 0777, true);
				chmod($directory, 0777);
			}

			$file = array(
				'name'     => $file['name'],
				'type'     => $file['type'],
				'tmp_name' => $file['tmp_name'],
				'error'    => $file['error'],
				'size'     => $file['size']
			);

			if (is_file($file['tmp_name'])) {
				// Sanitize the filename
				$filename = basename(html_entity_decode($file['name'], ENT_QUOTES, 'UTF-8'));

				// Validate the filename length
				if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 255)) {
					$result['error'] = $this->language->get('error_filename');
				}

				// Allowed file extension types
				$allowed = array();

				$extension_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('system_file_ext_allowed'));

				$filetypes = explode("\n", $extension_allowed);

				foreach ($filetypes as $filetype) {
					$allowed[] = trim($filetype);
				}

				$file_type = utf8_strtolower(utf8_substr(strrchr($filename, '.'), 1));

				if (!in_array($file_type, $allowed)) {
					$result['error'] = $this->language->get('error_filetype');
				}

				// Allowed file mime types
				$allowed = array();

				$mime_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('system_file_mime_allowed'));

				$filetypes = explode("\n", $mime_allowed);

				foreach ($filetypes as $filetype) {
					$allowed[] = trim($filetype);
				}

				if (!in_array($file['type'], $allowed)) {
					$this->error['upload'] = $this->language->get('error_filetype');
				}

				// Return any upload error
				if ($file['error'] != UPLOAD_ERR_OK) {
					$result['error'] = $this->language->get('error_upload_' . $file['error']);
				}
			} else {
				$result['error'] = $this->language->get('error_upload');
			}

			if (empty($result['error'])) {
				$filename = $prefix . time() . '_' .  token(4) . '.' . $file_type;

				move_uploaded_file($file['tmp_name'], $directory . $filename);

				$result['result'] = $dir  . $filename;
			}
		}

		return $result;
	}

	public function upload($file, $prefix = '', $dir = '') {
		$result = array();

		$dir = ltrim(trim($dir, '/') . '/', '/');

		if (!empty($file['name']) && !is_array($file['name'])) {
			$directory = DIR_UPLOAD . $dir;

			// Check its a directory
			if (!is_dir($directory)) {
				mkdir($directory, 0777, true);
				chmod($directory, 0777);
			}

			$file = array(
				'name'     => $file['name'],
				'type'     => $file['type'],
				'tmp_name' => $file['tmp_name'],
				'error'    => $file['error'],
				'size'     => $file['size']
			);

			if (is_file($file['tmp_name'])) {
				// Sanitize the filename
				$filename = basename(html_entity_decode($file['name'], ENT_QUOTES, 'UTF-8'));

				// Validate the filename length
				if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 255)) {
					$result['error'] = $this->language->get('error_filename');
				}

				// Allowed file extension types
				$allowed = array();

				$extension_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('system_file_ext_allowed'));

				$filetypes = explode("\n", $extension_allowed);

				foreach ($filetypes as $filetype) {
					$allowed[] = trim($filetype);
				}

				$file_type = utf8_strtolower(utf8_substr(strrchr($filename, '.'), 1));

				if (!in_array($file_type, $allowed)) {
					$result['error'] = $this->language->get('error_filetype');
				}

				// Allowed file mime types
				$allowed = array();

				$mime_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('system_file_mime_allowed'));

				$filetypes = explode("\n", $mime_allowed);

				foreach ($filetypes as $filetype) {
					$allowed[] = trim($filetype);
				}

				if (!in_array($file['type'], $allowed)) {
					$this->error['upload'] = $this->language->get('error_filetype');
				}

				// Return any upload error
				if ($file['error'] != UPLOAD_ERR_OK) {
					$result['error'] = $this->language->get('error_upload_' . $file['error']);
				}
			} else {
				$result['error'] = $this->language->get('error_upload');
			}

			if (empty($result['error'])) {
				$filename = $prefix . time() . '.' . token(16);

				move_uploaded_file($file['tmp_name'], $directory . $filename);

				$this->load->model('tool/upload');

				$result['result'] = $this->model_tool_upload->addUpload($file['name'], $dir . $filename, true);
			}
		}

		return $result;
	}
}