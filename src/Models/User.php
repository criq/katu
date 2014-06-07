<?php

namespace Katu\Models;

use \Katu\Exception;

class User extends \Katu\Model {

	const TABLE = 'users';

	static function create() {
		return self::insert(array(
			'timeCreated' => (string) (\Katu\Utils\DateTime::get()->getDBDatetimeFormat()),
		));
	}

	static function getCurrent() {
		return self::get(\Katu\Session::get('katu.user.id'));
	}

	public function addUserService($serviceName, $serviceUserID) {
		return \App\Models\UserService::create($this, $serviceName, $serviceUserID);
	}

	public function getDefaultUserServiceByName($serviceName) {
		return \App\Models\UserService::getOneBy(array(
			'userID'      => (int)    ($this->id),
			'serviceName' => (string) ($serviceName),
		));
	}

	public function login() {
		return \Katu\Session::set('katu.user.id', (int) $this->id);
	}

	public function hasPermission($ac) {
		return (bool) \App\Models\UserAC::getOneBy(array(
			'userId' => (int)    ($this->id),
			'ac'     => (string) (trim($ac)),
		));
	}

	public function addPermission($ac) {
		return \App\Models\UserAC::make($this, $ac);
	}

}
