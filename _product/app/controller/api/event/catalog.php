<?php
namespace Controller\Event;

class Catalog extends \Controller {
	public function startup() {
		$this->event->register('model/catalog/*/before', new \Action('event/catalog/currency'), 0);

		// $this->load->controller('event/catalog_permission');
	}

	public function currency() {
		if (!$this->registry->has('currency')) {
			$code = '';

			$this->load->model('localisation/currency');

			$currencies = $this->model_localisation_currency->getCurrencies();

			if (isset($this->session->data['currency'])) {
				$code = $this->session->data['currency'];
			}

			if (isset($this->request->cookie['currency']) && !array_key_exists($code, $currencies)) {
				$code = $this->request->cookie['currency'];
			}

			if (!array_key_exists($code, $currencies)) {
				$code = $this->config->get('catalog_currency');
			}

			if (!isset($this->session->data['currency']) || $this->session->data['currency'] != $code) {
				$this->session->data['currency'] = $code;
			}

			// Set a new currency cookie if the code does not match the current one
			if (!isset($this->request->cookie['currency']) || $this->request->cookie['currency'] != $code) {
				setcookie('currency', $code, time() + 60 * 60 * 24 * 30, '/', $this->request->server['HTTP_HOST']);
			}

			$this->registry->set('currency', new \Model\Registry\Currency($this->registry));

			$this->load->model('catalog/price');

			$this->config->set('catalog_price_id', $this->model_catalog_price->getPriceIdMain());

			if ($this->currency->get($this->session->data['currency']))
				$this->language->set('catalog_price_format', $this->currency->get($this->session->data['currency']));
		}
	}
}