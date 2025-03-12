<?php

namespace Katu\Config;

class PaginationConfig extends \Katu\Config\Config
{
	public function getQueryParam(): string
	{
		return "page";
	}

	public function getPerPage(): int
	{
		return 25;
	}

	public function getPrevCopy(): string
	{
		return "Previous";
	}

	public function getNextCopy(): string
	{
		return "Next";
	}
}
