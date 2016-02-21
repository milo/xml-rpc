<?php

namespace Milo\XmlRpc;

use DOMDocument;


/**
 * Converter between XML source and Milo\XmlRpc\IMethod classes.
 *
 * @author  Miloslav Hůla (https://github.com/milo)
 */
class Converter
{
	use Strict;

	/** @var bool  indent exported XML (more or less, debug purpose only) */
	public $indentXmlOutput = FALSE;

	/** @var Coder */
	private $coder;


	public function __construct(Coder $coder = NULL)
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
	 * @param  string  XML source
	 * @param  int  LibXML options
	 * @return MethodCall|MethodResponse|MethodFaultResponse
	 *
	 * @throws MalformedXmlException
	 * @throws NotValidXmlException
	 */
	public function fromXml($xml, $libXmlOptions = 0)
	{
		$doc = $this->createDom();

		Helpers::handleXmlErrors();
		if (@!$doc->loadXML($xml, $libXmlOptions | LIBXML_NOBLANKS)) {  // @ - E_WARNING on empty XML string
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
	 * @return string
	 *
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
		$doc->preserveWhiteSpace = FALSE;
		return $doc;
	}


	/**
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
	 * @return MethodResponse
	 */
	protected function createMethodResponse(DOMDocument $doc)
	{
		$returnValue = $this->coder->decodeValueNode($doc->getElementsByTagName('value')->item(0));
		return new MethodResponse($returnValue);
	}


	/**
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
