<?php
namespace Model\Extension\Total;

class Reward extends \Model {
	public function getTotal($total) {
		if ($this->config->get('total_reward_status') && $this->account->isLogged()) {
			$this->load->language('extension/total/reward', 'reward');

			$reward = 0;

			foreach ($this->cart->getProducts() as $product) {
				$reward += $this->getProductRewardValue($product['product_id']);
			}

			if ($reward > 0) {
				$total['totals'][] = array(
					'code'        => 'reward',
					'title'       => $this->language->get('reward')->get('text_reward_product'),
					'description' => '',
					'value'       => $reward,
					'sort_order'  => $this->config->get('total_reward_sort_order')
				);
			}

			if (!empty($this->request->post['reward_status']) && !empty($this->request->post['reward_value'])) {
				$this->session->data['reward_value'] = (int)$this->request->post['reward_value'];
				$this->session->data['reward_status'] = true;
			}

			if (isset($this->session->data['reward_status']) && (int)$this->session->data['reward_status'] && isset($this->session->data['reward_value']) && (int)$this->session->data['reward_value']) {
				$data['min'] = (int)$this->config->get('total_reward_minimum');
				$data['max'] = (int)$this->getAccountRewardValue($this->account->getId());
				$cart_total = $this->cart->getSubTotal();

				if ((int)$this->config->get('total_reward_percent') && ($this->config->get('total_reward_percent')/100 * $this->cart->getSubTotal()) < $data['max'])
					$data['max'] = $this->config->get('total_reward_percent')/100 * $this->cart->getSubTotal();

				if ((int)$this->session->data['reward_value'] > (int)$cart_total)
					$this->session->data['reward_value'] = (int)$cart_total;

				if ((int)$this->session->data['reward_value'] >= (int)$data['min'] && (int)$this->session->data['reward_value'] <= (int)$data['max']) {
					$total['totals'][] = array(
						'code'        => 'reward',
						'title'       => $this->language->get('reward')->get('text_reward'),
						'description' => '',
						'value'       => -$this->session->data['reward_value'],
						'sort_order'  => $this->config->get('total_reward_sort_order')
					);

					$total['total'] -= $this->session->data['reward_value'];
				}
			}
		}
	}

	public function addAccountReward($account_id, $reward, $comment = '') {
		$this->db->query("INSERT INTO " . DB_PREFIX . "reward_account SET account_id = '" . (int)$account_id . "', reward = '" . (int)$reward . "', comment = '" . $this->db->escape((string)$comment) . "', date_added = NOW()");
	}

	public function deleteAccountReward($account_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "reward_account WHERE account_id = '" . (int)$account_id . "'");
	}

	public function getAccountRewards($data) {
		$sql = "SELECT * FROM " . DB_PREFIX . "reward_account WHERE 1";

		if (!empty($data['filter_account_id'])) {
			$sql .= " AND account_id = '" . (int)$data['filter_account_id'] . "'";
		}

		$sort_data = array(
			'account' => 'account',
			'date_added' => 'date_added'
		);

		if (isset($data['sort']) && isset($sort_data[$data['sort']])) {
			$sql .= " ORDER BY " . $sort_data[$data['sort']];
		} else {
			$sql .= " ORDER BY date_added";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getAccountRewardValue($account_id) {
		$query = $this->db->query("SELECT SUM(reward) as reward FROM " . DB_PREFIX . "reward_account WHERE account_id = '" . (int)$account_id . "'");

		return $query->num_rows ? $query->row['reward'] : 0;
	}

	public function updateProductReward($product_id, $reward) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "reward_product WHERE product_id = '" . (int)$product_id . "'");

		$this->db->query("INSERT INTO " . DB_PREFIX . "reward_product SET product_id = '" . (int)$product_id . "', reward = '" . (int)$reward . "'");
	}

	public function deleteProductReward($product_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "reward_product WHERE product_id = '" . (int)$product_id . "'");
	}

	public function getProductReward($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "reward_product WHERE product_id = '" . (int)$product_id . "'");

		return $query->row;
	}

	public function getProductRewardValue($product_id) {
		$query = $this->db->query("SELECT reward FROM " . DB_PREFIX . "reward_product WHERE product_id = '" . (int)$product_id . "'");

		return $query->num_rows ? $query->row['reward'] : 0;
	}
}
