<?php

declare(strict_types=1);

namespace Milo\XmlRpc;


/**
 * Value encoded as <base64>.
 */
class Base64Value
{
	use Strict;

	/** @var string */
	private $value;


	public function __construct(string $value)
	{
		$this->value = $value;
	}


	public function getValue(): string
	{
		return $this->value;
	}
}
