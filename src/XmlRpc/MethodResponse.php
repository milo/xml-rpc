<?php

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
	 *
	 * @param  DOMDocument $doc
	 * @param  Coder $coder
	 * @return void
	 */
	public function toXml(DOMDocument $doc, Coder $coder)
	{
		$methodResponseNode = $doc->appendChild($doc->createElement('methodResponse'));
		$methodResponseNode
			->appendChild($doc->createElement('params'))
				->appendChild($doc->createElement('param'))
					->appendChild($coder->encodeValueNode($doc, $this->returnValue));
	}

}
