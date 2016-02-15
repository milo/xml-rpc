<?php

/**
 * Test: <dateTime.iso8601> conversion
 *
 * @author  Miloslav Hůla
 */

require __DIR__ . '/../bootstrap.php';


$coder = new Milo\XmlRpc\Coder;
$doc = new DOMDocument;
$doc->preserveWhiteSpace = FALSE;
$doc->load(__DIR__ . '/files/Coder.dateTime.xml');



$var = $coder->decodeValueNode($doc->documentElement);
Assert::equal([
	DateTime::createFromFormat('Y-m-d H:i:s P', '2000-01-20 12:30:00 +02:30'),
	DateTime::createFromFormat('Y-m-d H:i:s', '2000-01-20 12:30:00'),
	DateTime::createFromFormat('Y-m-d H:i:s P', '2000-01-20 12:30:00 +02:30'),
	DateTime::createFromFormat('Y-m-d H:i:s', '2000-01-20 12:30:00'),
], $var);
