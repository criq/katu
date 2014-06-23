<?php

namespace Katu\Models;

use \Katu\Exception;

class UserService extends \Katu\Model {

	const TABLE = 'user_services';

	static function create($user, $serviceName, $serviceUserID) {
		return static::insert(array(
			'timeCreated'   => (string) (\Katu\Utils\DateTime::get()->getDBDatetimeFormat()),
			'userId'        => (int)    ($user->id),
			'serviceName'   => (string) ($serviceName),
			'serviceUserID' => (string) ($serviceUserID),
		));
	}

	static function getByServiceAndID($serviceName, $serviceUserID) {
		return static::getBy(array(
			'serviceName'   => (string) ($serviceName),
			'serviceUserID' => (string) ($serviceUserID),
		));
	}

	public function getUser() {
		return \App\Models\User::get($this->userID);
	}

	public function setServiceAccessToken($serviceAccessToken) {
		$this->update('serviceAccessToken', $serviceAccessToken);

		return TRUE;
	}

}
