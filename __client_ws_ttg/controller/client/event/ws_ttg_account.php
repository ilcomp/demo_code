<?php
namespace Controller\Event;

class WSTTGAccount extends \Controller {
	public function index() {
		$this->event->register('view/template/header/before', new \Action('event/ws_ttg_account/header'), 0);
		$this->event->register('view/account/account/before', new \Action('event/ws_ttg_account/account'), 0);
		$this->event->register('view/account/edit/before', new \Action('event/ws_ttg_account/edit'), 0);
		$this->event->register('view/account/order_info/before', new \Action('event/ws_ttg_account/order_info'), 0);
	}

	public function header($route, &$data) {
		if ($this->account->isLogged()) {
			$this->load->model('account/account');

			$account = $this->model_account_account->getAccountWithName($this->account->getId());

			if ($account['account'])
				$data['text_account'] = $account['account'];
		}
	}

	public function account($route, &$data) {
		$data['account_edit'] = $this->load->controller('account/edit', array('return_content' => true));
		$data['account_order'] = $this->load->controller('account/order', array('return_content' => true));

		$data['continue'] = $this->url->link('account/account');
		$data['logout'] = $this->url->link('account/logout');
		$data['account'] = $this->url->link('account/account');
		$data['order'] = $this->url->link('account/order');

		if (isset($this->request->get['code']))
			$data['tab_code'] = $this->request->get['code'];
	}

	public function edit($route, &$data) {
		$data['account_email'] = $this->load->controller('extension/account_login/telephone/email', array('return_content' => true));
		$data['account_telephone'] = $this->load->controller('extension/account_login/telephone/form', array('return_content' => true));
		$data['account_password'] = $this->load->controller('account/password', array('return_content' => true));
	}

	public function order_info($route, &$data) {
		$breadcrumb = array_pop($data['breadcrumbs']);

		array_pop($data['breadcrumbs']);

		$data['breadcrumbs'][] = $breadcrumb;
	}
}