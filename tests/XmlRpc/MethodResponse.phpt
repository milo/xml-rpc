<?php

/**
 * Test: MethodResponse
 *
 * @author  Miloslav Hůla
 */

require __DIR__ . '/../bootstrap.php';

$convertor = new Milo\XmlRpc\Convertor;


# From XML
$methodResponse = $convertor->fromXml(file_get_contents(__DIR__ . '/files/MethodResponse.xml'));

Assert::type( 'Milo\XmlRpc\MethodResponse', $methodResponse );
Assert::same( '', $methodResponse->getReturnValue() );


# To XML
$methodResponse = new Milo\XmlRpc\MethodResponse(231);
Assert::same( '<methodResponse><params><param><value><int>231</int></value></param></params></methodResponse>', $convertor->toXml($methodResponse) );
