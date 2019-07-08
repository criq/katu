<?php

namespace Katu\PDO;

class Table extends TableBase {

	public function touch() {
		$file = static::getLastUpdatedTemporaryFile();
		$file->touch();

		return true;
	}

	public function getLastUpdatedTime() {
		$file = static::getLastUpdatedTemporaryFile();

		return $file->getDateTimeModified();
	}

	public function getType() {
		$sql = " SHOW CREATE TABLE " . $this->name;
		$res = $this->pdo->createQuery($sql)->getResult();

		if (isset($res[0]['View'])) {
			return 'view';
		}

		return 'table';
	}

	public function isTable() {
		return $this->getType() == 'table';
	}

	public function isView() {
		return $this->getType() == 'view';
	}

	public function getCreateSyntax() {
		$sql = " SHOW CREATE TABLE " . $this->name;
		$res = $this->pdo->createQuery($sql)->getResult();

		if (isset($res[0]['View'])) {
			return $res[0]['Create View'];
		}

		return $res[0]['Create Table'];
	}

}
