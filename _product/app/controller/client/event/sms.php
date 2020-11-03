<?php
namespace Controller\Event;

class SMS extends \Controller {
	public function startup() {
		if ($this->config->get('sms_engine') && $this->config->get('sms_login') && $this->config->get('sms_api_key')) {
			$class = '\\Model\\Registry\\'. $this->config->get('sms_engine');
			$sms = new $class ($this->config->get('sms_login'), $this->config->get('sms_api_key'), $this->config->get('sms_sign'));

			$this->registry->set('sms', $sms);

			if ($this->sms !== null) {
				$this->event->register('view/account/*login/before', new \Action('event/sms/view'), 0);
				$this->event->register('view/account/*register/before', new \Action('event/sms/view'), 0);
				$this->event->register('view/account/*forgotten/before', new \Action('event/sms/view'), 0);
				$this->event->register('account/*', new \Action('event/sms/view'), 0);
			}
		}
	}

	public function view($route, &$data) {
		if (isset($this->session->data['error_sms']) || isset($this->session->data['sms_ids'])) {
			$this->load->language('account/sms', 'temp');

			$temp = $this->language->get('temp');
			$data_sms = $temp->all();

			$this->load->model('core/sms');

			$data_sms['action'] = $this->url->link('account/sms/send');

			$data_sms['error_sms'] = isset($this->session->data['error_sms']) ? $this->session->data['error_sms'] : '';

			$data_sms['sms_code'] = isset($this->request->post['sms_code']) ? $this->request->post['sms_code'] : '';

			$date_added = 0;

			if (!empty($this->session->data['sms_ids'])) {
				foreach ($this->session->data['sms_ids'] as $sms_id) {
					$sms_info = $this->model_core_sms->getSms($sms_id);

					if ($sms_info && $sms_info['status_id'] != 9) {
						$date_added = time() - strtotime($sms_info['date_added']);

						break;
					}
				}
			}

			$data_sms['date_added'] = max(0, $this->config->get('sms_code_lifetime') - $date_added);

			if ($date_added < 10)
				$data_sms['text_info'] = $temp->get('alert_sms_send');
			else
				$data_sms['text_info'] = $temp->get('alert_sms_time');

			$data['alert_info'] = sprintf($data_sms['text_info'], ceil($data_sms['date_added'] / 60));

			$data['error_warning'] = '';
			unset($this->session->data['error_warning']);

			unset($this->session->data['sms_ids']);
			unset($this->session->data['error_sms']);

			$data['sms'] = $this->load->view('account/sms_view', $data_sms);
		} else {
			$data['sms'] = '';
		}
	}

	public function validate($route, $arg) {
		$telephone = '';
		$account_id = 0;
		$type = 0;

		if ($route == 'account/login') {
			$telephone = preg_replace(array('/^8/','/\D/'), array('7', ''), $arg['telephone']);
			$account_id = $arg['account_id'];
			$type = 1;
		} elseif ($route == 'account/register') {
			$telephone = preg_replace(array('/^8/','/\D/'), array('7', ''), $arg['telephone']);
			$account_id = 0;
			$type = 2;
		} elseif ($route == 'account/forgotten') {
			$this->load->model('account/account_auth');

			$account_info = $this->model_account_account_auth->getAccountAuthByData(array('type' => 'email', 'login' => $arg['email'], 'status' => 1));

			if ($account_info && $account_info['status']) {
				$telephone = preg_replace(array('/^8/','/\D/'), array('7', ''), $account_info['telephone']);
				$account_id = $account_info['account_id'];
				$type = 3;
			}
		}

		unset($this->session->data['error_sms']);
		unset($this->session->data['sms_ids']);

		if (isset($this->session->data['error_sms_time'])) {
			$this->session->data['error_sms'] = $this->session->data['error_sms_time'];

			unset($this->session->data['error_sms_time']);
		}

		if (!isset($this->session->data['sms_info'])) {
			$this->session->data['sms_info'] = array();
		}

		if (strlen($telephone) == 11 && $telephone[0] == 7) {
			$this->load->language('account/sms_send');
			$this->load->model('core/sms');

			if (!empty($this->request->post['sms_new']) && isset($this->session->data['sms_info'][$account_id . '_' . $type . '_' . $telephone])) {
				unset($this->session->data['sms_info'][$account_id . '_' . $type . '_' . $telephone]);

				unset($this->request->post['sms_code']);
			}

			if (isset($this->request->post['sms_code'])) {
				$sms_code = $this->request->post['sms_code'];

				$sms_id = -1;

				if (isset($this->session->data['sms_info'][$account_id . '_' . $type . '_' . $telephone])) {
					$this->session->data['sms_ids'] = $this->session->data['sms_info'][$account_id . '_' . $type . '_' . $telephone];

					foreach ($this->session->data['sms_info'][$account_id . '_' . $type . '_' . $telephone] as $id) {
						$sms_id = max($sms_id, $this->model_core_sms->getValidateSmsByCode($id, $sms_code));

						if ($sms_code && $sms_id > 0) {
							$this->model_core_sms->updateStatusSms($sms_id, 9);

							unset($this->session->data['sms_info'][$account_id . '_' . $type . '_' . $telephone]);

							return 0;
						}
					}
				}

				if ($sms_id == -1) {
					$this->session->data['error_sms_time'] = '';// $this->language->get('error_sms_time');

					unset($this->session->data['sms_info'][$account_id . '_' . $type . '_' . $telephone]);
				}
			} else {
				unset($this->session->data['sms_info'][$account_id . '_' . $type . '_' . $telephone]);
			}

			if (!isset($this->session->data['sms_info'][$account_id . '_' . $type . '_' . $telephone])) {
				if ($this->sms->has_balance(10)) {
					unset($this->request->post['sms_code']);

					$code_len = 4;
					$code = rand(pow(10, $code_len-1),pow(10, $code_len)-1);
					$code_len = 8;
					$id = rand(pow(10, $code_len-1),pow(10, $code_len)-1);

					$data = $this->sms->send($telephone, sprintf($this->language->get('sms_login'), $code), 'DIRECT', null, $this->url->link('account/sms/status'));

					if ($this->sms->status_ok()) {
						$ids = array();

						if (isset($data['id'])) {
							$this->model_core_sms->addSmsApproval($data['id'], $data['number'], $code, $data['status'], $type, $account_id);

							$ids[] = $data['id'];
						} else {
							foreach ($data as $value) {
								if ($value['status']) {
									$this->model_core_sms->addSmsApproval($value['id'], $value['number'], $code, $value['status'], $type, $account_id);

									$ids[] = $value['id'];
								} else {
									$this->session->data['error_sms'] =  $this->language->get('error_sms');

									return 1;
								}
							}
						}

						$this->load->model('account/account_attempt');

						$this->model_account_account_attempt->deleteAccountAttempts($this->request->post['login']);

						$this->session->data['sms_info'][$account_id . '_' . $type . '_' . $telephone] = $ids;

						$this->session->data['sms_ids'] = $ids;
					} else {
						$this->session->data['error_sms'] =  $this->language->get('error_sms');
					}

					return 1;
				} elseif ($this->sms->status_ok()) {
					$this->log->write('SMS Error: Balance empty!');
				} else {
					$this->log->write('SMS Error: Service is unavailable!');
				}
			} else {
				$this->session->data['error_sms'] = $this->language->get('error_sms_code');

				return 1;
			}
		}
	}
}