<?php

/**
 * Test: MethodCall
 */

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

$converter = new Milo\XmlRpc\Converter;


# From XML
$methodCall = $converter->fromXml(file_get_contents(__DIR__ . '/files/MethodCall.xml'));

Assert::type( 'Milo\XmlRpc\MethodCall', $methodCall );
Assert::same( 'namespace.myMethod', $methodCall->getName() );
Assert::same( [''], $methodCall->getParameters() );
Assert::same( 1, $methodCall->getParameterCount() );


# To XML
$methodCall = new Milo\XmlRpc\MethodCall('mc');
Assert::same( '<methodCall><methodName>mc</methodName></methodCall>', $converter->toXml($methodCall) );

$methodCall = new Milo\XmlRpc\MethodCall('mc', [1]);
Assert::same( '<methodCall><methodName>mc</methodName><params><param><value><int>1</int></value></param></params></methodCall>', $converter->toXml($methodCall) );
