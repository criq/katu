<?php

namespace Katu\PDO;

class Table extends TableBase
{
	public function getCreateSyntax()
	{
		$sql = " SHOW CREATE TABLE " . $this->name;
		$res = $this->getConnection()->createQuery($sql)->getResult();

		if (isset($res[0]["View"])) {
			return $res[0]["Create View"];
		}

		return $res[0]["Create Table"];
	}

	public function touch()
	{
		$file = $this->getLastUpdatedTemporaryFile();
		$file->touch();

		return true;
	}

	public function getLastUpdatedDateTime()
	{
		$file = $this->getLastUpdatedTemporaryFile();

		return $file->getDateTimeModified();
	}

	public function getType()
	{
		$sql = " SHOW CREATE TABLE {$this->name}";
		$res = $this->getConnection()->createQuery($sql)->getResult();

		if (isset($res[0]["View"])) {
			return "view";
		}

		return "table";
	}

	public function isTable()
	{
		return $this->getType() == "table";
	}

	public function isView()
	{
		return $this->getType() == "view";
	}

	public function getChecksum()
	{
		$sql = " CHECKSUM TABLE " . $this->getName();
		$res = $this->getConnection()->createQuery($sql)->getResult()[0]["Checksum"];

		return (int)$res;
	}
}
