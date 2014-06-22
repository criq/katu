<?php

namespace Katu\Models;

use \Katu\Exception;

class User extends \Katu\Model {

	const TABLE = 'users';

	/*
	static function createTable() {
		const CREATE_COMMAND = "
	CREATE TABLE `users` (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`timeCreated` datetime NOT NULL,
		`emailAddressId` int(11) unsigned NOT NULL,
		`name` varchar(255) NOT NULL DEFAULT '',
		`password` text NOT NULL,
		`isActive` enum('0','1') NOT NULL DEFAULT '1',
		PRIMARY KEY (`id`),
		KEY `emailAddressId` (`emailAddressId`)
		) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
	";
	}
	*/

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

	static function logout() {
		return \Katu\Session::reset('katu.user.id');
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
