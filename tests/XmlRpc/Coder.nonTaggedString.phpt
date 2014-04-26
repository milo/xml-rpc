<?php

/**
 * Test: Decoding of non-tagged string
 *
 * @author  Miloslav Hůla
 */

require __DIR__ . '/../bootstrap.php';



$coder = new Milo\XmlRpc\Coder;
$doc = new DOMDocument;
$doc->preserveWhiteSpace = FALSE;
$doc->load(__DIR__ . '/files/Coder.nonTaggedString.xml');



$var = $coder->decodeValueNode($doc->documentElement);
Assert::equal(array(
	' non-tagged string ',
	' ',
	'',
), $var);
