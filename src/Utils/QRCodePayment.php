<?php

namespace Katu\Utils;

class QRCodePayment {

	public $version = '1.0';
	public $params = array();

	public function __construct($version = NULL) {
		if (!is_null($version)) {
			$this->version = $version;
		}
	}

	static function make($version = NULL) {
		return new self($version);
	}

	public function setAccount($value) {
		$this->params['ACC'] = $value;

		return $this;
	}

	public function setAmount($value) {
		$this->params['AM'] = $value;

		return $this;
	}

	public function setCurrency($value) {
		$this->params['CC'] = $value;

		return $this;
	}

	public function setInMessage($value) {
		$this->params['MSG'] = $value;

		return $this;
	}

	public function setVS($value) {
		$this->params['X-VS'] = $value;

		return $this;
	}

	public function setURL($value) {
		$this->params['X-URL'] = $value;

		return $this;
	}

	public function getString() {
		$string = 'SPD*' . $this->version . '*';

		foreach ($this->params as $key => $value) {
			$string .= $key . ':' . $value . '*';
		}

		return $string;
	}

}
