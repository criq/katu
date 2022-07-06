<?php

namespace Katu\Tools\Views;

interface ViewEngineInterface
{
	public function render(string $template, array $data = []): string;
}
