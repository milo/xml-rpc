<?php

declare(strict_types=1);

/**
 * Test: Remember last received <dateTime.iso8601> format
 */

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';



$coder = new Milo\XmlRpc\Coder;
$doc = new DOMDocument;
$doc->preserveWhiteSpace = false;

$php = DateTimeImmutable::createFromFormat('Y-m-d H:i:s P', '2000-01-01 15:20:00 +01:00');
$xml = '<value><dateTime.iso8601>20000101T15:20:00+01:00</dateTime.iso8601></value>';


$doc->loadXML($xml);
Assert::equal($php, $coder->decodeValueNode($doc->documentElement));
Assert::equal('<value><dateTime.iso8601>20000101T15:20:00+01:00</dateTime.iso8601></value>', $doc->saveXML($coder->encodeValueNode($doc, $php)));


$coder->rememberDatetimeFormat = false;
$doc->loadXML($xml);
Assert::equal($php, $coder->decodeValueNode($doc->documentElement));
Assert::equal('<value><dateTime.iso8601>2000-01-01T15:20:00+01:00</dateTime.iso8601></value>', $doc->saveXML($coder->encodeValueNode($doc, $php)));
