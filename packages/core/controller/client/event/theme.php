<?php
namespace Controller\Event;

class Theme extends \Controller {
	public function index($route, &$args, $template) {
		// if (!is_array($this->config->get('forms'))) {
		// 	$this->load->model('design/form');

		// 	$forms = $this->model_design_form->getForms();

		// 	foreach ($forms as &$form) {
		// 		$form['fields'] = $this->model_design_form->getFormFields($form['form_id']);
		// 	}
		// 	unset($form);

		// 	$this->config->set('forms', $forms);
		// }

		// $args['forms'] = $this->config->get('forms');

		if (is_file(DIR_TEMPLATE . $this->config->get('config_theme') . '/template/' . $route . '.twig')) {
			$this->config->set('template_directory', $this->config->get('config_theme') . '/template/');
		} elseif (is_file(DIR_TEMPLATE . $this->config->get('theme_default') . '/template/' . $route . '.twig')) {
			$this->config->set('template_directory', $this->config->get('theme_default') . '/template/');
		}
	}

	public function theme($route, $args) {
		if (!$this->config->get('developer_theme')) {
			$iterators = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(DIR_TEMPLATE . $this->config->get('config_theme'), \FilesystemIterator::SKIP_DOTS));

			foreach($iterators as $iterator) {
				$pathname = str_replace(DIR_TEMPLATE . $this->config->get('config_theme'), '', $iterator->getPathname());

				if (preg_match('/^\/(?!template|scss)/', $pathname) && filemtime(DIR_TEMPLATE . $this->config->get('config_theme') . $pathname) > @filemtime(DIR_THEME . $this->config->get('config_theme') . $pathname)) {
					$directory = str_replace(DIR_TEMPLATE, DIR_THEME, $iterator->getPath());

					if(!is_dir($directory))
						mkdir($directory, 0755, true);

					copy(DIR_TEMPLATE . $this->config->get('config_theme') . $pathname, DIR_THEME . $this->config->get('config_theme') . $pathname);
				}
			}
		}
	}

	public function scss($route, $args) {
		$dir_css = DIR_THEME . $this->config->get('config_theme') . '/css/';
		$dir_scss = DIR_TEMPLATE . $this->config->get('config_theme') . '/scss/';

		if (!is_dir($dir_css))
			mkdir($dir_css, 0755, 1);

		if (!is_dir($dir_scss))
			mkdir($dir_scss, 0755, 1);

		$filemtime_scss = 0;
		$files = array();

		foreach (glob($dir_scss . "*.scss") as $file) {
			$filemtime = filemtime($file);
			if (!$filemtime_scss || $filemtime_scss < $filemtime)
				$filemtime_scss = $filemtime;

			if (preg_match('/^(?!_).+\.scss$/', basename($file)))
				$files[] = basename($file);
		}

		if (!empty($files)) {
			if ($this->config->get('developer_scss')) {
				$cache = false;

				foreach ($files as $key => $file) {
					if (is_file($dir_css . str_replace('.scss','.css',$file))) unset($files[$key]);
				}
			} else {
				$scss_files = $files;

				$cache = true;

				foreach (glob($dir_css . "*.css") as $file) {
					$file_key = array_search(str_replace('.css','.scss',basename($file)), $scss_files);
					if ($file_key !== false) {
						$filemtime = filemtime($file);

						if ($filemtime_scss > $filemtime){
							$cache = false;
							break;
						}

						array_splice($scss_files, $file_key, 1);
					}
				}

				if (!empty($scss_files))
					$cache = false;
			}

			if (!$cache && !empty($files)) {
				$scss = new \Leafo\ScssPhp\Compiler();

				$scss->setImportPaths(array($dir_scss, DIR_ASSETS));
				$scss->setFormatter('Leafo\ScssPhp\Formatter\Compressed'); //Expanded / Compact / Compressed / Crunched
				//$scss->setSourceMap(Leafo\ScssPhp\Compiler::SOURCE_MAP_FILE); // SOURCE_MAP_NONE, SOURCE_MAP_INLINE, or SOURCE_MAP_FILE

				try {
					foreach ($files as $key => $file) {
						// $scss->setSourceMapOptions(array(
						// 	'sourceRoot'        => '/',
						// 	'sourceMapFilename' => str_replace('.scss','.css', $file),
						// 	'sourceMapURL'      => str_replace('.scss','.map', $file),
						// 	'sourceMapWriteTo'  => $dir_css . str_replace('.scss','.map', $file),
						// 	'outputSourceFiles' => false,
						// 	'sourceMapRootpath' => '',
						// 	'sourceMapBasepath' => $_SERVER['DOCUMENT_ROOT'],
						// ));

						$output = $scss->compile('@import "' . $file . '"');
						// if (preg_match_all('/@media\s?\(min-width:\s?768px\)[^{]*\{[\s\S]+?}\s*}/', $output, $matchs) && strlen(implode('',$matchs[0])) > 2000) {
						// 	file_put_contents($dir_css . str_replace('.scss','-768px.css',$file), implode('',$matchs[0]));

						// 	$output = str_replace($matchs[0], '', $output);
						// }

						// if (preg_match_all('/@media\s?\(min-width:\s?992px\)[^{]*\{[\s\S]+?}\s*}/', $output, $matchs) && strlen(implode('',$matchs[0])) > 2000) {
						// 	file_put_contents($dir_css . str_replace('.scss','-992px.css',$file), implode('',$matchs[0]));

						// 	$output = str_replace($matchs[0], '', $output);
						// }

						// if (preg_match_all('/@media\s?\(min-width:\s?1200px\)[^{]*\{[\s\S]+?}\s*}/', $output, $matchs) && strlen(implode('',$matchs[0])) > 2000) {
						// 	file_put_contents($dir_css . str_replace('.scss','-1200px.css',$file), implode('',$matchs[0]));

						// 	$output = str_replace($matchs[0], '', $output);
						// }

						file_put_contents($dir_css . str_replace('.scss','.css',$file), $output);
					}
				} catch (\Exception $e) {
					echo $e->getMessage();
				}
			}
		}
	}
}