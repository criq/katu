<?php

namespace Katu\Utils;

class Facebook {

	public $facebook;

	public function __construct() {
		$this->facebook = new \Facebook(array(
			'appId'  => \Katu\Config::get('facebook', 'app_id'),
			'secret' => \Katu\Config::get('facebook', 'secret'),
		));

		$this->facebook->setAccessToken($this->getAccessToken());

		return TRUE;
	}

	public function getLoginURL() {
		return \Katu\Types\URL::make($this->facebook->getLoginUrl(array(
			'redirect_uri' => (string) \Katu\Utils\URL::getCurrent()->getWithoutQuery(),
		)));
	}

	public function getAppID() {
		return $this->facebook->getAppID();
	}

	public function getAppSecret() {
		return $this->facebook->getAppSecret();
	}

	public function getTokenURL($code) {
		return \Katu\Types\URL::make('https://graph.facebook.com/oauth/access_token', array(
			'code'          => $code,
			'client_id'     => $this->getAppID(),
			'client_secret' => $this->getAppSecret(),
			'redirect_uri'  => (string) \Katu\Utils\URL::getCurrent()->getWithoutQuery(),
		));
	}

	public function getToken($code) {
		$curl = new \Curl();
		$token_url = $this->getTokenURL($code);

		if ($curl->get((string) $token_url) === 0) {
			parse_str($curl->response, $params);
			if (isset($params['access_token'])) {
				return $params['access_token'];
			}
		}

		return FALSE;
	}

	public function getVariableName($suffix = NULL) {
		return 'facebook_' . $this->getAppID() . ($suffix ? '_' . $suffix : NULL);
	}

	public function setAccessToken($access_token) {
		return \Katu\Cookie::set($this->getVariableName('access_token'), $access_token);
	}

	public function getAccessToken() {
		return \Katu\Cookie::get($this->getVariableName('access_token'));
	}

	public function resetAccessToken() {
		return \Katu\Cookie::remove($this->getVariableName('access_token'));
	}

	public function setUser($user_id) {
		return \Katu\Session::set($this->getVariableName('user_id'), $user_id);
	}

	public function getUser($user_id) {
		return \Katu\Session::get($this->getVariableName('user_id'));
	}

}
