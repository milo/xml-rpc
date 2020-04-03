<?php

declare(strict_types=1);

namespace Milo\XmlRpc;


/**
 * Helpers.
 */
class Helpers
{
	/** @var array */
	private static $errorHandling = [];


	/**
	 * @throws LogicException
	 */
	final public function __construct()
	{
		throw new LogicException('Class ' . __CLASS__ . ' is static and cannot be instantized.');
	}


	/**
	 * Enable LibXML error handling.
	 */
	public static function handleXmlErrors(): void
	{
		self::$errorHandling[] = libxml_use_internal_errors(true);
	}


	/**
	 * Fetch all LibXML errors and converts them into LibXmlErrorException chain.
	 */
	public static function fetchXmlErrors(bool $restoreHandling = true): ?LibXmlErrorException
	{
		$e = null;
		foreach (array_reverse(libxml_get_errors()) as $error) {
			$e = new LibXmlErrorException($error, $e);
		}
		libxml_clear_errors();

		if ($restoreHandling) {
			self::restoreErrorHandling();
		}

		return $e;
	}


	/**
	 * Restore LibXML errors handling.
	 */
	public static function restoreErrorHandling(): void
	{
		libxml_use_internal_errors(array_pop(self::$errorHandling));
	}
}
