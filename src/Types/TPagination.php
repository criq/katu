<?php

namespace Katu\Types;

class TPagination
{
	const PAGINATION_ALL_PAGES_LIMIT = 10;
	const PAGINATION_ENDS_OFFSET = 0;
	const PAGINATION_CURRENT_OFFSET = 3;

	public $page;
	public $pages;
	public $perPage;
	public $total;

	public function __construct(int $total, int $perPage, int $page)
	{
		if ((int)$total < 0) {
			throw new \Exception("Invalid total.");
		}
		if ((int)$perPage < 1) {
			throw new \Exception("Invalid per page.");
		}
		if ((int)$page < 1) {
			throw new \Exception("Invalid page.");
		}

		$this->total = (int)$total;
		$this->perPage = (int)$perPage;
		$this->page = (int)$page;
		$this->pages = (int)ceil($total / $perPage);
	}

	public static function getAppPageIdent()
	{
		return \Katu\Config\Config::get('app', 'pagination', 'pageIdent');
	}

	public static function getAppPerPage()
	{
		return \Katu\Config\Config::get('app', 'pagination', 'perPage');
	}

	public static function getRequestPageExpression(\Slim\Http\Request $request, int $perPage = null)
	{
		$params = \Katu\Tools\Routing\URL::getCurrent()->getQueryParams();

		return new \Sexy\Page(static::getPageFromRequest($params), is_null($perPage) ? static::getAppPerPage() : $perPage);
	}

	public static function getPageFromRequest($params)
	{
		if (!($params[static::getAppPageIdent()] ?? null)) {
			return 1;
		}

		if ($params[static::getAppPageIdent()] < 1) {
			return 1;
		}

		return (int)$params[static::getAppPageIdent()];
	}

	public function getMinPage()
	{
		return 1;
	}

	public function getMaxPage()
	{
		return (int) (ceil($this->total / $this->perPage));
	}

	public function getPaginationPages($options = [])
	{
		$options = array_merge([
			'allPagesLimit' => static::PAGINATION_ALL_PAGES_LIMIT,
			'currentOffset' => static::PAGINATION_CURRENT_OFFSET,
			'endsOffset' => static::PAGINATION_ENDS_OFFSET,
		], $options);

		if (!$this->total) {
			return [];
		}

		if ($this->getMaxPage() <= $options['allPagesLimit']) {
			return range($this->getMinPage(), $this->getMaxPage());
		}

		$pages = [];
		$pages = array_merge($pages, range($this->getMinPage(), $this->getMinPage() + $options['endsOffset']));
		$pages = array_merge($pages, range($this->page - $options['currentOffset'], $this->page + $options['currentOffset']));
		$pages = array_merge($pages, range($this->getMaxPage() - $options['endsOffset'], $this->getMaxPage()));

		$pages = array_unique(array_filter($pages, function ($i) {
			return ($i > 0 && $i <= $this->getMaxPage()) ? true : false;
		}));
		natsort($pages);
		$pages = array_values($pages);

		return $pages;
	}
}
