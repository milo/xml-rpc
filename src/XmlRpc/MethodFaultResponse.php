<?php

namespace Milo\XmlRpc;

use DOMDocument;


/**
 * Fault XML-RPC <methodResponse> representation.
 *
 * @author Miloslav Hůla (https://github.com/milo)
 */
class MethodFaultResponse implements IMethod, IMethodResponse
{
	use Strict;

	/** @var string  error message */
	private $message;

	/** @var int  error code */
	private $code;


	/**
	 * @param  string  faultString
	 * @param  string  faultCode
	 */
	public function __construct($message, $code)
	{
		$this->message = (string) $message;
		$this->code = (int) $code;
	}


	/**
	 * Returns faultString.
	 *
	 * @return string
	 */
	public function getMessage()
	{
		return $this->message;
	}


	/**
	 * Returns faultCode.
	 *
	 * @return int
	 */
	public function getCode()
	{
		return $this->code;
	}


	/**
	 * Creates fault response from Exception.
	 *
	 * @return self
	 */
	public static function fromException(\Exception $e)
	{
		return new static($e->getMessage(), $e->getCode());
	}


	/**
	 * Fills DOM by error message and code.
	 */
	public function toXml(DOMDocument $doc, Coder $coder)
	{
		$coder = clone $coder;
		$coder->encodeBinaryAsBase64 = FALSE;

		$struct = (object) [
			'faultCode' => $this->code,
			'faultString' => $this->message,
		];

		$methodResponseNode = $doc->appendChild($doc->createElement('methodResponse'));
		$methodResponseNode
			->appendChild($doc->createElement('fault'))
				->appendChild($coder->encodeValueNode($doc, $struct));
	}

}
