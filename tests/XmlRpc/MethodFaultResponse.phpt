<?php

/**
 * Test: MethodFaultResponse
 */

require __DIR__ . '/../bootstrap.php';

use Tester\Assert;

$converter = new Milo\XmlRpc\Converter;


# From XML
$faultResponse = $converter->fromXml(file_get_contents(__DIR__ . '/files/MethodFaultResponse.xml'));
Assert::type( 'Milo\XmlRpc\MethodFaultResponse', $faultResponse );
Assert::same( 0, $faultResponse->getCode() );
Assert::same( 'error message', $faultResponse->getMessage() );


# From exception
$e = new Exception('Error message', 10);

$faultResponse = Milo\XmlRpc\MethodFaultResponse::fromException($e);
Assert::type( 'Milo\XmlRpc\MethodFaultResponse', $faultResponse );
Assert::same( 10, $faultResponse->getCode() );
Assert::same( 'Error message', $faultResponse->getMessage() );


# To XML
$faultResponse = new Milo\XmlRpc\MethodFaultResponse('Error message', 123);
Assert::same(
	'<methodResponse><fault><value><struct><member><name>faultCode</name><value><int>123</int></value></member><member><name>faultString</name><value><string>Error message</string></value></member></struct></value></fault></methodResponse>',
	$converter->toXml($faultResponse)
);
