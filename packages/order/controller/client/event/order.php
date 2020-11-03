<?php
namespace Controller\Event;

class Order extends \Controller {
	public function startup() {
		$this->event->register('controller/order/*/before', new \Action('event/order/cart'), 0);
		$this->event->register('controller/extension/payment/*/before', new \Action('event/order/cart'), 0);
		$this->event->register('controller/extension/shipping/*/before', new \Action('event/order/cart'), 0);
		$this->event->register('controller/extension/total/*/before', new \Action('event/order/cart'), 0);
		$this->event->register('controller/order/*/before', new \Action('event/catalog/currency'), 0);

		$this->event->register('controller/common/template/before', new \Action('event/order/template'), 0);
	}

	public function cart() {
		if (!$this->registry->has('cart')) {
			$this->registry->set('cart', new \Model\Registry\Cart($this->registry));
		}
	}

	public function template($route, &$args) {
		if (isset($this->request->get['route']) && strpos($this->request->get['route'], 'order/') === 0)
			$this->document->addMeta('robots', 'noindex, nofollow');

		$args[0]['actions']['cart'] = $this->url->link('order/cart');

		$filename = 'system/cart.js';

		if (@filemtime(DIR_TEMPLATE . $filename) > @filemtime(DIR_THEME . $filename)) {
			copy(DIR_TEMPLATE . $filename, DIR_THEME . $filename);
		}

		if (file_exists(DIR_THEME . $filename)) {
			$this->document->addScript('theme/' . $filename . '?v=' . filemtime(DIR_THEME . $filename), 0, 'async');
		}

		$api = new \Url(HTTP_APPLICATION_API);

		$data_view['action_block'] = $api->link('order/cart');

		$args[0]['blocks']['cart'] = $this->load->view('block/cart', $data_view);
	}
}