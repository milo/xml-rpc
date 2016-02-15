<?php

/**
 * Test: PHP resource type conversion
 *
 * @author  Miloslav Hůla
 */

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';



$coder = new Milo\XmlRpc\Coder;
$doc = new DOMDocument;



$fd = fopen('php://memory', 'rw');
fwrite($fd, 'test');
Assert::equal('<value><base64/></value>', $doc->saveXML($coder->encodeValueNode($doc, $fd)));
fseek($fd, 0, SEEK_SET);
Assert::equal('<value><base64>dGVzdA==</base64></value>', $doc->saveXML($coder->encodeValueNode($doc, $fd)));

Assert::exception(function() use ($coder, $doc) {
	$ctx = stream_context_create();
	$doc->saveXML($coder->encodeValueNode($doc, $ctx));
}, 'InvalidArgumentException', "Conversion of 'stream-context' resource is not implemented.");
