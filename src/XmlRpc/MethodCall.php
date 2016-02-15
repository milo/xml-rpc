<?php

namespace Milo\XmlRpc;

use DOMDocument;


/**
 * XML-RPC <methodCall> representation.
 *
 * @author Miloslav Hůla (https://github.com/milo)
 */
class MethodCall implements IMethod
{
	use Strict;

	/** @var string */
	private $name;

	/** @var array */
	private $parameters;


	/**
	 * @param  string  method name
	 * @param  array  method parameters
	 */
	public function __construct($name, array $parameters = [])
	{
		$this->name = (string) $name;
		$this->parameters = $parameters;
	}


	/**
	 * Returns method name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * Returns method parameters.
	 *
	 * @return array
	 */
	public function getParameters()
	{
		return $this->parameters;
	}


	/**
	 * Returns method parameter count.
	 *
	 * @return int
	 */
	public function getParameterCount()
	{
		return count($this->parameters);
	}


	/**
	 * Fills DOM by method name and parameters.
	 */
	public function toXml(DOMDocument $doc, Coder $coder)
	{
		$methodCallNode = $doc->appendChild($doc->createElement('methodCall'));
		$methodCallNode
			->appendChild($doc->createElement('methodName'))
				->appendChild($doc->createTextNode($this->name));

		if (count($this->parameters)) {
			$paramsNode = $methodCallNode->appendChild($doc->createElement('params'));
			foreach ($this->parameters as $var) {
				$paramsNode
					->appendChild($doc->createElement('param'))
						->appendChild($coder->encodeValueNode($doc, $var));
			}
		}
	}

}
