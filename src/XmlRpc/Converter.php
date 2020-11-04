<?php

declare(strict_types=1);

namespace Milo\XmlRpc;

use DOMDocument;


/**
 * Converter between XML source and Milo\XmlRpc\IMethod classes.
 */
class Converter
{
	use Strict;

	/** @var bool  indent exported XML (more or less, debug purpose only) */
	public $indentXmlOutput = false;

	/** @var Coder */
	private $coder;


	public function __construct(Coder $coder = null)
	{
		$this->coder = $coder ?: new Coder;
	}


	public function getCoder(): Coder
	{
		return $this->coder;
	}


	/**
	 * Creates IMethod object from XML.
	 *
	 * @param  string $xml  XML source
	 * @param  int $libXmlOptions  LibXML options
	 * @return MethodCall|MethodResponse|MethodFaultResponse
	 * @throws MalformedXmlException
	 * @throws NotValidXmlException
	 */
	public function fromXml(string $xml, int $libXmlOptions = 0)
	{
		$doc = $this->createDom();

		Helpers::handleXmlErrors();
		try {
			if (@!$doc->loadXML($xml, $libXmlOptions | LIBXML_NOBLANKS)) {  # @ - E_WARNING on empty XML string
				throw new MalformedXmlException('XML source loading failed.', $xml, Helpers::fetchXmlErrors());
			}
		} catch (\ValueError $e) { # Since PHP 8.0
			throw new MalformedXmlException('XML source loading failed.', $xml, $e);
		}

		if (!$doc->relaxNGValidate(__DIR__ . DIRECTORY_SEPARATOR . 'xml-rpc.rng')) {
			throw new NotValidXmlException('XML source is not valid to XML-RPC schema.', $xml, Helpers::fetchXmlErrors());
		}
		Helpers::restoreErrorHandling();

		if ($doc->documentElement->nodeName === 'methodCall') {
			return $this->createMethodCall($doc);

		} elseif ($doc->documentElement->nodeName === 'methodResponse') {
			return $doc->documentElement->firstChild->nodeName === 'fault'
				? $this->createMethodFaultResponse($doc)
				: $this->createMethodResponse($doc);
		}

		throw new \LogicException("Broken XML-RPC schema. Should not be there.");
	}


	/**
	 * Creates XML from IMethod object.
	 *
	 * @throws NotValidXmlException
	 */
	public function toXml(IMethod $method): string
	{
		$doc = $this->createDom();
		$method->toXml($doc, $this->coder);

		$doc->formatOutput = (bool) $this->indentXmlOutput;
		$xml = $doc->saveXML($doc->documentElement);

		Helpers::handleXmlErrors();
		if (!$doc->relaxNGValidate(__DIR__ . DIRECTORY_SEPARATOR . 'xml-rpc.rng')) {
			throw new NotValidXmlException('Being stored XML is not valid to XML-RPC schema.', $xml, Helpers::fetchXmlErrors());
		}
		Helpers::restoreErrorHandling();

		return $xml;
	}


	protected function createDom(): DOMDocument
	{
		$doc = new DOMDocument;
		$doc->preserveWhiteSpace = false;
		return $doc;
	}


	protected function createMethodCall(DOMDocument $doc): MethodCall
	{
		$name = $doc->getElementsByTagName('methodName')->item(0)->textContent;
		$parameters = [];
		foreach ($doc->getElementsByTagName('param') as $node) {
			$parameters[] = $this->coder->decodeValueNode($node->firstChild);
		}

		return new MethodCall($name, $parameters);
	}


	protected function createMethodResponse(DOMDocument $doc): MethodResponse
	{
		return new MethodResponse(
			$this->coder->decodeValueNode($doc->getElementsByTagName('value')->item(0))
		);
	}


	protected function createMethodFaultResponse(DOMDocument $doc): MethodFaultResponse
	{
		$struct = (object) $this->coder->decodeValueNode($doc->getElementsByTagName('value')->item(0));
		return new MethodFaultResponse($struct->faultString, $struct->faultCode);
	}
}
