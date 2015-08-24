<?php

namespace Katu\Utils\Email;

class MandrillStatus {

	private $id;
	private $emailAddress;
	private $status;
	private $rejectReason;

	public function __construct($status = []) {
		if (isset($status['_id'])) {
			$this->id = $status['_id'];
		}
		if (isset($status['email'])) {
			$this->emailAddress = $status['email'];
		}
		if (isset($status['status'])) {
			$this->status = $status['status'];
		}
		if (isset($status['reject_reason'])) {
			$this->rejectReason = $status['reject_reason'];
		}
	}

	public function isSuccessful() {
		return in_array($this->status, ['sent', 'queued']);
	}

}
