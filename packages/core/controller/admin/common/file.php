<?php
namespace Controller\Common;

class File extends \Controller {
	public function index() {
		$data = $this->content();

		//$data['content'] = $this->load->view('common/filemanager', $data);

		$this->response->setOutput($this->load->view('common/filemanager_modal', $data));
	}

	public function view() {
		$data = $this->content('common/file/view');

		$data['content'] = $this->load->view('common/filemanager', $data);

		$this->response->setOutput($this->load->controller('common/template', $data));
	}

	public function content($output = 'common/file') {
		$this->load->language('common/file');

		if (!isset($this->request->get['directory']) && isset($this->session->data['file_directory']) && is_dir(DIR_FILE . html_entity_decode($this->session->data['file_directory'], ENT_QUOTES, 'UTF-8') . '/')) {
			$this->request->get['directory'] = $this->session->data['file_directory'];
		} elseif (isset($this->request->get['directory'])) {
			$this->session->data['file_directory'] = $this->request->get['directory'];
		} else {
			unset($this->session->data['file_directory']);
		}

		// Make sure we have the correct directory
		if (isset($this->request->get['directory']) && $this->request->get['directory']) {
			$directory = DIR_FILE . html_entity_decode($this->request->get['directory'], ENT_QUOTES, 'UTF-8') . '/';
		} else {
			$directory = DIR_FILE;
		}

		if (isset($this->request->get['filter_name'])) {
			$filter_name = basename(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		} else {
			$filter_name = '';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['directories'] = array();

		// Get directories
		$directories = glob($directory . '*', GLOB_ONLYDIR);

		if ($directories) {
			// Split the array based on current page number and max number of items per page of 10
			$files = array_slice($directories, ($page - 1) * 16, 16);

			foreach ($files as $file) {
				if (substr(str_replace('\\', '/', realpath($file)), 0, utf8_strlen(DIR_FILE)) == DIR_FILE) {
					$name = basename($file);

					$url = '';

					if (isset($this->request->get['target'])) {
						$url .= '&target=' . urlencode($this->request->get['target']);
					}

					if (isset($this->request->get['thumb'])) {
						$url .= '&thumb=' . urlencode($this->request->get['thumb']);
					}

					if (isset($this->request->get['ckeditor'])) {
						$url .= '&ckeditor=' . urlencode($this->request->get['ckeditor']);
					}

					$data['directories'][] = array(
						'name' => $name,
						'path' => utf8_substr($file, utf8_strlen(DIR_FILE)),
						'type' => 'directory',
						'href' => $this->url->link($output, 'directory=' . urlencode(utf8_substr($file, utf8_strlen(DIR_FILE))) . $url)
					);
				}
			}
		}

		$this->load->model('tool/file');
		$this->load->model('tool/image');

		$data['files'] = array();

		if (!preg_match('/\.(.+)$/', $filter_name)) {
			if (substr($filter_name, -1) != '.') {
				$filter_name = $filter_name . '*.';
			}

			$filter_name = $filter_name . '*';
		}

		$files = glob($directory . $filter_name, GLOB_BRACE);

		if ($files) {
			// Split the array based on current page number and max number of items per page of 10
			$thumb_width = $this->config->get('admin_image_thumb_width') ? $this->config->get('admin_image_thumb_width') : 136;
			$thumb_height = $this->config->get('admin_image_thumb_height') ? $this->config->get('admin_image_thumb_height') : 136;

			$images = array_slice($files, max(0, ($page - 1) * 16 - count($directories)), 16 - count($data['directories']));

			foreach ($images as $file) {
				if (substr(str_replace('\\', '/', realpath($file)), 0, utf8_strlen(DIR_FILE)) == DIR_FILE) {
					if (@getimagesize($file)) {
						$thumb = $this->model_tool_image->resize(utf8_substr($file, utf8_strlen(DIR_FILE)), $thumb_width, $thumb_height);
					} else {
						$thumb = $this->model_tool_image->resize('file.png', $thumb_width, $thumb_height);
					}

					$name = basename($file);

					$data['files'][] = array(
						'thumb' => $thumb,
						'name' => $name,
						'size' => '',
						'path' => utf8_substr($file, utf8_strlen(DIR_FILE)),
						'href' => $this->model_tool_file->link(utf8_substr($file, utf8_strlen(DIR_IMAGE)))
					);
				}
			}
		}

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->get['directory'])) {
			$data['directory'] = urldecode($this->request->get['directory']);
		} else {
			$data['directory'] = '';
		}

		if (isset($this->request->get['filter_name'])) {
			$data['filter_name'] = $this->request->get['filter_name'];
		} else {
			$data['filter_name'] = '';
		}

		// Return the target ID for the file manager to set the value
		if (isset($this->request->get['target'])) {
			$data['target'] = $this->request->get['target'];
		} else {
			$data['target'] = '';
		}

		// Return the thumbnail for the file manager to show a thumbnail
		if (isset($this->request->get['thumb'])) {
			$data['thumb'] = $this->request->get['thumb'];
		} else {
			$data['thumb'] = '';
		}

		if (isset($this->request->get['ckeditor'])) {
			$data['ckeditor'] = $this->request->get['ckeditor'];
		} else {
			$data['ckeditor'] = '';
		}

		// Parent
		$url = '';

		if (isset($this->request->get['directory'])) {
			$pos = strrpos($this->request->get['directory'], '/');

			$url .= '&directory=' . urlencode(substr($this->request->get['directory'], 0, $pos));
		}

		if (isset($this->request->get['target'])) {
			$url .= '&target=' . urlencode($this->request->get['target']);
		}

		if (isset($this->request->get['thumb'])) {
			$url .= '&thumb=' . urlencode($this->request->get['thumb']);
		}

		if (isset($this->request->get['ckeditor'])) {
			$url .= '&ckeditor=' . urlencode($this->request->get['ckeditor']);
		}

		$data['parent'] = $this->url->link($output, $url);

		// Refresh
		$url = '';

		if (isset($this->request->get['directory'])) {
			$url .= '&directory=' . urlencode(html_entity_decode($this->request->get['directory'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['target'])) {
			$url .= '&target=' . urlencode($this->request->get['target']);
		}

		if (isset($this->request->get['thumb'])) {
			$url .= '&thumb=' . urlencode($this->request->get['thumb']);
		}

		if (isset($this->request->get['ckeditor'])) {
			$url .= '&ckeditor=' . urlencode($this->request->get['ckeditor']);
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['file_refresh'] = $this->url->link($output, $url);

		$url = '';

		if (isset($this->request->get['directory'])) {
			$url .= '&directory=' . urlencode(html_entity_decode($this->request->get['directory'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['target'])) {
			$url .= '&target=' . urlencode($this->request->get['target']);
		}

		if (isset($this->request->get['thumb'])) {
			$url .= '&thumb=' . urlencode($this->request->get['thumb']);
		}

		if (isset($this->request->get['ckeditor'])) {
			$url .= '&ckeditor=' . urlencode($this->request->get['ckeditor']);
		}

		// Get total number of files and directories
		$data['pagination'] = $this->load->controller('block/pagination', array(
			'total' => count((array)$directories) + count((array)$files),
			'page'  => $page,
			'limit' => 16,
			'url'   => $this->url->link($output, $url . '&page={page}')
		));

		$data['search'] = str_replace('&amp;', '&', $this->url->link($output));
		$data['file_upload'] = str_replace('&amp;', '&',$this->url->link('common/file/upload'));
		$data['folder'] = str_replace('&amp;', '&',$this->url->link('common/file/folder'));
		$data['file_delete'] = str_replace('&amp;', '&',$this->url->link('common/file/delete'));

		return $data;
	}

	public function upload() {
		$this->load->language('common/file');

		$json = array();

		// Check user has permission
		if (!$this->user->hasPermission('modify', 'common/file')) {
			$json['error'] = $this->language->get('error_permission');
		}

		// Make sure we have the correct directory
		if (isset($this->request->get['directory'])) {
			$directory = DIR_FILE . html_entity_decode($this->request->get['directory'], ENT_QUOTES, 'UTF-8') . '/';
		} else {
			$directory = DIR_FILE;
		}

		// Check its a directory
		if (!is_dir($directory)) {
			$json['error'] = $this->language->get('error_directory');
		}

		if (!$json) {
			// Check if multiple files are uploaded or just one
			$files = array();

			if (!empty($this->request->files['file']['name']) && is_array($this->request->files['file']['name'])) {
				foreach (array_keys($this->request->files['file']['name']) as $key) {
					$files[] = array(
						'name'     => $this->request->files['file']['name'][$key],
						'type'     => $this->request->files['file']['type'][$key],
						'tmp_name' => $this->request->files['file']['tmp_name'][$key],
						'error'    => $this->request->files['file']['error'][$key],
						'size'     => $this->request->files['file']['size'][$key]
					);
				}
			}

			foreach ($files as $file) {
				if (is_file($file['tmp_name'])) {
					// Sanitize the filename
					$filename = preg_replace('/[\/\\?%*:|"\'\(\)<>]/', '', basename(html_entity_decode($file['name'], ENT_QUOTES, 'UTF-8')));

					// Validate the filename length
					if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 255)) {
						$json['error'] = $this->language->get('error_filename');
					}

					// Allowed file extension types
					$allowed = array();

					$extension_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('system_file_ext_allowed'));

					$filetypes = explode("\n", $extension_allowed);

					foreach ($filetypes as $filetype) {
						$allowed[] = trim($filetype);
					}

					if (!in_array(utf8_strtolower(utf8_substr(strrchr($filename, '.'), 1)), $allowed)) {
						$json['error'] = $this->language->get('error_filetype');
					}

					// Allowed file mime types
					$allowed = array();

					$mime_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('system_file_mime_allowed'));

					$filetypes = explode("\n", $mime_allowed);

					foreach ($filetypes as $filetype) {
						$allowed[] = trim($filetype);
					}

					if (!in_array($file['type'], $allowed)) {
						$json['error'] = $this->language->get('error_filetype');
					}

					// Return any upload error
					if ($file['error'] != UPLOAD_ERR_OK) {
						$json['error'] = $this->language->get('error_upload_' . $file['error']);
					}
				} else {
					$json['error'] = $this->language->get('error_upload');
				}

				if (!$json) {
					move_uploaded_file($file['tmp_name'], $directory . $filename);
				}
			}
		}

		if (!$json) {
			$json['success'] = $this->language->get('text_uploaded');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}

	public function folder() {
		$this->load->language('common/file');

		$json = array();

		// Check user has permission
		if (!$this->user->hasPermission('modify', 'common/file')) {
			$json['error'] = $this->language->get('error_permission');
		}

		// Make sure we have the correct directory
		if (isset($this->request->get['directory'])) {
			$directory = DIR_FILE . html_entity_decode($this->request->get['directory'], ENT_QUOTES, 'UTF-8') . '/';
		} else {
			$directory = DIR_FILE;
		}

		// Check its a directory
		if (!is_dir($directory)) {
			$json['error'] = $this->language->get('error_directory');
		}

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			// Sanitize the folder name
			$folder = basename(html_entity_decode($this->request->post['folder'], ENT_QUOTES, 'UTF-8'));

			// Validate the filename length
			if ((utf8_strlen($folder) < 3) || (utf8_strlen($folder) > 128)) {
				$json['error'] = $this->language->get('error_folder');
			}

			// Check if directory already exists or not
			if (is_dir($directory . '/' . $folder)) {
				$json['error'] = $this->language->get('error_exists');
			}
		}

		if (!$json) {
			mkdir($directory . '/' . $folder, 0777);
			chmod($directory . '/' . $folder, 0777);

			$json['success'] = $this->language->get('text_directory');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}

	public function delete() {
		$this->load->language('common/file');

		$json = array();

		// Check user has permission
		if (!$this->user->hasPermission('modify', 'common/file')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (isset($this->request->post['path']) && is_array($this->request->post['path'])) {
			$paths = $this->request->post['path'];
		} else {
			$paths = array();
		}

		// Loop through each path to run validations
		foreach ($paths as $path) {
			// Check path exsists
			if ($path == DIR_FILE || substr(str_replace('\\', '/', realpath(DIR_FILE . $path) . '/'), 0, strlen(DIR_FILE)) != DIR_FILE) {
				$json['error'] = $this->language->get('error_delete');

				break;
			}
		}

		if (!$json) {
			// Loop through each path
			foreach ($paths as $path) {
				$path = rtrim(DIR_FILE . $path, '/');

				// If path is just a file delete it
				if (is_file($path)) {
					unlink($path);

				// If path is a directory beging deleting each file and sub folder
				} elseif (is_dir($path)) {
					$files = array();

					// Make path into an array
					$path = array($path);

					// While the path array is still populated keep looping through
					while (count($path) != 0) {
						$next = array_shift($path);

						foreach (glob($next) as $file) {
							// If directory add to path array
							if (is_dir($file)) {
								$path[] = $file . '/*';
							}

							// Add the file to the files to be deleted array
							$files[] = $file;
						}
					}

					// Reverse sort the file array
					rsort($files);

					foreach ($files as $file) {
						// If file just delete
						if (is_file($file)) {
							unlink($file);

						// If directory use the remove directory function
						} elseif (is_dir($file)) {
							rmdir($file);
						}
					}
				}
			}

			$json['success'] = $this->language->get('text_delete');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}

	public function get_link() {
		if ($this->request->get['file']) {
			$this->load->model('tool/file');

			$file = $this->model_tool_file->link($this->request->get['file']);
		} else {
			$file = '';
		}

		$this->response->addHeader('Content-Type: text/plain');
		$this->response->setOutput($file);
	}
}