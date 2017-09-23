<?php

namespace Milo\XmlRpc;

use DOMDocument;
use DOMElement;
use DOMText;


/**
 * Coder between XML-RPC types and PHP types.
 *   - decodes XML to PHP
 *   - encodes PHP to XML
 *
 * @see    http://xmlrpc.scripting.com/spec
 */
class Coder
{
	use Strict;

	/** @var bool  convert <struct> as stdClass object; array otherwise */
	public $decodeStructAsObject = FALSE;

	/** @var bool  encode binary strings as <base64> */
	public $encodeBinaryAsBase64 = TRUE;

	/** @var bool  remember datetime format used for decoding an use it for encoding */
	public $rememberDatetimeFormat = TRUE;

	/** @var int  encode depth limit */
	public $maxEncodeDepth = 20;

	/** @var string  datetime format used lastly for decoding */
	private $lastDatetimeFormat;


	/**
	 * Is string binary?
	 *
	 * @param  string $str
	 * @return bool
	 */
	public static function isBinary($str)
	{
		return (bool) preg_match('#[\x00-\x08\x0B\x0C\x0E-\x1F]#', $str);
	}


	/**
	 * Removes all characters which cannot be in XML.
	 *
	 * @param  string $str
	 * @return string
	 */
	public static function sanitizeXml($str)
	{
		return preg_replace('#[\x00-\x08\x0B\x0C\x0E-\x1F]+#', '', (string) $str);
	}


	/**
	 * Converts <value> node to PHP variable.
	 *
	 * @param  DOMElement $node  <value> node
	 * @return mixed
	 * @throws CoderException
	 */
	public function decodeValueNode(DOMElement $node)
	{
		if (!$node->hasChildNodes()) {
			return '';
		}

		$textContent = $node->firstChild->textContent;
		if ($node->firstChild instanceof DOMText) {
			return $textContent;
		}

		switch ($node->firstChild->nodeName) {
			case 'string':
				return $textContent;

			case 'i1':
			case 'i2':
			case 'i4':
			case 'i8':
			case 'int':
				return (int) $textContent;  # presume PHP has 32-bit integer at least

			case 'boolean':
				return (bool) $textContent;

			case 'double':
				return $this->decodeDouble($textContent);

			case 'dateTime.iso8601':
				return $this->decodeDateTime($textContent);

			case 'base64':
				return base64_decode($textContent);

			case 'nil':
				return NULL;

			case 'struct':
				return $this->decodeStructNode($node->firstChild);

			case 'array':
				return $this->decodeArrayNode($node->firstChild);
		}

		throw new CoderException("Converting <{$node->firstChild->nodeName}> node is not supported.");
	}


	/**
	 * Converts PHP variable to <value> node.
	 *
	 * @param  DOMDocument $doc
	 * @param  mixed $var
	 * @param  int $level
	 * @return DOMElement  <value> node
	 * @throws CoderException
	 */
	public function encodeValueNode(DOMDocument $doc, $var, $level = 1)
	{
		if ($level > (int) $this->maxEncodeDepth) {
			throw new CoderException('Nesting level too deep or recursive dependency. Try to increase ' . __CLASS__ . '::$maxEncodeDepth');

		} elseif ($var instanceof IValueConvertible) {
			return $this->encodeValueNode($doc, $var->getXmlRpcValue(), $level + 1);

		} elseif ($var === NULL) {
			$node = $doc->createElement('nil');

		} elseif (is_bool($var)) {
			$node = $doc->createElement('boolean', $var ? '1' : '0');

		} elseif (is_string($var)) {
			if ($this->encodeBinaryAsBase64 && static::isBinary($var)) {
				$node = $doc->createElement('base64', base64_encode($var));

			} else {
				$node = $doc->createElement('string');
				if (strlen($var)) {
					$node->appendChild($doc->createTextNode(self::sanitizeXml($var)));
				}
			}

		} elseif (is_int($var) && $var >= -2147483648 && $var <= 2147483647) {  # XML-RPC specifies 32-bit integer only
			$node = $doc->createElement('int', number_format($var, 0, '.', ''));

		} elseif (is_float($var) || is_int($var)) {
			$node = $doc->createElement('double', $this->encodeDouble($var));

		} elseif ($var instanceof \DateTime) {
			$node = $doc->createElement('dateTime.iso8601', $this->encodeDateTime($var));

		} elseif (is_array($var)) {
			if (($keys = array_keys($var)) === array_keys($keys)) {
				$node = $this->encodeArrayNode($doc, $var, $level);
			} else {
				$node = $this->encodeStructNode($doc, $var, $level);
			}

		} elseif ($var instanceof Base64Value) {
			$node = strlen($var = $var->getValue())
				? $node = $doc->createElement('base64', base64_encode($var))
				: $node = $doc->createElement('base64');

		} elseif (is_object($var)) {
			$node = $this->encodeStructNode($doc, $var, $level);


		} elseif (is_resource($var)) {
			$node = $this->encodeResource($doc, $var);

		} else {
			throw new CoderException("Type '" . gettype($var) . "' is not convertible to <value>.");
		}

		$valueNode = $doc->createElement('value');
		$valueNode->appendChild($node);

		return $valueNode;
	}


