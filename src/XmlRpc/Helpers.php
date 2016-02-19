<?php

namespace Milo\XmlRpc;


/**
 * Helpers.
 *
 * @author Miloslav Hůla (https://github.com/milo)
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
	public static function handleXmlErrors()
	{
		self::$errorHandling[] = libxml_use_internal_errors(TRUE);
	}


	/**
	 * Fetch all LibXML errors and converts them into LibXmlErrorException chain.
	 *
	 * @param  bool  restore LibXML errors handling
	 * @return LibXmlErrorException|NULL
	 */
	public static function fetchXmlErrors($restoreHandling = TRUE)
	{
		$e = NULL;
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
	public static function restoreErrorHandling()
	{
		libxml_use_internal_errors(array_pop(self::$errorHandling));
	}

}
