<?php

namespace Milo\XmlRpc;


/**
 * Value encoded as <base64>.
 */
class Base64Value
{
	use Strict;

	/** @var string */
	private $value;


	/**
	 * @param  string $value
	 */
	public function __construct($value)
	{
		$this->value = (string) $value;
	}


	/**
	 * @return string
	 */
	public function getValue()
	{
		return $this->value;
	}
}
