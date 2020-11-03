<?php
namespace Controller\Event;

class Order extends \Controller {
	public function startup() {
		$this->event->register('controller/order/*/before', new \Action('event/order/cart'), 0);
		$this->event->register('controller/order/*/before', new \Action('event/catalog/currency'), 0);
	}

	public function cart() {
		if (!$this->registry->has('cart')) {
			$this->registry->set('cart', new \Model\Registry\Cart($this->registry));
		}
	}
}