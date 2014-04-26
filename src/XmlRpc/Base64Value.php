<?php

namespace Milo\XmlRpc;


/**
 * Value will be encoded as <value><base64>.
 *
 * @author Miloslav Hůla (https://github.com/milo)
 */
class Base64Value extends Sanity
{
	/** @var string */
	private $value;


	/**
	 * @var string
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
