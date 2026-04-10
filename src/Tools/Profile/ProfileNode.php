<?php

namespace Katu\Tools\Profile;

use Twig\Compiler;
use Twig\Node\Node;

/**
 * @internal
 */
final class ProfileNode extends Node
{
	public function __construct(Node $name, Node $body, int $lineno, ?string $tag = null)
	{
		parent::__construct([
			"name" => $name,
			"body" => $body,
		], [], $lineno, $tag);
	}

	public function compile(Compiler $compiler)
	{
		$var = $compiler->getVarName();
		$compiler
			->addDebugInfo($this)
			->write(sprintf("\$%s = microtime(true);\n", $var))
			->write("try {\n")
			->indent()
			->subcompile($this->getNode("body"))
			->outdent()
			->write("} finally {\n")
			->indent()
			->write("\\Katu\\Tools\\Profile\\ProfilerContext::addMeasuredSegment(")
			->subcompile($this->getNode("name"))
			->raw(sprintf(", microtime(true) - \$%s);\n", $var))
			->outdent()
			->write("}\n");
	}
}
