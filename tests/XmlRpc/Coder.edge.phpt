<?php

/**
 * Test: Coder's edge cases
 *
 * @author  Miloslav Hůla
 */

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';



class Mock extends Milo\XmlRpc\Coder
{
	public function encodeDouble($var)
	{
		return parent::encodeDouble($var);
	}

	public function decodeDateTime($text)
	{
		return parent::decodeDateTime($text);
	}
}



$coder = new Mock;

Assert::exception(function () use ($coder) {
	$doc = new DOMDocument;
	$doc->loadXML('<value><invlid/></value>');
	$coder->decodeValueNode($doc->firstChild);
}, 'Milo\XmlRpc\CoderException', 'Converting <invlid> node is not supported.');


Assert::same('1', $coder->encodeDouble(1.0));
Assert::same('4.1E-6', (string) 4.1E-6);
Assert::same('0.000004', $coder->encodeDouble(4.1E-6));


Assert::exception(function () use ($coder) {
	$coder->decodeDateTime('Invalid');
}, 'Milo\XmlRpc\NotValidXmlException', "Inappropriate format of datetime 'Invalid'.");
