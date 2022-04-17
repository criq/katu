<?php

namespace Katu\Interfaces;

interface ViewEngine
{
	public function render(string $template, array $data = []): string;
}
