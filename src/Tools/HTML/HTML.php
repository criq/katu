<?php

namespace Katu\Tools\HTML;

use Psr\Http\Message\StreamInterface;

class HTML
{
	protected $html;

	public function __construct(string $html)
	{
		$this->setHTML($html);
	}

	public function __toString(): string
	{
		return $this->getHTML();
	}

	public function setHTML(string $html): HTML
	{
		$this->html = $html;

		return $this;
	}

	public function getHTML(): string
	{
		return $this->html;
	}

	public function getStream(): StreamInterface
	{
		return \GuzzleHttp\Psr7\Utils::streamFor($this->getHTML());
	}

	public function getTwigMarkup(): \Twig\Markup
	{
		return new \Twig\Markup($this->getHTML(), "UTF-8");
	}
}
