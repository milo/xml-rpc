<?php

declare(strict_types=1);

namespace Milo\XmlRpc;


/**
 * Undefined member access check. Stolen from Nette Framework (https://nette.org).
 */
trait Strict
{
	/**
	 * @throws LogicException
	 */
	public function & __get(string $name)
	{
		throw new LogicException('Cannot read an undeclared property ' . get_class($this) . '::$' . $name . '.');
	}


	/**
	 * @throws LogicException
	 */
	public function __set(string $name, $value)
	{
		throw new LogicException('Cannot write to an undeclared property ' . get_class($this) . '::$' . $name . '.');
	}
}
