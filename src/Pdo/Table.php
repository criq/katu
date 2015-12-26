<?php

namespace Katu\Pdo;

use \Katu\Utils\Cache;

class Table extends TableBase {

	public function touch() {
		return \Katu\Utils\Tmp::set(static::getLastUpdatedTmpName(), microtime(true));
	}

	public function getLastUpdatedTime() {
		return \Katu\Utils\Tmp::get(static::getLastUpdatedTmpName());
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
