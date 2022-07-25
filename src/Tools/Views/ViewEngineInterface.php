<?php

namespace Katu\Tools\Views;

use Psr\Http\Message\StreamInterface;

interface ViewEngineInterface
{
	public function render(string $template, array $data = []): StreamInterface;
}
