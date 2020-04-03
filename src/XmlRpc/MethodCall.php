<?php

declare(strict_types=1);

namespace Milo\XmlRpc;

use DOMDocument;


/**
 * XML-RPC <methodCall> representation.
 */
class MethodCall implements IMethod
{
	use Strict;

	/** @var string */
	private $name;

	/** @var array */
	private $parameters;


	public function __construct(string $name, array $parameters = [])
	{
		$this->name = $name;
		$this->parameters = $parameters;
	}


	/**
	 * Returns method name.
	 */
	public function getName(): string
	{
		return $this->name;
	}


	/**
	 * Returns method parameters.
	 */
	public function getParameters(): array
	{
		return $this->parameters;
	}


	/**
	 * Returns method parameter count.
	 */
	public function getParameterCount(): int
	{
		return count($this->parameters);
	}


	/**
	 * Fills DOM by method name and parameters.
	 */
	public function toXml(DOMDocument $doc, Coder $coder): void
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
