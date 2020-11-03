<?php
namespace Controller\Event;

class Editor extends \Controller {
	public function index($route, &$data) {
		// $this->document->addScript('view/javascript/editor/switcheditor.js');
		// $this->document->addScript('view/javascript/editor/editor-codemirror.js');
		// $this->document->addScript('view/javascript/editor/editor-ckeditor.js');
		// $this->document->addScript('view/javascript/editor/dialog-image.js');
		// $this->document->addScript('view/javascript/editor/dialog-link.js');

		// $this->document->addScript('view/javascript/codemirror/lib/codemirror.js');
		// $this->document->addScript('view/javascript/codemirror/lib/xml.js');
		// $this->document->addStyle('view/javascript/codemirror/lib/codemirror.css');
		// $this->document->addStyle('view/javascript/codemirror/theme/monokai.css');

		//$this->document->addStyle('/assets/ckeditor5-build-classic/sample/css/sample.css');

		$this->document->addScript('/assets/switcheditor/switcheditor.js');
		$this->document->addScript('/assets/switcheditor/editor-codemirror.js');
		$this->document->addScript('/assets/switcheditor/editor-ckeditor.js');
		$this->document->addScript('/assets/switcheditor/dialog-image.js');
		$this->document->addScript('/assets/switcheditor/dialog-link.js');

		$data['language'] = $this->config->get('language');
		$data['http_client'] = '/';
		$data['image_manager'] = $this->url->link('common/filemanager', 'user_token=' . $this->request->get['user_token']);
		$data['image_link'] = $this->url->link('common/filemanager/get_link', 'user_token=' . $this->request->get['user_token']);
		$data['file_manager'] = $this->url->link('common/file', 'user_token=' . $this->request->get['user_token']);
		$data['file_link'] = $this->url->link('common/file/get_link', 'user_token=' . $this->request->get['user_token']);
		$data['link_manager'] = $this->url->link('design/link', 'user_token=' . $this->request->get['user_token']);
	}
}