<?php

namespace Jabli\Utils;

class Facebook {

	public $facebook;

	public function __construct() {
		$this->facebook = new \Facebook(array(
			'appId'  => \Jabli\Config::get('facebook', 'app_id'),
			'secret' => \Jabli\Config::get('facebook', 'secret'),
		));

		$this->facebook->setAccessToken($this->getAccessToken());

		return TRUE;
	}

	public function getLoginURL() {
		return $this->facebook->getLoginUrl(array(
			'redirect_uri' => URL::getCurrent(),
		));
	}

	public function getAppID() {
		return $this->facebook->getAppID();
	}

	public function getAppSecret() {
		return $this->facebook->getAppSecret();
	}

	public function getTokenURL($code) {
		$params = array(
			'code'          => $code,
			'client_id'     => $this->getAppID(),
			'client_secret' => $this->getAppSecret(),
			'redirect_uri'  => URL::getCurrent(),
		);

		return 'https://graph.facebook.com/oauth/access_token?' . http_build_query($params);
	}

	public function getToken($code) {
		$curl = new \Curl();
		if ($curl->get($this->getTokenURL($code)) === 0) {
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
		return \Jabli\Cookie::set($this->getVariableName('access_token'), $access_token);
	}

	public function getAccessToken() {
		return \Jabli\Cookie::get($this->getVariableName('access_token'));
	}

	public function resetAccessToken() {
		return \Jabli\Cookie::remove($this->getVariableName('access_token'));
	}

	public function setUser($user_id) {
		return \Jabli\Session::set($this->getVariableName('user_id'), $user_id);
	}

	public function getUser($user_id) {
		return \Jabli\Session::get($this->getVariableName('user_id'));
	}

}
