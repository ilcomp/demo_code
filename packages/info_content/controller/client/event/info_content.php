<?php
namespace Controller\Event;

class InfoContent extends \Controller {
	public function startup() {
		$this->event->register('view/info/article/before', new \Action('event/info_content/article'), 0);
		$this->event->register('view/common/contact/before', new \Action('event/info_content/article'), 0);
	}

	public function article($route, &$data) {
		$this->load->model('info/content');
		$this->load->model('core/custom_field');

		$filter_data = array(
			'filter' => array(
				'article_id' => $this->request->get['info_article_id']
			)
		);

		$contents = $this->model_info_content->getContents($filter_data);

		if (!empty($contents)) {
			$this->load->model('core/custom_field');

			$custom_fields = $this->model_core_custom_field->getCustomFieldsByLocation('info_content_' . $this->request->get['info_article_id']);

			foreach ($contents as &$content) {
				$content['custom_fields'] = array();

				$custom_field_values = $this->model_info_content->getContentCustomFields($content['content_id']);

				foreach ($custom_fields as $custom_field) {
					$language_id = $custom_field['multilanguage'] ? $this->config->get('config_language_id') : -1;

					if (isset($custom_field_values[$custom_field['custom_field_id']]) && isset($custom_field_values[$custom_field['custom_field_id']][$language_id])) {
						$value = $custom_field_values[$custom_field['custom_field_id']][$language_id];
					} elseif (isset($custom_field['setting']['default']))
						$value = $custom_field['setting']['default'];
					else
						$value = '';

					$content[$custom_field['code']] = $this->model_core_custom_field->getAsValue($value, $custom_field);
				}
			}
			unset($content);

			$data_view['contents'] = $contents;

			$data['info_content'] = $this->load->view('info/content', $data_view);
		}
	}
}