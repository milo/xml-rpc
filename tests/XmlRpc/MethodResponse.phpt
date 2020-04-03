<?php

declare(strict_types=1);

/**
 * Test: MethodResponse
 */

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

$converter = new Milo\XmlRpc\Converter;


# From XML
$methodResponse = $converter->fromXml(file_get_contents(__DIR__ . '/files/MethodResponse.xml'));

Assert::type(Milo\XmlRpc\MethodResponse::class, $methodResponse);
Assert::same('', $methodResponse->getReturnValue());


# To XML
$methodResponse = new Milo\XmlRpc\MethodResponse(231);
Assert::same('<methodResponse><params><param><value><int>231</int></value></param></params></methodResponse>', $converter->toXml($methodResponse));
