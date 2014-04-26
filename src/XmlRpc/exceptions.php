<?php

namespace Milo\XmlRpc;


/**
 * XML source is somehow wrong.
 */
abstract class BadXmlException extends \RuntimeException
{
	/** @var string */
	private $xml;


	public function __construct($message = '', $xml = '', \Exception $previous = NULL)
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
 * Envelope for LibXMLError. For exceptions chaining purpose only.
 */
class LibXmlErrorException extends \ErrorException
{
	/** @var int */
	private $column;


	final public function __construct(\LibXmlError $error, self $previous = NULL)
	{
		parent::__construct(trim($error->message), $error->code, $error->level, $error->file, $error->line, $previous);
		$this->column = $error->column;
	}


	/**
	 * @return int
	 */
	final public function getColumn()
	{
		return $this->column;
	}

}


/**
 * Thrown on ValueValidator::validate() fail.
 */
class InvalidValueException extends \RuntimeException
{
}


/**
 * Thrown when invalid schema passed to ValueValidator::validate().
 */
class InvalidSchemaException extends \LogicException
{
}
