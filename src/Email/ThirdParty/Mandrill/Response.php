<?php

namespace Katu\Email\ThirdParty\Mandrill;

class Response {

	private $statuses = [];

	public function __construct($response = []) {
		foreach ($response as $status) {
			$this->statuses[] = new Status($status);
		}
	}

	public function isSuccessful() {
		if (!$this->statuses) {
			return false;
		}

		foreach ($this->statuses as $status) {
			if (!$status->isSuccessful()) {
				return false;
			}
		}

		return true;
	}

}
