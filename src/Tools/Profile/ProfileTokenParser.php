<?php

namespace Katu\Tools\Profile;

use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

/**
 * {% profile 'twig.block_name' %} ... {% endprofile %}
 *
 * @internal
 */
final class ProfileTokenParser extends AbstractTokenParser
{
	public function getTag()
	{
		return "profile";
	}

	public function parse(Token $token)
	{
		$lineno = $token->getLine();
		$name = $this->parser->getExpressionParser()->parseExpression();
		$stream = $this->parser->getStream();
		$stream->expect(Token::BLOCK_END_TYPE);
		$body = $this->parser->subparse([$this, "decideProfileEnd"], true);
		$stream->expect(Token::BLOCK_END_TYPE);

		return new ProfileNode($name, $body, $lineno, $this->getTag());
	}

	public function decideProfileEnd(Token $token)
	{
		return $token->test("endprofile");
	}
}