	/**
	 * @param  string $text
	 * @return float|string
	 */
	protected function decodeDouble($text)
	{
		$float = (float) $text;

		$decimals = 0;
		if (($dot = strpos($text, '.')) !== FALSE) {
			$decimals = strlen($text) - $dot - 1;
		}

		return $text === number_format($float, $decimals, '.', '') ? $float : $text;
	}


	/**
	 * @param  string $var
	 * @return string
	 */
	protected function encodeDouble($var)
	{
		return preg_match('#E-([0-9]+)#', (string) $var, $m)
			? number_format($var, (int) $m[1], '.', '')
			: (string) $var;
	}


	/**
	 * @param  string $text
	 * @return \DateTime
	 * @throws NotValidXmlException  when datetime is in inappropriate format
	 */
	protected function decodeDateTime($text)
	{
		static $formats = [
			'Y-m-d\TH:i:sP',
			'Y-m-d\TH:i:s',
			'Ymd\TH:i:sP',
			'Ymd\TH:i:s',
		];

		foreach ($formats as $format) {
			if (($date = \DateTime::createFromFormat($format, $text)) !== FALSE) {
				if ($this->rememberDatetimeFormat) {
					$this->lastDatetimeFormat = $format;
				}
				return $date;
			}
		}

		throw new NotValidXmlException("Inappropriate format of datetime '$text'.");
	}


	/**
	 * @param  \DateTime $var
	 * @return string
	 */
	protected function encodeDateTime(\DateTime $var)
	{
		if ($this->rememberDatetimeFormat && $this->lastDatetimeFormat !== NULL) {
			return $var->format($this->lastDatetimeFormat);
		}

		return $var->format('Y-m-d\TH:i:sP');
	}


	/**
	 * @param  DOMElement $node
	 * @return \stdClass|array
	 */
	protected function decodeStructNode(DOMElement $node)
	{
		$struct = [];
		foreach ($node->childNodes as $member) {
			$name = $member->childNodes->item(0)->textContent;
			$value = $this->decodeValueNode($member->childNodes->item(1));

			$struct[$name] = $value;
		}

		return $this->decodeStructAsObject
			? (object) $struct
			: $struct;
	}


	/**
	 * @param  DOMDocument $doc
	 * @param  mixed $var
	 * @param  int $level
	 * @return DOMElement  <struct> node
	 */
	protected function encodeStructNode(DOMDocument $doc, $var, $level)
	{
		$structNode = $doc->createElement('struct');
		foreach ($var as $name => $item) {
			$memberNode = $structNode->appendChild($doc->createElement('member'));
			$memberNode
				->appendChild($doc->createElement('name'))
					->appendChild($doc->createTextNode(self::sanitizeXml($name)));
			$memberNode
				->appendChild($this->encodeValueNode($doc, $item, $level + 1));
		}

		return $structNode;
	}


	/**
	 * @param  DOMElement $node
	 * @return array
	 */
	protected function decodeArrayNode(DOMElement $node)
	{
		$return = [];
		if ($node->firstChild && $node->firstChild->nodeName === 'data') {
			foreach ($node->firstChild->childNodes as $value) {
				if ($value->nodeName === 'value') {
					$return[] = $this->decodeValueNode($value);
				}
			}
		}

		return $return;
	}


	/**
	 * @param  DOMDocument $doc
	 * @param  array $var
	 * @param  int $level
	 * @return DOMElement  <array> node
	 */
	protected function encodeArrayNode(DOMDocument $doc, array $var, $level)
	{
		$arrayNode = $doc->createElement('array');
		$dataNode = $arrayNode->appendChild($doc->createElement('data'));
		foreach ($var as $item) {
			$dataNode
				->appendChild($this->encodeValueNode($doc, $item, $level + 1));
		}

		return $arrayNode;
	}


	/**
	 * Converts resource content to DOMElement.
	 *
	 * @param  DOMDocument $doc
	 * @param  resource $resource
	 * @return DOMElement
	 * @throws CoderException  when conversion is not implemented
	 */
	protected function encodeResource(DOMDocument $doc, $resource)
	{
		$type = get_resource_type($resource);
		if ($type !== 'stream') {
			throw new CoderException("Conversion of '$type' resource is not implemented.");
		}

		$data = base64_encode(stream_get_contents($resource));
		return strlen($data)
			? $doc->createElement('base64', $data)
			: $doc->createElement('base64');
	}

}
