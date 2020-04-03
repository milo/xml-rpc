<?php

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


	/**
	 * @return Coder
	 */
	public function getCoder()
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
	public function fromXml($xml, $libXmlOptions = 0)
	{
		$doc = $this->createDom();

		Helpers::handleXmlErrors();
		if (@!$doc->loadXML($xml, $libXmlOptions | LIBXML_NOBLANKS)) {  # @ - E_WARNING on empty XML string
			throw new MalformedXmlException('XML source loading failed.', $xml, Helpers::fetchXmlErrors());
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
	 * @param  IMethod $method
	 * @return string
	 * @throws NotValidXmlException
	 */
	public function toXml(IMethod $method)
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


	/**
	 * @return DOMDocument
	 */
	protected function createDom()
	{
		$doc = new DOMDocument;
		$doc->preserveWhiteSpace = false;
		return $doc;
	}


	/**
	 * @param  DOMDocument $doc
	 * @return MethodCall
	 */
	protected function createMethodCall(DOMDocument $doc)
	{
		$name = $doc->getElementsByTagName('methodName')->item(0)->textContent;
		$parameters = [];
		foreach ($doc->getElementsByTagName('param') as $node) {
			$parameters[] = $this->coder->decodeValueNode($node->firstChild);
		}

		return new MethodCall($name, $parameters);
	}


	/**
	 * @param  DOMDocument $doc
	 * @return MethodResponse
	 */
	protected function createMethodResponse(DOMDocument $doc)
	{
		return new MethodResponse(
			$this->coder->decodeValueNode($doc->getElementsByTagName('value')->item(0))
		);
	}


	/**
	 * @param  DOMDocument $doc
	 * @return MethodFaultResponse
	 */
	protected function createMethodFaultResponse(DOMDocument $doc)
	{
		$struct = (object) $this->coder->decodeValueNode($doc->getElementsByTagName('value')->item(0));
		return new MethodFaultResponse($struct->faultString, $struct->faultCode);
	}
}


/** @deprecated */
class Convertor extends Converter
{}
