<?php

namespace Jabli\Models;

use \Jabli\Exception;

class User extends \Jabli\Model {

	const TABLE = 'users';

	static function create() {
		return self::insert();
	}

	static function getLoggedIn() {
		return self::getByPK(\Jabli\Session::get('fw.user.id'));
	}

	public function addUserService($service_name, $service_user_id) {
		return UserService::create($this, $service_name, $service_user_id);
	}

	public function login() {
		return \Jabli\Session::set('fw.user.id', (int) $this->id);
	}

	public function hasAC($ac) {
		return (bool) UserAC::getByProperties(array(
			'user_id' => (int)    ($this->id),
			'ac'      => (string) (trim($ac)),
		))->getOne();
	}

	public function addAC($ac) {
		return UserAC::make($this, $ac);
	}

}
