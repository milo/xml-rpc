<?php

/**
 * Test: Conversion of base XML-RPC types
 */

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';



$coder = new Milo\XmlRpc\Coder;
$doc = new DOMDocument;
$doc->preserveWhiteSpace = FALSE;
$doc->load(__DIR__ . '/files/Coder.basic.xml');



# From XML to PHP
$var = $coder->decodeValueNode($doc->documentElement);
Assert::equal([
	'string',
	'',
	123,
	-65535,
	FALSE,
	TRUE,
	-1.256,
	DateTime::createFromFormat('Y-m-d H:i:s P', '2000-01-20 12:30:00 +00:00'),
	NULL,
	['a' => NULL, 'b' => 'c'],
	['1', 1],
], $var);



# From PHP to XML
$doc = new DOMDocument;
$doc->formatOutput = TRUE;
$node = $coder->encodeValueNode($doc, $var);
Assert::same(
	file_get_contents(__DIR__ . '/files/Coder.basic.xml'),
	$doc->saveXML($node)
);
