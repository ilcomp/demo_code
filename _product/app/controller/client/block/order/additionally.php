<?php
namespace Controller\Block\Order;

class Additionally extends \Controller {
	public function index() {
		if ($this->config->get('block_order_additionally')) {
			return $this->config->get('block_order_additionally');
		}

		$this->load->model('core/extension');

		$data['additionallys'] = array();

		$sort_order = array();

		$results = $this->model_core_extension->getExtensions('additionally');

		foreach ($results as $key => $value) {
			$sort_order[$key] = $this->config->get('additionally_' . $value['code'] . '_sort_order');
		}

		array_multisort($sort_order, SORT_ASC, $results);

		foreach ($results as $result) {
			if ($this->config->get('additionally_' . $result['code'] . '_status')) {
				if ($this->request->server['REQUEST_METHOD'] == 'POST') {
					if (isset($this->request->post['additionally'][$result['code']])) {
						$this->session->data['additionally'][$result['code']] = $this->request->post['additionally'][$result['code']];
					} else {
						$this->session->data['additionally'][$result['code']] = '';
					}
				}

				$data['additionallys'][$result['code']] = $this->load->controller('extension/additionally/' . $result['code'], $result);

				if (!empty($this->session->data['additionally']))
					$this->session->data['order']['additionally'] = $this->session->data['additionally'];
			}
		}

		return $this->load->view('block/order/additionally', $data);
	}

	public function form() {
		$this->config->set('block_order_additionally', $this->index());
	}
}
