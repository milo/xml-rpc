<?php

declare(strict_types=1);

namespace Milo\XmlRpc;

use DOMDocument;


/**
 * Successful XML-RPC <methodResponse> representation.
 */
class MethodResponse implements IMethod, IMethodResponse
{
	use Strict;

	/** @var mixed  return value */
	private $returnValue;


	/**
	 * @param mixed  <methodResponse> return value
	 */
	public function __construct($returnValue)
	{
		$this->returnValue = $returnValue;
	}


	/**
	 * Returns <methodResponse> return value.
	 *
	 * @return mixed
	 */
	public function getReturnValue()
	{
		return $this->returnValue;
	}


	/**
	 * Fills DOM by return value.
	 */
	public function toXml(DOMDocument $doc, Coder $coder): void
	{
		$methodResponseNode = $doc->appendChild($doc->createElement('methodResponse'));
		$methodResponseNode
			->appendChild($doc->createElement('params'))
				->appendChild($doc->createElement('param'))
					->appendChild($coder->encodeValueNode($doc, $this->returnValue));
	}
}
