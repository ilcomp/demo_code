<?php
namespace Controller\Event;

class AccountPermission extends \Controller {
	public function index() {
		$this->event->register('model/account/account/editAccount/before', new \Action('event/account_permission/editAccount'), 0);

		$this->event->register('model/order/order/getOrder/before', new \Action('event/account_permission/getOrder'), 0);
		$this->event->register('model/order/order/getOrders/before', new \Action('event/account_permission/getOrders'), 0);
		$this->event->register('model/order/order/getTotalOrders/before', new \Action('event/account_permission/getOrders'), 0);
	}

	public function editAccount($route, &$data) {
		$data[0] = $this->account->getId();
	}

	public function getOrder($route, &$data) {
		$filter = array(
			'account_id' => $this->account->getId()
		);

		if (!isset($data[1]['filter']))
			$data[1]['filter'] = array();

		$data[1]['filter'] = array_merge($data[1]['filter'], $filter);
	}

	public function getOrders($route, &$data) {
		$filter = array(
			'account_id' => $this->account->getId()
		);

		if (!isset($data[0]['filter']))
			$data[0]['filter'] = array();

		$data[0]['filter'] = array_merge($data[0]['filter'], $filter);
	}
}