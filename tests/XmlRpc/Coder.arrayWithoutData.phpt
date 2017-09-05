<?php

/**
 * Test: An <array> without <data> workaround.
 */

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$xml = file_get_contents(__DIR__ . '/files/Coder.arrayWithoutData.xml');
assertValueElement($xml);


$coder = new Milo\XmlRpc\Coder;
$doc = new DOMDocument;
$doc->preserveWhiteSpace = FALSE;
$doc->loadXML($xml);

Assert::equal([], $coder->decodeValueNode($doc->documentElement));
