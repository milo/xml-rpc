<?php

/**
 * Test: <dateTime.iso8601> conversion
 */

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$xml = file_get_contents(__DIR__ . '/files/Coder.dateTime.xml');
assertValueElement($xml);


$coder = new Milo\XmlRpc\Coder;
$doc = new DOMDocument;
$doc->preserveWhiteSpace = false;
$doc->loadXML($xml);


$var = $coder->decodeValueNode($doc->documentElement);
Assert::equal([
	DateTime::createFromFormat('Y-m-d H:i:s P', '2000-01-20 12:30:00 +02:30'),
	DateTime::createFromFormat('Y-m-d H:i:s P', '2000-01-20 12:30:00 -02:30'),
	DateTime::createFromFormat('Y-m-d H:i:s', '2000-01-20 12:30:00'),

	DateTime::createFromFormat('Y-m-d H:i:s P', '2000-01-20 12:30:00 +02:30'),
	DateTime::createFromFormat('Y-m-d H:i:s P', '2000-01-20 12:30:00 -02:30'),
	DateTime::createFromFormat('Y-m-d H:i:s', '2000-01-20 12:30:00'),

	DateTime::createFromFormat('Y-m-d H:i:s P', '2000-01-20 12:30:00 Z'),

	DateTime::createFromFormat('Y-m-d H:i:s P', '2000-01-20 12:30:00+0200'),
	DateTime::createFromFormat('Y-m-d H:i:s P', '2000-01-20 12:30:00-0200'),
], $var);
