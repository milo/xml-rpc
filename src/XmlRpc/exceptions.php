<?php

declare(strict_types=1);

namespace Milo\XmlRpc;


/**
 * All exceptions marker.
 */
interface IException
{
}


/**
 * Wrong algorithm. API is used in a wrong way. Application code should be changed.
 */
class LogicException extends \LogicException implements IException
{
}


/**
 * Thrown when invalid schema passed to ValueValidator::validate().
 */
class InvalidSchemaException extends LogicException
{
}


/**
 * Unpredictable situation occurred.
 */
abstract class RuntimeException extends \RuntimeException implements IException
{
}


/**
 * XML source is somehow wrong.
 */
abstract class BadXmlException extends RuntimeException
{
	/** @var string */
	private $xml;


	public function __construct(string $message = '', string $xml = '', \Exception $previous = null)
	{
		parent::__construct($message, 0, $previous);
		$this->xml = $xml;
	}


	/**
	 * @return string
	 */
	final public function getXml()
	{
		return $this->xml;
	}
}


/**
 * XML source is malformed (syntax error).
 */
class MalformedXmlException extends BadXmlException
{
}


/**
 * XML content is not valid (does not match to schema).
 */
class NotValidXmlException extends BadXmlException
{
}


/**
 * Thrown on ValueValidator::validate() fail.
 */
class InvalidValueException extends RuntimeException
{
}


/**
 * MethodCall processing somehow failed. It will results into fault method response.
 * The exception message and code will be sent to the client.
 */
class FaultResponseException extends RuntimeException
{
}


/**
 * Encoding to XML or decoding from XML failed.
 */
class CoderException extends RuntimeException
{
}


/**
 * Envelope for LibXMLError. For exceptions chaining purpose only.
 */
class LibXmlErrorException extends \ErrorException implements IException
{
	/** @var int */
	private $column;


	final public function __construct(\LibXmlError $error, self $previous = null)
	{
		parent::__construct(trim($error->message), $error->code, $error->level, $error->file, $error->line, $previous);
		$this->column = $error->column;
	}


	final public function getColumn(): int
	{
		return $this->column;
	}
}
