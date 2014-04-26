<?php

/**
 * Test: Resources validity
 *
 * @author  Miloslav Hůla
 */

require __DIR__ . '/../bootstrap.php';



$rngFile = __DIR__ . '/../../src/XmlRpc/xml-rpc.rng';
Assert::true( is_file($rngFile) );



# Schema is valid Relax NG schema
$doc = new DOMDocument;
$doc->loadXml('<test/>');
Assert::error(function() use ($doc, $rngFile) {
	$doc->relaxNGValidate($rngFile);
}, E_WARNING, 'DOMDocument::relaxNGValidate(): Did not expect element test there');
