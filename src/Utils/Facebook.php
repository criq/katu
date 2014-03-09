<?php

namespace Jabli\Utils;

class Facebook {

	public $facebook;

	public function __construct() {
		$this->facebook = new \Facebook(array(
			'appId'  => \Jabli\Config::get('facebook', 'app_id'),
			'secret' => \Jabli\Config::get('facebook', 'secret'),
		));
	}

	public function getLoginURL() {
		return $this->facebook->getLoginUrl();
	}

	public function getCookieName() {

	}

}
