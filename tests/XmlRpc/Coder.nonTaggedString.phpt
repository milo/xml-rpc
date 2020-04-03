<?php

/**
 * Test: Decoding of non-tagged string
 */

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$xml = file_get_contents(__DIR__ . '/files/Coder.nonTaggedString.xml');
assertValueElement($xml);


$coder = new Milo\XmlRpc\Coder;
$doc = new DOMDocument;
$doc->preserveWhiteSpace = false;
$doc->loadXML($xml);


$var = $coder->decodeValueNode($doc->documentElement);
Assert::equal([
	' non-tagged string ',
	' ',
	'',
], $var);
