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

	static function createWithEmailAddress($emailAddress) {
		if (!$emailAddress || !($emailAddress instanceof EmailAddress)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid e-mail address.");
		}

		// Look for another user with this e-mail address.
		if (static::getBy(array(
			'emailAddressId' => $emailAddress->id,
		))->getTotal()) {
			throw new \Katu\Exceptions\ArgumentErrorException("E-mail address is already in use.");
		}

		$object = parent::create();
		$object->setEmailAddress($emailAddress);
		$object->save();

		return $object;
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

	public function setEmailAddress($emailAddress) {
		if (!$emailAddress || !($emailAddress instanceof EmailAddress)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid e-mail address.");
		}

		// Look for another user with this e-mail address.
		if (static::getBy(array(
			'emailAddressId' => $emailAddress->id,
			new CmpNotEq(static::getColumn('id'), $this->id),
		))->getTotal()) {
			throw new \Katu\Exceptions\ArgumentErrorException("E-mail address is used by another user.");
		}

		$this->update('emailAddressId', $emailAddress->id);

		return TRUE;
	}

	public function setName($name) {
		$this->update('name', trim($name));

		return TRUE;
	}

	public function login() {
		return \Katu\Session::set('katu.user.id', (int) $this->id);
	}

	static function logout() {
		return \Katu\Session::reset('katu.user.id');
	}

	public function hasRole($role) {
		return (bool) \App\Models\UserRole::getOneBy(array(
			'userId' => (int) ($this->id),
			'roleId' => (int) ($role->id),
		));
	}

	public function addRole($role) {
		return \App\Models\UserRole::make($this, $role);
	}

	public function hasPermission($permission) {
		return (bool) \App\Models\UserPermission::getOneBy(array(
			'userId'     => (int)    ($this->id),
			'permission' => (string) (trim($permission)),
		));
	}

	public function addPermission($permission) {
		return \App\Models\UserPermission::make($this, $permission);
	}

	static function currentHasPermission($permission) {
		$user = static::getCurrent();
		if (!$user) {
			return FALSE;
		}

		return $user->hasPermission($permission);
	}

}
