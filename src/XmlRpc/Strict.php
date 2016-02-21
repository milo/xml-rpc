<?php

namespace Milo\XmlRpc;


/**
 * Undefined member access check. Stolen from Nette Framework (https://nette.org).
 */
trait Strict
{
	/**
	 * @param  string $name
	 * @throws LogicException
	 */
	public function & __get($name)
	{
		throw new LogicException('Cannot read an undeclared property ' . get_class($this) . '::$' . $name . '.');
	}


	/**
	 * @param  string $name
	 * @param  mixed $value
	 * @throws LogicException
	 */
	public function __set($name, $value)
	{
		throw new LogicException('Cannot write to an undeclared property ' . get_class($this) . '::$' . $name . '.');
	}

}
