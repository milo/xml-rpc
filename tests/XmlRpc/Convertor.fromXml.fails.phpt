<?php

/**
 * Test: Convertor::fromXml() fails
 *
 * @author  Miloslav Hůla
 */

require __DIR__ . '/../bootstrap.php';



$convertor = new Milo\XmlRpc\Convertor;

Assert::exception(function() use ($convertor) {
	$convertor->fromXml('');
}, 'Milo\XmlRpc\MalformedXmlException', 'XML source loading failed.');

$e = Assert::exception(function() use ($convertor) {
	$convertor->fromXml('x');
}, 'Milo\XmlRpc\MalformedXmlException', 'XML source loading failed.');

Assert::exception(function() use ($e) {
	throw $e->getPrevious();
}, 'Milo\XmlRpc\LibXmlErrorException', "Start tag expected, '<' not found");



$e = Assert::exception(function() use ($convertor) {
	$convertor->fromXml('<test/>');
}, 'Milo\XmlRpc\NotValidXmlException', 'XML source is not valid to XML-RPC schema.');

Assert::exception(function() use ($e) {
	throw $e->getPrevious();
}, 'Milo\XmlRpc\LibXmlErrorException', 'Did not expect element test there');
