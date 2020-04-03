<?php

declare(strict_types=1);

/**
 * Test: Coder's edge cases
 */

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';



class Mock extends Milo\XmlRpc\Coder
{
	public function encodeDouble($var): string
	{
		return parent::encodeDouble($var);
	}

	public function decodeDateTime(string $text): \DateTime
	{
		return parent::decodeDateTime($text);
	}
}



$coder = new Mock;

Assert::exception(function () use ($coder) {
	$doc = new DOMDocument;
	$doc->loadXML('<value><invlid/></value>');
	$coder->decodeValueNode($doc->firstChild);
}, Milo\XmlRpc\CoderException::class, 'Converting <invlid> node is not supported.');


Assert::same('1', $coder->encodeDouble(1.0));
Assert::same('4.1E-6', (string) 4.1E-6);
Assert::same('0.000004', $coder->encodeDouble(4.1E-6));


Assert::exception(function () use ($coder) {
	$coder->decodeDateTime('Invalid');
}, Milo\XmlRpc\NotValidXmlException::class, "Inappropriate format of datetime 'Invalid'.");
