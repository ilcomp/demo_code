<?php
namespace Controller\Event;

class Account extends \Controller {
	public function startup() {
		$this->event->register('view/block/column_left/before', new \Action('event/account/menu'), 0);
		$this->event->register('view/setting/mail/before', new \Action('event/account/setting_mail'), 0);
		$this->event->register('view/order/order_list/before', new \Action('event/account/order_list'), 0);
		$this->event->register('view/order/order_info/before', new \Action('event/account/order_info'), 0);

		$this->event->register('view/setting/custom_field_list/before', new \Action('event/account/custom_field_list'), 0);
		$this->event->register('model/core/custom_field/getLocations/after', new \Action('event/account/cf_getLocations'), 0);
		$this->event->register('model/core/custom_field/getTypes/after', new \Action('event/account/cf_getTypes'), 0);

		$this->event->register('model/tool/image/*/before', new \Action('event/account/image'), 0);
		$this->event->register('model/tool/file/*/before', new \Action('event/account/file'), 0);
	}

	public function menu($route, &$data) {
		$this->load->language('extension/system/account', 'temp');

		$language = $this->language->get('temp');

		$route = isset($this->request->get['route']) ? $this->request->get['route'] : $this->config->get('active_default');

		$account = array();
		$account_active = false;

		if ($this->user->hasPermission('access', 'account/account')) {
			if ($active = strpos($route, 'account/account') === 0) {
				$account_active = true;
			}

			$account[] = array(
				'name'	   => $language->get('menu_account'),
				'href'     => $this->url->link('account/account', 'user_token=' . $this->session->data['user_token']),
				'children' => array(),
				'active'   => $active && strpos($route, 'account/account_') === false
			);
		}
		if ($this->user->hasPermission('access', 'account/account_group')) {
			if ($active = strpos($route, 'account/account_group') === 0) {
				$account_active = true;
			}

			$account[] = array(
				'name'	   => $language->get('menu_group'),
				'href'     => $this->url->link('account/account_group', 'user_token=' . $this->session->data['user_token']),
				'children' => array(),
				'active'   => $active
			);
		}
		if ($this->user->hasPermission('access', 'account/account_approval')) {
			if ($active = strpos($route, 'account/account_approval') === 0) {
				$account_active = true;
			}

			$account[] = array(
				'name'	   => $language->get('menu_approval'),
				'href'     => $this->url->link('account/account_approval', 'user_token=' . $this->session->data['user_token']),
				'children' => array(),
				'active'   => $active
			);
		}
		if ($this->user->hasPermission('access', 'account/account_attempt')) {
			if ($active = strpos($route, 'account/account_attempt') === 0) {
				$account_active = true;
			}

			$account[] = array(
				'name'	   => $language->get('menu_attempt'),
				'href'     => $this->url->link('account/account_attempt', 'user_token=' . $this->session->data['user_token']),
				'children' => array(),
				'active'   => $active
			);
		}

		if ($account) {
			$menu = array_shift($data['menus']);

			array_unshift($data['menus'], array(
				'id'       => 'menu-account',
				'icon'	   => 'fa-user',
				'name'	   => $language->get('menu_account'),
				'href'     => '',
				'children' => $account,
				'active'   => $account_active
			));

			array_unshift($data['menus'], $menu);
		}
	}

	public function setting_mail($route, &$data) {
		$this->load->language('extension/system/account', 'temp');

		$language = $this->language->get('temp');

		$data['mail_alerts'][] = array(
			'text'  => $language->get('text_mail_account'),
			'value' => 'account'
		);
	}

	public function order_list($route, &$data) {
		$this->load->model('account/account');

		$this->load->language('extension/system/account', 'temp');

		$language = $this->language->get('temp');

		foreach ($data['orders'] as &$order) {
			$account_info = $this->model_account_account->getAccount($order['account_id']);

			$account_info['name'] = $order['account'];

			$order['account'] = $account_info;
		}
		unset($order);

		$data['account_status'] = true;

		$filter_account_info = $this->model_account_account->getAccount($data['filter_account_id']);

		$data['filter_account'] = $filter_account_info ? $filter_account_info['name'] : '';
	}

	public function order_info($route, &$data) {
		$this->load->model('account/account');

		$this->load->language('extension/system/account', 'temp');

		$language = $this->language->get('temp');

		$data['account'] = $this->model_account_account->getAccount($data['account_id']);

		$data['account_status'] = true;
	}

	public function custom_field_list($route, &$data) {
		$this->load->language('extension/system/account', 'temp');

		$language = $this->language->get('temp');

		$data['text_account'] = $language->get('text_account');
		$data['text_account_contact'] = $language->get('text_account') . ' > ' . $language->get('text_account_contact');
		$data['text_account_address'] = $language->get('text_account') . ' > ' . $language->get('text_account_address');

		$this->load->model('account/account_group');

		$account_groups = $this->model_account_account_group->getAccountGroups();

		foreach ($account_groups as $account_group) {
			$data['text_account_group_' . $account_group['account_group_id']] = $language->get('text_account') . ' > ' . $account_group['name'];
		}
	}

	public function cf_getLocations($route, $data, &$output) {
		$this->load->language('extension/system/account', 'temp');

		$language = $this->language->get('temp');

		$output['account'] = array(
			'label' => $language->get('text_account'),
			'options' => array(
				array(
					'value' => 'account_contact',
					'name' => $language->get('text_account') . ' > ' . $language->get('text_account_contact'),
				),
				array(
					'value' => 'account_address',
					'name' => $language->get('text_account') . ' > ' . $language->get('text_account_address'),
				)
			)
		);

		$this->load->model('account/account_group');

		$account_groups = $this->model_account_account_group->getAccountGroups();

		foreach ($account_groups as $account_group) {
			$output['account']['options'][] = array(
				'value' => 'account_group_' . $account_group['account_group_id'],
				'name' => $language->get('text_account') . ' > ' . $account_group['name'],
			);
		}
	}

	public function image($route, $data) {
		if (strpos($data[0], 'account') === 0) {
			if (strpos($route, 'link') && empty($data[1]))
				$image = array('type' => 'link', 'image' => $data[0]);
			elseif (strpos($route, 'resize') && empty($data[3]))
				$image = array('type' => 'resize', 'image' => $data[0], 'height' => $data[1], 'width' => $data[1]);
			elseif (strpos($route, 'crop') && empty($data[3]))
				$image = array('type' => 'crop', 'image' => $data[0], 'height' => $data[1], 'width' => $data[1]);

			if (!empty($image)) {
				$image['user_token'] = $this->request->get['user_token'];

				return $this->url->link('tool/image', $image);
			}
		}
	}

	public function file($route, $data) {
		if (strpos($data[0], 'account') === 0) {
			if (strpos($route, 'link') && empty($data[1]))
				$file = array('type' => 'link', 'file' => $data[0]);

			if (!empty($file)) {
				$file['user_token'] = $this->request->get['user_token'];

				return $this->url->link('tool/file', $file);
			}
		}
	}
}