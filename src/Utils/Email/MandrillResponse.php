<?php

namespace Katu\Utils\Email;

class MandrillResponse {

	private $statuses = [];

	public function __construct($response = []) {
		foreach ($response as $status) {
			$this->statuses[] = new MandrillStatus($status);
		}
	}

	public function isSuccessful() {
		foreach ($this->statuses as $status) {
			if (!$status->isSuccessful()) {
				return false;
			}
		}

		return true;
	}

}
