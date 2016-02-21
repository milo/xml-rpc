<?php

/**
 * Test: Converter::fromXml() fails
 */

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';



$converter = new Milo\XmlRpc\Converter;

Assert::exception(function() use ($converter) {
	$converter->fromXml('');
}, 'Milo\XmlRpc\MalformedXmlException', 'XML source loading failed.');

$e = Assert::exception(function() use ($converter) {
	$converter->fromXml('x');
}, 'Milo\XmlRpc\MalformedXmlException', 'XML source loading failed.');

Assert::exception(function() use ($e) {
	throw $e->getPrevious();
}, 'Milo\XmlRpc\LibXmlErrorException', "Start tag expected, '<' not found");



/** @var Milo\XmlRpc\NotValidXmlException $e */
$e = Assert::exception(function() use ($converter) {
	$converter->fromXml('<test/>');
}, 'Milo\XmlRpc\NotValidXmlException', 'XML source is not valid to XML-RPC schema.');
Assert::same('<test/>', $e->getXml());


/** @var Milo\XmlRpc\LibXmlErrorException $e */
$e = $e->getPrevious();
Assert::exception(function() use ($e) {
	throw $e;
}, 'Milo\XmlRpc\LibXmlErrorException', 'Did not expect element test there');
Assert::same($e->getColumn(), 0);
