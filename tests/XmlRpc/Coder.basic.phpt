<?php

declare(strict_types=1);

/**
 * Test: Conversion of base XML-RPC types
 */

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$xml = file_get_contents(__DIR__ . '/files/Coder.basic.xml');
assertValueElement($xml);


$coder = new Milo\XmlRpc\Coder;
$doc = new DOMDocument;
$doc->preserveWhiteSpace = false;
$doc->loadXML($xml);



# From XML to PHP
$var = $coder->decodeValueNode($doc->documentElement);
Assert::equal([
	'string',
	'',
	123,
	-65535,
	false,
	true,
	-1.256,
	DateTime::createFromFormat('Y-m-d H:i:s P', '2000-01-20 12:30:00 +00:00'),
	null,
	['a' => null, 'b' => 'c'],
	['1', 1],
	[],
], $var);



# From PHP to XML
$doc = new DOMDocument;
$doc->formatOutput = true;
$node = $coder->encodeValueNode($doc, $var);
Assert::same(
	$xml,
	$doc->saveXML($node)
);
