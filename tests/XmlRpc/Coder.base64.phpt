<?php

/**
 * Test: <base64> conversion
 */

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';



$coder = new Milo\XmlRpc\Coder;

$php = "Binary-\x00-string";
$xml = '<value><base64>' . base64_encode($php) . '</base64></value>';



$doc = new DOMDocument;
$doc->preserveWhiteSpace = false;
$doc->loadXML($xml);

Assert::same($php, $coder->decodeValueNode($doc->documentElement));
Assert::same($xml, $doc->saveXML($coder->encodeValueNode($doc, $php)));



$doc = new DOMDocument;
$doc->preserveWhiteSpace = false;

$coder->encodeBinaryAsBase64 = false;
Assert::same('<value><string>Binary--string</string></value>', $doc->saveXML($coder->encodeValueNode($doc, $php)));



# Binary string detection
$values = [];
for ($i = 0; $i <= 0xFF; $i++) {
	$binary = ($i >= 0x00 && $i <= 0x08) || $i === 0x0B || $i === 0x0C || ($i >= 0x0E && $i <= 0x1F);

	$hex = sprintf('0x%02X', $i);
	$values["$hex:" . ($binary ? 'T' : 'F')] = "$hex:" . ($coder::isBinary(chr($i)) ? 'T' : 'F');
}
Assert::same( 256, count($values) );
Assert::same( array_keys($values), array_values($values) );



# Milo\XmlRpc\Base64Value converting
$base64 = new Milo\XmlRpc\Base64Value('');
Assert::same('<value><base64/></value>', $doc->saveXML($coder->encodeValueNode($doc, $base64)));

$base64 = new Milo\XmlRpc\Base64Value('x');
Assert::same('<value><base64>eA==</base64></value>', $doc->saveXML($coder->encodeValueNode($doc, $base64)));
