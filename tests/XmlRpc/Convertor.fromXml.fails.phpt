<?php

/**
 * Test: Convertor::fromXml() fails
 *
 * @author  Miloslav Hůla
 */

require __DIR__ . '/../bootstrap.php';



class Mock extends Milo\XmlRpc\Convertor
{
	public function createDom()
	{
		return parent::createDom();
	}
}


$mock = new Mock;

Assert::exception(function() use ($mock) {
	$mock->fromXml('');
}, 'Milo\XmlRpc\MalformedXmlException', 'XML source loading failed.');

$e = Assert::exception(function() use ($mock) {
	$mock->fromXml('x');
}, 'Milo\XmlRpc\MalformedXmlException', 'XML source loading failed.');

Assert::exception(function() use ($e) {
	throw $e->getPrevious();
}, 'Milo\XmlRpc\LibXmlErrorException', "Start tag expected, '<' not found");



$e = Assert::exception(function() use ($mock) {
	$mock->fromXml('<test/>');
}, 'Milo\XmlRpc\NotValidXmlException', 'XML source is not valid to XML-RPC schema.');

Assert::exception(function() use ($e) {
	throw $e->getPrevious();
}, 'Milo\XmlRpc\LibXmlErrorException', 'Did not expect element test there');
