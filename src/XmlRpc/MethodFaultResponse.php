<?php

declare(strict_types=1);

namespace Milo\XmlRpc;

use DOMDocument;


/**
 * Fault XML-RPC <methodResponse> representation.
 */
class MethodFaultResponse implements IMethod, IMethodResponse
{
	use Strict;

	/** @var string  error message */
	private $message;

	/** @var int  error code */
	private $code;


	public function __construct(string $message, int $code)
	{
		$this->message = $message;
		$this->code = $code;
	}


	/**
	 * Returns faultString.
	 */
	public function getMessage(): string
	{
		return $this->message;
	}


	/**
	 * Returns faultCode.
	 */
	public function getCode(): int
	{
		return $this->code;
	}


	/**
	 * Creates fault response from Exception.
	 */
	public static function fromException(\Exception $e): self
	{
		return new static($e->getMessage(), $e->getCode());
	}


	/**
	 * Fills DOM by error message and code.
	 */
	public function toXml(DOMDocument $doc, Coder $coder): void
	{
		$coder = clone $coder;
		$coder->encodeBinaryAsBase64 = false;

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
