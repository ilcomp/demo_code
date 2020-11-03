<?php
namespace Model\Order;

class OrderHistory extends \Model {
	public function addOrderHistory($order_id, $order_status_id, $comment = '', $notify = false, $override = false) {
		$this->load->model('order/order');

		$order_info = $this->model_order_order->getOrder($order_id);

		if ($order_info) {
			$order_info['totals'] = $this->model_order_order->getOrderTotals($order_info['order_id']);

			$event_status_id = $this->event->trigger('model/order/order_history/order_status_id/before', array('model/order/order_history/order_status_id/before', array($order_info, $order_status_id, $comment, $notify, $override)));

			if ($event_status_id)
				$order_status_id = $event_status_id;

			// Update the DB with the new statuses
			$this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$order_status_id . "', date_modified = NOW() WHERE order_id = '" . (int)$order_id . "'");

			$this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$order_status_id . "', notify = '" . (int)$notify . "', comment = '" . $this->db->escape($comment) . "', date_added = NOW()");

			$order_history_id = $this->db->getLastId();

			$event_history_id = $this->event->trigger('model/order/order_history/order_status_id/after', array('model/order/order_history/order_status_id/after', array($order_info, $order_status_id, $comment, $notify, $override), $order_history_id));

			if ($event_history_id)
				$order_history_id = $event_history_id;

			return $order_history_id;
		}
	}

	public function getOrderHistories($order_id, $data = array()) {
		$sql = "SELECT oh.date_added, lid.name AS status, oh.comment, oh.notify FROM " . DB_PREFIX . "order_history oh LEFT JOIN " . DB_PREFIX . "listing_item_description lid ON oh.order_status_id = lid.listing_item_id WHERE oh.order_id = '" . (int)$order_id . "' AND lid.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		$sql .= "  ORDER BY oh.date_added DESC";

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getTotalOrderHistories($order_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "order_history WHERE order_id = '" . (int)$order_id . "'");

		return $query->row['total'];
	}

	public function getTotalOrderHistoriesByOrderStatusId($order_status_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "order_history WHERE order_status_id = '" . (int)$order_status_id . "'");

		return $query->row['total'];
	}
}